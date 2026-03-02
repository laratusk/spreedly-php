<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Laratusk\Spreedly\Contracts\CertificateManagerInterface;
use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\CertificateKeyPair;
use Laratusk\Spreedly\Laravel\Facades\SpreedlyCertificateManager;
use Laratusk\Spreedly\Laravel\Models\SpreedlyCertificate;
use Laratusk\Spreedly\Services\CertificateManager;
use Laratusk\Spreedly\SpreedlyClient;
use Laratusk\Spreedly\Support\MacAddress;

beforeEach(function (): void {
    $migration = include __DIR__.'/../../../src/Laravel/database/migrations/create_spreedly_certificates_table.php.stub';
    $migration->up();
});

/**
 * Build a CertificateManagerInterface mock whose encryptPrivateKey / decryptPrivateKey
 * delegate to the real Crypt-backed implementation so database round-trips work correctly.
 *
 * Uses SpreedlyCertificateManager::swap() so the facade cache is properly replaced —
 * the facade may have been resolved already (and cached) during the old-cert creation
 * in booted(), so app()->instance() alone is insufficient.
 */
function mockCertManager(CertificateKeyPair $keyPair): void
{
    $realManager = new CertificateManager;
    $mock = Mockery::mock(CertificateManagerInterface::class);
    $mock->shouldReceive('createCertificateKeyPair')->once()->andReturn($keyPair);
    $mock->shouldReceive('encryptPrivateKey')
        ->andReturnUsing(fn (string $key): string => $realManager->encryptPrivateKey($key));
    $mock->shouldReceive('decryptPrivateKey')
        ->andReturnUsing(fn (string $cipher): string => $realManager->decryptPrivateKey($cipher));

    SpreedlyCertificateManager::swap($mock);
}

test('it successfully renews an expiring certificate and deletes the old one', function (): void {
    $expiresSoon = now()->addDays(3);

    // Old certificate — private_key is encrypted at rest by the booted() hook.
    $certificate = SpreedlyCertificate::query()->create([
        'name' => 'Test Cert',
        'gateway' => 'stripe',
        'token' => 'old_token_123',
        'environment' => 'development',
        'mac_address' => MacAddress::get(),
        'pem' => 'old_pem',
        'public_key' => 'old_pub_key',
        'private_key' => 'old_priv_key',
        'expires_at' => $expiresSoon,
    ]);

    $newExp = now()->addYears(1);

    // Swap the facade after old cert creation so the cache is correctly replaced.
    mockCertManager(new CertificateKeyPair('new_pem', 'new_priv_key'));

    Log::shouldReceive('info')->once()->with('Spreedly certificate successfully renewed', Mockery::on(fn ($data): bool => $data['old_token'] === 'old_token_123' && $data['new_token'] === 'new_token_456'));

    // Transporter: expects the locally-generated pem + private_key to be uploaded.
    $mockTransporter = Mockery::mock(TransporterInterface::class);
    $mockTransporter->shouldReceive('post')
        ->once()
        ->with('certificates.json', ['certificate' => ['pem' => 'new_pem', 'private_key' => 'new_priv_key']])
        ->andReturn([
            'certificate' => [
                'token' => 'new_token_456',
                'state' => 'retained',
                'common_name' => 'Test Cert',
                'subject' => 'Test Cert',
                'cert_body' => 'new_pem',
                'private_key_body' => 'new_priv_key',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'expires_at' => $newExp->toIso8601String(),
            ],
        ]);

    $client = new SpreedlyClient('env', 'secret', $mockTransporter);
    app()->instance(SpreedlyClient::class, $client);

    $this->artisan('spreedly:renew-certificates')
        ->expectsOutput('Starting Spreedly certificates renewal process...')
        ->expectsOutput('Attempting to renew certificate: Test Cert (old_token_123)')
        ->expectsOutputToContain('Successfully renewed certificate. Name: Test Cert, Gateway: stripe, Old token: old_token_123, New token: new_token_456, Expires at: '.$newExp->toIso8601String())
        ->expectsOutput('Renewal complete. Renewed: 1. Failed: 0.')
        ->assertExitCode(0);

    // Old certificate must be deleted.
    expect(SpreedlyCertificate::query()->find($certificate->id))->toBeNull();

    // New certificate must be created with the locally-generated values.
    $newCert = SpreedlyCertificate::query()->where('token', 'new_token_456')->firstOrFail();
    expect($newCert)->not->toBeNull()
        ->and($newCert->name)->toBe('Test Cert')
        ->and($newCert->pem)->toBe('new_pem')
        ->and($newCert->getPrivateKey())->toBe('new_priv_key');
});

test('it ignores certificates that are not expiring within 7 days', function (): void {
    SpreedlyCertificate::query()->create([
        'name' => 'Valid Cert',
        'gateway' => 'stripe',
        'token' => 'valid_token',
        'expires_at' => now()->addDays(10),
    ]);

    $this->artisan('spreedly:renew-certificates')
        ->expectsOutput('Starting Spreedly certificates renewal process...')
        ->expectsOutput('No expiring certificates found.')
        ->assertExitCode(0);

    expect(SpreedlyCertificate::count())->toBe(1);
});

test('it retains the old certificate if renewal fails and logs the error', function (): void {
    $certificate = SpreedlyCertificate::query()->create([
        'name' => 'Fail Cert',
        'gateway' => 'stripe',
        'token' => 'fail_token',
        'expires_at' => now()->addDays(1),
    ]);

    // Key-pair generation succeeds; the Spreedly API upload fails.
    mockCertManager(new CertificateKeyPair('new_pem', 'new_priv_key'));

    Log::shouldReceive('error')->once()->with('Spreedly certificate renewal failed', Mockery::on(fn ($data): bool => $data['token'] === 'fail_token' && $data['error'] === 'API Error'));

    $mockTransporter = Mockery::mock(TransporterInterface::class);
    $mockTransporter->shouldReceive('post')
        ->once()
        ->andThrow(new Exception('API Error'));

    $client = new SpreedlyClient('env', 'secret', $mockTransporter);
    app()->instance(SpreedlyClient::class, $client);

    $this->artisan('spreedly:renew-certificates')
        ->expectsOutput('Starting Spreedly certificates renewal process...')
        ->expectsOutput('Attempting to renew certificate: Fail Cert (fail_token)')
        ->expectsOutput('Failed to renew certificate fail_token: API Error')
        ->expectsOutput('Renewal complete. Renewed: 0. Failed: 1.')
        ->assertExitCode(1);

    // Old certificate must NOT be deleted.
    expect(SpreedlyCertificate::find($certificate->id))->not->toBeNull();
});

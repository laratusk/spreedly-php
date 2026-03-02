<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Laratusk\Spreedly\Contracts\CertificateManagerInterface;
use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\CertificateKeyPair;
use Laratusk\Spreedly\Laravel\Facades\SpreedlyCertificateManager;
use Laratusk\Spreedly\Laravel\Models\SpreedlyCertificate;
use Laratusk\Spreedly\Laravel\Services\CertificateManager;
use Laratusk\Spreedly\Laravel\Support\MacAddress;
use Laratusk\Spreedly\SpreedlyClient;

beforeEach(function (): void {
    MacAddress::reset();
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
        'token' => 'old_token_123',
        'mac_address' => MacAddress::get(),
        'pem' => 'old_pem',
        'public_key' => 'old_pub_key',
        'private_key' => 'old_priv_key',
        'expires_at' => $expiresSoon,
    ]);

    $newExp = now()->addYears(1);

    // Swap the facade after old cert creation so the cache is correctly replaced.
    mockCertManager(new CertificateKeyPair('new_pem', 'new_priv_key', 'new_pub_key', 'new_pub_key_hash'));

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

    $this->artisan('spreedly:certificate-install')
        ->expectsOutput('Starting Spreedly certificate renewal process...')
        ->expectsOutput('Renewing certificate: Test Cert (old_token_123)')
        ->expectsOutputToContain('Certificate renewed successfully. Old: old_token_123, New: new_token_456, Expires: '.$newExp->toIso8601String())
        ->assertExitCode(0);

    // Old certificate must be deleted.
    expect(SpreedlyCertificate::query()->find($certificate->id))->toBeNull();

    // New certificate must be created with the locally-generated values.
    $newCert = SpreedlyCertificate::query()->where('token', 'new_token_456')->firstOrFail();
    expect($newCert)->not->toBeNull()
        ->and($newCert->name)->toBe('Test Cert')
        ->and($newCert->pem)->toBe('new_pem')
        ->and($newCert->getPrivateKey())->toBe('new_priv_key')
        ->and($newCert->getPublicKey())->toBe('new_pub_key')
        ->and($newCert->getPublicKeyHash())->toBe('new_pub_key_hash');
});

test('it ignores a certificate that is not expiring within the configured threshold', function (): void {
    SpreedlyCertificate::query()->create([
        'name' => 'Valid Cert',
        'token' => 'valid_token',
        'expires_at' => now()->addDays(10),
    ]);

    $this->artisan('spreedly:certificate-install')
        ->expectsOutput('Starting Spreedly certificate renewal process...')
        ->expectsOutputToContain('Certificate is valid, no renewal needed. Expires:')
        ->assertExitCode(0);

    expect(SpreedlyCertificate::count())->toBe(1);
});

test('it retains the old certificate if renewal fails and logs the error', function (): void {
    $certificate = SpreedlyCertificate::query()->create([
        'name' => 'Fail Cert',
        'token' => 'fail_token',
        'expires_at' => now()->addDays(1),
    ]);

    // Key-pair generation succeeds; the Spreedly API upload fails.
    mockCertManager(new CertificateKeyPair('new_pem', 'new_priv_key', 'new_pub_key', 'new_pub_key_hash'));

    Log::shouldReceive('error')->once()->with('Spreedly certificate renewal failed', Mockery::on(fn ($data): bool => $data['token'] === 'fail_token' && $data['error'] === 'API Error'));

    $mockTransporter = Mockery::mock(TransporterInterface::class);
    $mockTransporter->shouldReceive('post')
        ->once()
        ->andThrow(new Exception('API Error'));

    $client = new SpreedlyClient('env', 'secret', $mockTransporter);
    app()->instance(SpreedlyClient::class, $client);

    $this->artisan('spreedly:certificate-install')
        ->expectsOutput('Starting Spreedly certificate renewal process...')
        ->expectsOutput('Renewing certificate: Fail Cert (fail_token)')
        ->expectsOutput('Failed to renew certificate fail_token: API Error')
        ->assertExitCode(1);

    // Old certificate must NOT be deleted.
    expect(SpreedlyCertificate::find($certificate->id))->not->toBeNull();
});

test('it replaces the non-expiring certificate when --force is used', function (): void {
    $validCert = SpreedlyCertificate::query()->create([
        'name' => 'Valid Cert',
        'token' => 'valid_token',
        'mac_address' => MacAddress::get(),
        'pem' => 'old_pem',
        'public_key' => 'old_pub_key',
        'private_key' => 'old_priv_key',
        'expires_at' => now()->addDays(30), // not expiring
    ]);

    $newExp = now()->addYears(1);

    mockCertManager(new CertificateKeyPair('new_pem', 'new_priv_key', 'new_pub_key', 'new_pub_key_hash'));

    Log::shouldReceive('info')->once()->with('Spreedly certificate successfully renewed', Mockery::on(
        fn ($data): bool => $data['old_token'] === 'valid_token' && $data['new_token'] === 'forced_token_789'
    ));

    $mockTransporter = Mockery::mock(TransporterInterface::class);
    $mockTransporter->shouldReceive('post')
        ->once()
        ->with('certificates.json', ['certificate' => ['pem' => 'new_pem', 'private_key' => 'new_priv_key']])
        ->andReturn([
            'certificate' => [
                'token' => 'forced_token_789',
                'state' => 'retained',
                'common_name' => 'Valid Cert',
                'subject' => 'Valid Cert',
                'cert_body' => 'new_pem',
                'private_key_body' => 'new_priv_key',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'expires_at' => $newExp->toIso8601String(),
            ],
        ]);

    $client = new SpreedlyClient('env', 'secret', $mockTransporter);
    app()->instance(SpreedlyClient::class, $client);

    $this->artisan('spreedly:certificate-install', ['--force' => true])
        ->expectsOutput('Starting Spreedly certificate renewal process...')
        ->expectsOutput('Renewing certificate: Valid Cert (valid_token)')
        ->expectsOutputToContain('Certificate renewed successfully. Old: valid_token, New: forced_token_789, Expires: '.$newExp->toIso8601String())
        ->assertExitCode(0);

    expect(SpreedlyCertificate::query()->find($validCert->id))->toBeNull();

    $newCert = SpreedlyCertificate::query()->where('token', 'forced_token_789')->firstOrFail();
    expect($newCert->name)->toBe('Valid Cert')
        ->and($newCert->pem)->toBe('new_pem')
        ->and($newCert->getPrivateKey())->toBe('new_priv_key')
        ->and($newCert->getPublicKey())->toBe('new_pub_key')
        ->and($newCert->getPublicKeyHash())->toBe('new_pub_key_hash');
});

test('it creates a new certificate when none exists, even with --force', function (): void {
    mockCertManager(new CertificateKeyPair('new_pem', 'new_priv_key', 'new_pub_key', 'new_pub_key_hash'));

    $mockTransporter = Mockery::mock(TransporterInterface::class);
    $mockTransporter->shouldReceive('post')
        ->once()
        ->andReturn([
            'certificate' => [
                'token' => 'brand_new_token',
                'state' => 'retained',
                'common_name' => null,
                'subject' => null,
                'cert_body' => 'new_pem',
                'private_key_body' => 'new_priv_key',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'expires_at' => now()->addYears(1)->toIso8601String(),
            ],
        ]);

    $client = new SpreedlyClient('env', 'secret', $mockTransporter);
    app()->instance(SpreedlyClient::class, $client);

    $this->artisan('spreedly:certificate-install', ['--force' => true])
        ->expectsOutput('Starting Spreedly certificate renewal process...')
        ->expectsOutput('No certificate found. Creating a new one...')
        ->expectsOutput('New Spreedly certificate successfully created.')
        ->assertExitCode(0);

    expect(SpreedlyCertificate::count())->toBe(1);
});

test('it retains the non-expiring certificate when --force renewal fails', function (): void {
    $validCert = SpreedlyCertificate::query()->create([
        'name' => 'Valid Cert',
        'token' => 'valid_token',
        'mac_address' => MacAddress::get(),
        'expires_at' => now()->addDays(30),
    ]);

    mockCertManager(new CertificateKeyPair('new_pem', 'new_priv_key', 'new_pub_key', 'new_pub_key_hash'));

    Log::shouldReceive('error')->once()->with('Spreedly certificate renewal failed', Mockery::on(
        fn ($data): bool => $data['token'] === 'valid_token' && $data['error'] === 'API Error'
    ));

    $mockTransporter = Mockery::mock(TransporterInterface::class);
    $mockTransporter->shouldReceive('post')->once()->andThrow(new Exception('API Error'));

    $client = new SpreedlyClient('env', 'secret', $mockTransporter);
    app()->instance(SpreedlyClient::class, $client);

    $this->artisan('spreedly:certificate-install', ['--force' => true])
        ->expectsOutput('Starting Spreedly certificate renewal process...')
        ->expectsOutput('Renewing certificate: Valid Cert (valid_token)')
        ->expectsOutput('Failed to renew certificate valid_token: API Error')
        ->assertExitCode(1);

    // Original cert must be retained on failure.
    expect(SpreedlyCertificate::query()->find($validCert->id))->not->toBeNull();
});

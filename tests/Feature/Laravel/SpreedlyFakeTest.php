<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Gateway;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Laravel\Facades\Spreedly;
use Laratusk\Spreedly\SpreedlyClient;
use Laratusk\Spreedly\Testing\MockTransporter;
use Laratusk\Spreedly\Testing\SpreedlyFake;

/**
 * @return array<string, mixed>
 */
function gatewayFixtureData(string $token = 'gw_test_123'): array
{
    return [
        'gateway' => [
            'token' => $token,
            'gateway_type' => 'test',
            'name' => 'Test Gateway',
            'description' => null,
            'state' => 'retained',
            'payment_methods' => ['credit_card'],
            'characteristics' => [],
            'credentials' => [],
            'gateway_settings' => [],
            'gateway_specific_fields' => [],
            'merchant_profile_key' => null,
            'sub_merchant_key' => null,
            'redacted_at' => null,
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function transactionFixtureData(string $token = 'tx_test_123'): array
{
    return [
        'transaction' => [
            'token' => $token,
            'transaction_type' => 'Purchase',
            'succeeded' => true,
            'message' => 'Succeeded!',
            'amount' => 1000,
            'currency_code' => 'USD',
            'state' => 'succeeded',
            'gateway_token' => 'gw_test_123',
            'payment_method' => null,
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];
}

// ── Construction ─────────────────────────────────────────────────────────────

test('SpreedlyFake::make returns fake with mock and client', function (): void {
    $fake = SpreedlyFake::make();

    expect($fake)->toBeInstanceOf(SpreedlyFake::class);
    expect($fake->mock)->toBeInstanceOf(MockTransporter::class);
    expect($fake->client())->toBeInstanceOf(SpreedlyClient::class);
});

// ── Container swap ────────────────────────────────────────────────────────────

test('fake client can be swapped into the Laravel container', function (): void {
    $fake = SpreedlyFake::make();

    $this->app->instance(SpreedlyClient::class, $fake->client());

    expect($this->app->make(SpreedlyClient::class))->toBe($fake->client());
});

test('Spreedly facade uses fake after container swap', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('GET', 'gateways.json', ['gateways' => []]);

    $this->app->instance(SpreedlyClient::class, $fake->client());

    $collection = Spreedly::gateways()->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

// ── Mock responses via facade ─────────────────────────────────────────────────

test('facade returns configured mock response as typed DTO', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('GET', 'gateways/gw_test_123.json', gatewayFixtureData());

    $this->app->instance(SpreedlyClient::class, $fake->client());

    $gateway = Spreedly::gateways()->retrieve('gw_test_123');

    expect($gateway)->toBeInstanceOf(Gateway::class);
    expect($gateway->token)->toBe('gw_test_123');
    expect($gateway->gatewayType)->toBe('test');
    expect($gateway->state)->toBe('retained');
});

test('facade POST request returns mock response', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('POST', 'gateways.json', gatewayFixtureData('gw_created_456'));

    $this->app->instance(SpreedlyClient::class, $fake->client());

    $gateway = Spreedly::gateways()->create(['gateway_type' => 'test']);

    expect($gateway)->toBeInstanceOf(Gateway::class);
    expect($gateway->token)->toBe('gw_created_456');
});

test('facade returns empty collection when no mock response configured for list', function (): void {
    $fake = SpreedlyFake::make();

    $this->app->instance(SpreedlyClient::class, $fake->client());

    $collection = Spreedly::gateways()->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

// ── Call assertions ───────────────────────────────────────────────────────────

test('mock records calls made through facade', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('GET', 'gateways/gw_test_123.json', gatewayFixtureData());

    $this->app->instance(SpreedlyClient::class, $fake->client());

    Spreedly::gateways()->retrieve('gw_test_123');

    expect($fake->mock->getCallCount())->toBe(1);
    $fake->mock->assertCalled('GET', 'gateways/gw_test_123.json');
});

test('mock assertCalled throws when method was not called', function (): void {
    $fake = SpreedlyFake::make();

    $this->app->instance(SpreedlyClient::class, $fake->client());

    expect(fn () => $fake->mock->assertCalled('GET', 'gateways/gw_test_123.json'))
        ->toThrow(RuntimeException::class);
});

test('mock tracks multiple calls and their count', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('GET', 'gateways/gw_test_123.json', gatewayFixtureData());
    $fake->mock->addResponse('POST', 'gateways.json', gatewayFixtureData('gw_new'));

    $this->app->instance(SpreedlyClient::class, $fake->client());

    Spreedly::gateways()->retrieve('gw_test_123');
    Spreedly::gateways()->create(['gateway_type' => 'test']);

    expect($fake->mock->getCallCount())->toBe(2);
    $fake->mock->assertCalled('GET', 'gateways/gw_test_123.json');
    $fake->mock->assertCalled('POST', 'gateways.json');
});

// ── Real flow simulation ──────────────────────────────────────────────────────

test('can simulate a full purchase flow with fake', function (): void {
    $fake = SpreedlyFake::make();
    $fake->mock->addResponse('POST', 'gateways/gw_test_123/purchase.json', transactionFixtureData('tx_abc'));

    $this->app->instance(SpreedlyClient::class, $fake->client());

    $transaction = Spreedly::transactions()->purchase('gw_test_123', [
        'payment_method_token' => 'pm_token',
        'amount' => 1000,
        'currency_code' => 'USD',
    ]);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->token)->toBe('tx_abc');
    expect($transaction->succeeded)->toBeTrue();
    expect($transaction->amount)->toBe(1000);
    $fake->mock->assertCalled('POST', 'gateways/gw_test_123/purchase.json');
});

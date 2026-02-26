<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\Gateway;

test('can be created from array with gateway wrapper', function (): void {
    $data = [
        'gateway' => [
            'token' => 'abc123',
            'gateway_type' => 'stripe',
            'description' => 'My Gateway',
            'name' => 'Stripe',
            'state' => 'retained',
            'payment_methods' => ['credit_card'],
            'characteristics' => ['supports_purchase'],
            'credentials' => [],
            'gateway_settings' => [],
            'gateway_specific_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $gateway = Gateway::fromArray($data);

    expect($gateway->token)->toBe('abc123');
    expect($gateway->gatewayType)->toBe('stripe');
    expect($gateway->description)->toBe('My Gateway');
    expect($gateway->state)->toBe('retained');
    expect($gateway->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($gateway->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without gateway wrapper', function (): void {
    $data = [
        'token' => 'abc123',
        'gateway_type' => 'test',
        'name' => 'Test',
        'state' => 'retained',
        'payment_methods' => [],
        'characteristics' => [],
        'credentials' => [],
        'gateway_settings' => [],
        'gateway_specific_fields' => [],
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $gateway = Gateway::fromArray($data);

    expect($gateway->token)->toBe('abc123');
    expect($gateway->gatewayType)->toBe('test');
});

test('nullable fields default to null', function (): void {
    $data = [
        'gateway' => [
            'token' => 'abc123',
            'gateway_type' => 'test',
            'name' => 'Test',
            'state' => 'retained',
            'payment_methods' => [],
            'characteristics' => [],
            'credentials' => [],
            'gateway_settings' => [],
            'gateway_specific_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $gateway = Gateway::fromArray($data);

    expect($gateway->description)->toBeNull();
    expect($gateway->merchantProfileKey)->toBeNull();
    expect($gateway->subMerchantKey)->toBeNull();
    expect($gateway->redactedAt)->toBeNull();
});

test('to array round trip', function (): void {
    $data = [
        'gateway' => [
            'token' => 'abc123',
            'gateway_type' => 'stripe',
            'description' => 'Test',
            'name' => 'Stripe',
            'state' => 'retained',
            'payment_methods' => ['credit_card'],
            'characteristics' => ['supports_purchase'],
            'credentials' => [],
            'gateway_settings' => [],
            'gateway_specific_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $gateway = Gateway::fromArray($data);
    $array = $gateway->toArray();

    expect($array['token'])->toBe('abc123');
    expect($array['gateway_type'])->toBe('stripe');
    expect($array['state'])->toBe('retained');
    expect($array['created_at'])->toBeString();
});

<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;

test('can be created from array with payment_method wrapper', function (): void {
    $data = $this->loadFixture('payment_methods/create.json');

    $pm = PaymentMethod::fromArray($data);

    expect($pm->token)->toBe('56wyNnSmuA6en32YnlLFoJNFLSI');
    expect($pm->storageState)->toBe('cached');
    expect($pm->test)->toBeTrue();
    expect($pm->lastFourDigits)->toBe('4242');
    expect($pm->firstSixDigits)->toBe('411111');
    expect($pm->cardType)->toBe('visa');
    expect($pm->paymentMethodType)->toBe('credit_card');
    expect($pm->createdAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without wrapper', function (): void {
    $data = [
        'token' => 'pm_abc123',
        'storage_state' => 'retained',
        'test' => false,
        'payment_method_type' => 'credit_card',
        'eligible_for_card_updater' => false,
        'errors' => [],
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $pm = PaymentMethod::fromArray($data);

    expect($pm->token)->toBe('pm_abc123');
    expect($pm->storageState)->toBe('retained');
    expect($pm->test)->toBeFalse();
});

test('nullable fields default to null', function (): void {
    $data = [
        'payment_method' => [
            'token' => 'pm_abc123',
            'storage_state' => 'retained',
            'test' => false,
            'payment_method_type' => 'credit_card',
            'eligible_for_card_updater' => false,
            'errors' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $pm = PaymentMethod::fromArray($data);

    expect($pm->lastFourDigits)->toBeNull();
    expect($pm->cardType)->toBeNull();
    expect($pm->email)->toBeNull();
    expect($pm->fingerprint)->toBeNull();
    expect($pm->callbackUrl)->toBeNull();
});

test('to array contains required fields', function (): void {
    $data = $this->loadFixture('payment_methods/create.json');
    $pm = PaymentMethod::fromArray($data);
    $array = $pm->toArray();

    expect($array['token'])->toBe('56wyNnSmuA6en32YnlLFoJNFLSI');
    expect($array['storage_state'])->toBe('cached');
    expect($array['payment_method_type'])->toBe('credit_card');
    expect($array['created_at'])->toBeString();
});

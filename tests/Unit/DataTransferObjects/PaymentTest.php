<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\Payment;

test('can be created from array with payment wrapper', function (): void {
    $data = [
        'payment' => [
            'token' => 'PAY123abc456DEF789ghi',
            'state' => 'succeeded',
            'amount' => 1500,
            'currency_code' => 'USD',
            'payment_method_token' => 'PM1234567890abcdefgh',
            'description' => 'Test payment',
            'data' => ['reference' => 'order_12345'],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $payment = Payment::fromArray($data);

    expect($payment->token)->toBe('PAY123abc456DEF789ghi');
    expect($payment->state)->toBe('succeeded');
    expect($payment->amount)->toBe(1500);
    expect($payment->currencyCode)->toBe('USD');
    expect($payment->paymentMethodToken)->toBe('PM1234567890abcdefgh');
    expect($payment->description)->toBe('Test payment');
    expect($payment->data)->toBe(['reference' => 'order_12345']);
    expect($payment->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($payment->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without wrapper', function (): void {
    $data = [
        'token' => 'PAY123abc456DEF789ghi',
        'state' => 'pending',
        'data' => [],
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $payment = Payment::fromArray($data);

    expect($payment->token)->toBe('PAY123abc456DEF789ghi');
    expect($payment->state)->toBe('pending');
});

test('nullable fields default to null', function (): void {
    $data = [
        'payment' => [
            'token' => 'PAY123abc456DEF789ghi',
            'state' => 'pending',
            'data' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $payment = Payment::fromArray($data);

    expect($payment->amount)->toBeNull();
    expect($payment->currencyCode)->toBeNull();
    expect($payment->paymentMethodToken)->toBeNull();
    expect($payment->description)->toBeNull();
});

test('to array round trip', function (): void {
    $data = [
        'payment' => [
            'token' => 'PAY123abc456DEF789ghi',
            'state' => 'succeeded',
            'amount' => 1500,
            'currency_code' => 'USD',
            'payment_method_token' => 'PM1234567890abcdefgh',
            'description' => 'Test payment',
            'data' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $payment = Payment::fromArray($data);
    $array = $payment->toArray();

    expect($array['token'])->toBe('PAY123abc456DEF789ghi');
    expect($array['state'])->toBe('succeeded');
    expect($array['amount'])->toBe(1500);
    expect($array['currency_code'])->toBe('USD');
    expect($array['created_at'])->toBeString();
});

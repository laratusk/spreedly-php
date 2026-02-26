<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

test('can be created from array with transaction wrapper', function (): void {
    $data = [
        'transaction' => [
            'token' => 'tx_abc123',
            'transaction_type' => 'Purchase',
            'succeeded' => true,
            'state' => 'succeeded',
            'amount' => 1000,
            'currency_code' => 'USD',
            'on_test_gateway' => true,
            'retain_on_success' => false,
            'test' => true,
            'response' => [],
            'gateway_specific_fields' => [],
            'gateway_specific_response_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $tx = Transaction::fromArray($data);

    expect($tx->token)->toBe('tx_abc123');
    expect($tx->transactionType)->toBe('Purchase');
    expect($tx->succeeded)->toBeTrue();
    expect($tx->amount)->toBe(1000);
    expect($tx->currencyCode)->toBe('USD');
    expect($tx->createdAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('maps nested payment method', function (): void {
    $data = [
        'transaction' => [
            'token' => 'tx_abc123',
            'transaction_type' => 'Purchase',
            'succeeded' => true,
            'on_test_gateway' => false,
            'retain_on_success' => false,
            'test' => false,
            'response' => [],
            'gateway_specific_fields' => [],
            'gateway_specific_response_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
            'payment_method' => [
                'token' => 'pm_xyz',
                'storage_state' => 'retained',
                'test' => true,
                'payment_method_type' => 'credit_card',
                'last_four_digits' => '4242',
                'eligible_for_card_updater' => true,
                'errors' => [],
                'created_at' => '2024-01-15T09:00:00Z',
                'updated_at' => '2024-01-15T09:00:00Z',
            ],
        ],
    ];

    $tx = Transaction::fromArray($data);

    expect($tx->paymentMethod)->toBeInstanceOf(PaymentMethod::class);
    expect($tx->paymentMethod?->token)->toBe('pm_xyz');
    expect($tx->paymentMethod?->lastFourDigits)->toBe('4242');
});

test('failed transaction has succeeded false', function (): void {
    $data = [
        'transaction' => [
            'token' => 'tx_failed',
            'transaction_type' => 'Purchase',
            'succeeded' => false,
            'state' => 'failed',
            'message' => 'Unable to process',
            'on_test_gateway' => true,
            'retain_on_success' => false,
            'test' => true,
            'response' => ['success' => false],
            'gateway_specific_fields' => [],
            'gateway_specific_response_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $tx = Transaction::fromArray($data);

    expect($tx->succeeded)->toBeFalse();
    expect($tx->state)->toBe('failed');
    expect($tx->message)->toBe('Unable to process');
});

test('to array contains required fields', function (): void {
    $data = [
        'transaction' => [
            'token' => 'tx_abc123',
            'transaction_type' => 'Purchase',
            'succeeded' => true,
            'on_test_gateway' => false,
            'retain_on_success' => false,
            'test' => false,
            'response' => [],
            'gateway_specific_fields' => [],
            'gateway_specific_response_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $tx = Transaction::fromArray($data);
    $array = $tx->toArray();

    expect($array)->toHaveKey('token');
    expect($array)->toHaveKey('transaction_type');
    expect($array)->toHaveKey('succeeded');
    expect($array['token'])->toBe('tx_abc123');
});

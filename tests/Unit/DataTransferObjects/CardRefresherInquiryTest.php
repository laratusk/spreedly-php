<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\CardRefresherInquiry;

test('can be created from array with inquiry wrapper', function (): void {
    $data = [
        'inquiry' => [
            'token' => 'CRI123abc456DEF789ghi',
            'state' => 'succeeded',
            'succeeded' => true,
            'payment_method_token' => 'PM1234567890abcdefgh',
            'message' => 'Card refreshed successfully',
            'message_key' => 'messages.card_refresher.succeeded',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:05:00Z',
        ],
    ];

    $inquiry = CardRefresherInquiry::fromArray($data);

    expect($inquiry->token)->toBe('CRI123abc456DEF789ghi');
    expect($inquiry->state)->toBe('succeeded');
    expect($inquiry->succeeded)->toBeTrue();
    expect($inquiry->paymentMethodToken)->toBe('PM1234567890abcdefgh');
    expect($inquiry->message)->toBe('Card refreshed successfully');
    expect($inquiry->messageKey)->toBe('messages.card_refresher.succeeded');
    expect($inquiry->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($inquiry->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without inquiry wrapper', function (): void {
    $data = [
        'token' => 'CRI123abc456DEF789ghi',
        'state' => 'failed',
        'succeeded' => false,
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $inquiry = CardRefresherInquiry::fromArray($data);

    expect($inquiry->token)->toBe('CRI123abc456DEF789ghi');
    expect($inquiry->state)->toBe('failed');
    expect($inquiry->succeeded)->toBeFalse();
});

test('nullable fields default to null', function (): void {
    $data = [
        'inquiry' => [
            'token' => 'CRI123abc456DEF789ghi',
            'state' => 'pending',
            'succeeded' => false,
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $inquiry = CardRefresherInquiry::fromArray($data);

    expect($inquiry->paymentMethodToken)->toBeNull();
    expect($inquiry->message)->toBeNull();
    expect($inquiry->messageKey)->toBeNull();
});

test('to array round trip', function (): void {
    $data = [
        'inquiry' => [
            'token' => 'CRI123abc456DEF789ghi',
            'state' => 'succeeded',
            'succeeded' => true,
            'payment_method_token' => 'PM1234567890abcdefgh',
            'message' => 'Card refreshed',
            'message_key' => 'messages.succeeded',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:05:00Z',
        ],
    ];

    $inquiry = CardRefresherInquiry::fromArray($data);
    $array = $inquiry->toArray();

    expect($array['token'])->toBe('CRI123abc456DEF789ghi');
    expect($array['state'])->toBe('succeeded');
    expect($array['succeeded'])->toBeTrue();
    expect($array['payment_method_token'])->toBe('PM1234567890abcdefgh');
    expect($array['created_at'])->toBeString();
});

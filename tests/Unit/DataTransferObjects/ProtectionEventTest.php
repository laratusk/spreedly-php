<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\ProtectionEvent;

test('can be created from array with protection_event wrapper', function (): void {
    $data = [
        'protection_event' => [
            'token' => 'PE1234567890abcdefghi',
            'event_type' => 'card_updated',
            'state' => 'succeeded',
            'payment_method_token' => 'PM1234567890abcdefgh',
            'gateway_token' => 'GW1234567890abcdefgh',
            'data' => ['previous_last_four_digits' => '1111'],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $event = ProtectionEvent::fromArray($data);

    expect($event->token)->toBe('PE1234567890abcdefghi');
    expect($event->eventType)->toBe('card_updated');
    expect($event->state)->toBe('succeeded');
    expect($event->paymentMethodToken)->toBe('PM1234567890abcdefgh');
    expect($event->gatewayToken)->toBe('GW1234567890abcdefgh');
    expect($event->data)->toBe(['previous_last_four_digits' => '1111']);
    expect($event->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($event->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without wrapper', function (): void {
    $data = [
        'token' => 'PE1234567890abcdefghi',
        'event_type' => 'card_expired',
        'state' => 'succeeded',
        'data' => [],
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $event = ProtectionEvent::fromArray($data);

    expect($event->token)->toBe('PE1234567890abcdefghi');
    expect($event->eventType)->toBe('card_expired');
});

test('nullable fields default to null', function (): void {
    $data = [
        'protection_event' => [
            'token' => 'PE1234567890abcdefghi',
            'event_type' => 'card_expired',
            'state' => 'succeeded',
            'data' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $event = ProtectionEvent::fromArray($data);

    expect($event->paymentMethodToken)->toBeNull();
    expect($event->gatewayToken)->toBeNull();
});

test('to array round trip', function (): void {
    $data = [
        'protection_event' => [
            'token' => 'PE1234567890abcdefghi',
            'event_type' => 'card_updated',
            'state' => 'succeeded',
            'payment_method_token' => 'PM1234567890abcdefgh',
            'gateway_token' => 'GW1234567890abcdefgh',
            'data' => ['key' => 'value'],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $event = ProtectionEvent::fromArray($data);
    $array = $event->toArray();

    expect($array['token'])->toBe('PE1234567890abcdefghi');
    expect($array['event_type'])->toBe('card_updated');
    expect($array['state'])->toBe('succeeded');
    expect($array['data'])->toBe(['key' => 'value']);
    expect($array['created_at'])->toBeString();
});

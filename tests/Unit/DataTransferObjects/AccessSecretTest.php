<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\AccessSecret;

test('can be created from array with access_secret wrapper', function (): void {
    $data = [
        'access_secret' => [
            'token' => 'AS1234567890abcdefghi',
            'name' => 'My Access Secret',
            'description' => 'Used for production integrations',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $secret = AccessSecret::fromArray($data);

    expect($secret->token)->toBe('AS1234567890abcdefghi');
    expect($secret->name)->toBe('My Access Secret');
    expect($secret->description)->toBe('Used for production integrations');
    expect($secret->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($secret->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without wrapper', function (): void {
    $data = [
        'token' => 'AS1234567890abcdefghi',
        'name' => 'My Secret',
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $secret = AccessSecret::fromArray($data);

    expect($secret->token)->toBe('AS1234567890abcdefghi');
    expect($secret->name)->toBe('My Secret');
});

test('nullable fields default to null', function (): void {
    $data = [
        'access_secret' => [
            'token' => 'AS1234567890abcdefghi',
            'name' => 'My Secret',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $secret = AccessSecret::fromArray($data);

    expect($secret->description)->toBeNull();
});

test('to array round trip', function (): void {
    $data = [
        'access_secret' => [
            'token' => 'AS1234567890abcdefghi',
            'name' => 'My Access Secret',
            'description' => 'For production',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $secret = AccessSecret::fromArray($data);
    $array = $secret->toArray();

    expect($array['token'])->toBe('AS1234567890abcdefghi');
    expect($array['name'])->toBe('My Access Secret');
    expect($array['description'])->toBe('For production');
    expect($array['created_at'])->toBeString();
});

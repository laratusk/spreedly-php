<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\MerchantProfile;

test('can be created from array with merchant_profile wrapper', function (): void {
    $data = $this->loadFixture('merchant_profiles/create.json');

    $profile = MerchantProfile::fromArray($data);

    expect($profile->token)->toBe('Mp1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($profile->name)->toBe('My Store');
    expect($profile->city)->toBe('San Francisco');
    expect($profile->state)->toBe('CA');
    expect($profile->country)->toBe('US');
    expect($profile->merchantCategoryCode)->toBe('5411');
    expect($profile->merchantId)->toBe('mid_123');
    expect($profile->createdAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('nullable fields default to null', function (): void {
    $data = [
        'merchant_profile' => [
            'token' => 'mp_abc123',
            'profile_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $profile = MerchantProfile::fromArray($data);

    expect($profile->name)->toBeNull();
    expect($profile->city)->toBeNull();
    expect($profile->state)->toBeNull();
    expect($profile->merchantId)->toBeNull();
    expect($profile->merchantCategoryCode)->toBeNull();
});

test('profile fields is an array', function (): void {
    $data = $this->loadFixture('merchant_profiles/create.json');
    $profile = MerchantProfile::fromArray($data);

    expect($profile->profileFields)->toBeArray();
});

test('to array round trip', function (): void {
    $data = $this->loadFixture('merchant_profiles/create.json');
    $profile = MerchantProfile::fromArray($data);
    $array = $profile->toArray();

    expect($array['token'])->toBe('Mp1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($array['name'])->toBe('My Store');
    expect($array['created_at'])->toBeString();
});

<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\SubMerchant;

test('can be created from array with sub_merchant wrapper', function (): void {
    $data = $this->loadFixture('sub_merchants/create.json');

    $subMerchant = SubMerchant::fromArray($data);

    expect($subMerchant->token)->toBe('Sm1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($subMerchant->name)->toBe('Sub Merchant Co');
    expect($subMerchant->email)->toBe('sub@example.com');
    expect($subMerchant->url)->toBe('https://submerchant.com');
    expect($subMerchant->city)->toBe('New York');
    expect($subMerchant->state)->toBe('NY');
    expect($subMerchant->country)->toBe('US');
    expect($subMerchant->merchantCategoryCode)->toBe('5999');
    expect($subMerchant->createdAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('nullable fields default to null', function (): void {
    $data = [
        'sub_merchant' => [
            'token' => 'sm_abc123',
            'fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $sm = SubMerchant::fromArray($data);

    expect($sm->name)->toBeNull();
    expect($sm->email)->toBeNull();
    expect($sm->url)->toBeNull();
    expect($sm->city)->toBeNull();
    expect($sm->merchantCategoryCode)->toBeNull();
});

test('fields is an array', function (): void {
    $data = $this->loadFixture('sub_merchants/create.json');
    $sm = SubMerchant::fromArray($data);

    expect($sm->fields)->toBeArray();
});

test('to array round trip', function (): void {
    $data = $this->loadFixture('sub_merchants/create.json');
    $sm = SubMerchant::fromArray($data);
    $array = $sm->toArray();

    expect($array['token'])->toBe('Sm1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($array['name'])->toBe('Sub Merchant Co');
    expect($array['email'])->toBe('sub@example.com');
    expect($array['created_at'])->toBeString();
});

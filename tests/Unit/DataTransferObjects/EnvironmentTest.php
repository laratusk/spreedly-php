<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\Environment;

test('can be created from array with environment wrapper', function (): void {
    $data = $this->loadFixture('environments/show.json');

    $env = Environment::fromArray($data);

    expect($env->key)->toBe('env_key_abc123');
    expect($env->name)->toBe('My Environment');
    expect($env->test)->toBeTrue();
    expect($env->hipaa)->toBeFalse();
    expect($env->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($env->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without environment wrapper', function (): void {
    $data = [
        'key' => 'env_abc',
        'name' => 'Test Environment',
        'test' => true,
        'hipaa' => false,
        'callback_urls' => [],
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $env = Environment::fromArray($data);

    expect($env->key)->toBe('env_abc');
    expect($env->callbackUrl)->toBeNull();
});

test('callback urls is an array', function (): void {
    $data = $this->loadFixture('environments/show.json');
    $env = Environment::fromArray($data);

    expect($env->callbackUrls)->toBeArray();
});

test('to array round trip', function (): void {
    $data = $this->loadFixture('environments/show.json');
    $env = Environment::fromArray($data);
    $array = $env->toArray();

    expect($array['key'])->toBe('env_key_abc123');
    expect($array['name'])->toBe('My Environment');
    expect($array['test'])->toBeTrue();
    expect($array['created_at'])->toBeString();
});

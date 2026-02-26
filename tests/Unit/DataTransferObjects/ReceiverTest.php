<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\Receiver;

test('can be created from array with receiver wrapper', function (): void {
    $data = $this->loadFixture('receivers/create.json');

    $receiver = Receiver::fromArray($data);

    expect($receiver->token)->toBe('Rx1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($receiver->receiverType)->toBe('braintree');
    expect($receiver->state)->toBe('retained');
    expect($receiver->description)->toBe('My Braintree Receiver');
    expect($receiver->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($receiver->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without receiver wrapper', function (): void {
    $data = [
        'token' => 'rx_abc123',
        'receiver_type' => 'braintree',
        'state' => 'retained',
        'credentials' => [],
        'hostnames' => [],
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $receiver = Receiver::fromArray($data);

    expect($receiver->token)->toBe('rx_abc123');
    expect($receiver->description)->toBeNull();
    expect($receiver->redactedAt)->toBeNull();
});

test('hostnames and credentials are arrays', function (): void {
    $data = $this->loadFixture('receivers/create.json');
    $receiver = Receiver::fromArray($data);

    expect($receiver->hostnames)->toBeArray();
    expect($receiver->credentials)->toBeArray();
    expect($receiver->hostnames[0])->toBe('api.braintreegateway.com');
});

test('to array round trip', function (): void {
    $data = $this->loadFixture('receivers/create.json');
    $receiver = Receiver::fromArray($data);
    $array = $receiver->toArray();

    expect($array['token'])->toBe('Rx1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($array['receiver_type'])->toBe('braintree');
    expect($array['state'])->toBe('retained');
    expect($array['created_at'])->toBeString();
});

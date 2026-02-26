<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\Certificate;

test('can be created from array with certificate wrapper', function (): void {
    $data = $this->loadFixture('certificates/create.json');

    $cert = Certificate::fromArray($data);

    expect($cert->token)->toBe('Cert123xoVyB');
    expect($cert->state)->toBe('created');
    expect($cert->commonName)->toBe('apple.pay.example.com');
    expect($cert->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($cert->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('nullable fields default to null', function (): void {
    $data = [
        'certificate' => [
            'token' => 'cert_abc123',
            'state' => 'created',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $cert = Certificate::fromArray($data);

    expect($cert->commonName)->toBeNull();
    expect($cert->subject)->toBeNull();
    expect($cert->certBody)->toBeNull();
    expect($cert->privateKeyBody)->toBeNull();
    expect($cert->csr)->toBeNull();
    expect($cert->expiresAt)->toBeNull();
});

test('can be created from array without certificate wrapper', function (): void {
    $data = [
        'token' => 'cert_direct',
        'state' => 'generated',
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $cert = Certificate::fromArray($data);

    expect($cert->token)->toBe('cert_direct');
    expect($cert->state)->toBe('generated');
});

test('to array round trip', function (): void {
    $data = $this->loadFixture('certificates/create.json');
    $cert = Certificate::fromArray($data);
    $array = $cert->toArray();

    expect($array['token'])->toBe('Cert123xoVyB');
    expect($array['state'])->toBe('created');
    expect($array['created_at'])->toBeString();
});

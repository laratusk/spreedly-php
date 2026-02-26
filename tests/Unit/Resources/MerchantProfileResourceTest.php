<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\MerchantProfile;
use Laratusk\Spreedly\Resources\MerchantProfileResource;

test('create sends POST request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('merchant_profiles/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('merchant_profiles.json', ['merchant_profile' => ['name' => 'My Store']])
        ->andReturn($fixture);

    $resource = new MerchantProfileResource($transporter);
    $profile = $resource->create(['name' => 'My Store']);

    expect($profile)->toBeInstanceOf(MerchantProfile::class);
    expect($profile->token)->toBe('Mp1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($profile->name)->toBe('My Store');
});

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('merchant_profiles/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('merchant_profiles/Mp1gI6fHgIuUkVnmUGPA3xoVyB.json')
        ->andReturn($fixture);

    $resource = new MerchantProfileResource($transporter);
    $profile = $resource->retrieve('Mp1gI6fHgIuUkVnmUGPA3xoVyB');

    expect($profile)->toBeInstanceOf(MerchantProfile::class);
    expect($profile->city)->toBe('San Francisco');
});

test('list returns paginated collection', function (): void {
    $fixture = ['merchant_profiles' => [$this->loadFixture('merchant_profiles/create.json')['merchant_profile']]];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('merchant_profiles.json', [])
        ->andReturn($fixture);

    $resource = new MerchantProfileResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(MerchantProfile::class);
});

test('update sends PUT request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('merchant_profiles/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('merchant_profiles/Mp1gI6fHgIuUkVnmUGPA3xoVyB.json', ['merchant_profile' => ['name' => 'Updated Store']])
        ->andReturn($fixture);

    $resource = new MerchantProfileResource($transporter);
    $profile = $resource->update('Mp1gI6fHgIuUkVnmUGPA3xoVyB', ['name' => 'Updated Store']);

    expect($profile)->toBeInstanceOf(MerchantProfile::class);
});

test('createProtectionProvider sends POST to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('merchant_profiles/Mp1gI6fHgIuUkVnmUGPA3xoVyB/protection_provider.json', ['provider_type' => 'kount'])
        ->andReturn(['protection_provider' => ['token' => 'PP123', 'provider_type' => 'kount']]);

    $resource = new MerchantProfileResource($transporter);
    $result = $resource->createProtectionProvider('Mp1gI6fHgIuUkVnmUGPA3xoVyB', ['provider_type' => 'kount']);

    expect($result)->toBeArray();
    expect($result['protection_provider']['token'])->toBe('PP123');
});

test('retrieveProtectionProvider sends GET to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('merchant_profiles/Mp1gI6fHgIuUkVnmUGPA3xoVyB/protection_provider.json')
        ->andReturn(['protection_provider' => ['token' => 'PP123', 'provider_type' => 'kount']]);

    $resource = new MerchantProfileResource($transporter);
    $result = $resource->retrieveProtectionProvider('Mp1gI6fHgIuUkVnmUGPA3xoVyB');

    expect($result)->toBeArray();
    expect($result['protection_provider']['provider_type'])->toBe('kount');
});

test('createScaProvider sends POST to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('merchant_profiles/Mp1gI6fHgIuUkVnmUGPA3xoVyB/sca_provider.json', ['provider_type' => 'stripe_radar'])
        ->andReturn(['sca_provider' => ['token' => 'SCA123', 'provider_type' => 'stripe_radar']]);

    $resource = new MerchantProfileResource($transporter);
    $result = $resource->createScaProvider('Mp1gI6fHgIuUkVnmUGPA3xoVyB', ['provider_type' => 'stripe_radar']);

    expect($result)->toBeArray();
    expect($result['sca_provider']['token'])->toBe('SCA123');
});

test('retrieveScaProvider sends GET to correct endpoint', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('merchant_profiles/Mp1gI6fHgIuUkVnmUGPA3xoVyB/sca_provider.json')
        ->andReturn(['sca_provider' => ['token' => 'SCA123', 'provider_type' => 'stripe_radar']]);

    $resource = new MerchantProfileResource($transporter);
    $result = $resource->retrieveScaProvider('Mp1gI6fHgIuUkVnmUGPA3xoVyB');

    expect($result)->toBeArray();
    expect($result['sca_provider']['provider_type'])->toBe('stripe_radar');
});

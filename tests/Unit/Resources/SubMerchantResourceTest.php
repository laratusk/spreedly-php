<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\SubMerchant;
use Laratusk\Spreedly\Resources\SubMerchantResource;

test('create sends POST request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('sub_merchants/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('sub_merchants.json', ['sub_merchant' => ['name' => 'Sub Merchant Co']])
        ->andReturn($fixture);

    $resource = new SubMerchantResource($transporter);
    $subMerchant = $resource->create(['name' => 'Sub Merchant Co']);

    expect($subMerchant)->toBeInstanceOf(SubMerchant::class);
    expect($subMerchant->token)->toBe('Sm1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($subMerchant->name)->toBe('Sub Merchant Co');
});

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('sub_merchants/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('sub_merchants/Sm1gI6fHgIuUkVnmUGPA3xoVyB.json')
        ->andReturn($fixture);

    $resource = new SubMerchantResource($transporter);
    $subMerchant = $resource->retrieve('Sm1gI6fHgIuUkVnmUGPA3xoVyB');

    expect($subMerchant)->toBeInstanceOf(SubMerchant::class);
    expect($subMerchant->email)->toBe('sub@example.com');
});

test('list returns paginated collection', function (): void {
    $fixture = ['sub_merchants' => [$this->loadFixture('sub_merchants/create.json')['sub_merchant']]];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('sub_merchants.json', [])
        ->andReturn($fixture);

    $resource = new SubMerchantResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(SubMerchant::class);
});

test('update sends PUT request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('sub_merchants/create.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('put')
        ->once()
        ->with('sub_merchants/Sm1gI6fHgIuUkVnmUGPA3xoVyB.json', ['sub_merchant' => ['name' => 'Updated Name']])
        ->andReturn($fixture);

    $resource = new SubMerchantResource($transporter);
    $subMerchant = $resource->update('Sm1gI6fHgIuUkVnmUGPA3xoVyB', ['name' => 'Updated Name']);

    expect($subMerchant)->toBeInstanceOf(SubMerchant::class);
});

test('list forwards the order and count filters', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('sub_merchants.json', ['order' => 'asc', 'count' => 40])
        ->andReturn(['sub_merchants' => []]);

    $resource = new SubMerchantResource($transporter);

    expect($resource->list(order: 'asc', count: 40)->count())->toBe(0);
});

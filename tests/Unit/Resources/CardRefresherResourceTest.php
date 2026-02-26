<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\CardRefresherInquiry;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\Resources\CardRefresherResource;

test('create sends POST request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('card_refresher/inquiry.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('post')
        ->once()
        ->with('card_refresher/inquiry.json', ['inquiry' => ['payment_method_token' => 'PM1234567890abcdefgh']])
        ->andReturn($fixture);

    $resource = new CardRefresherResource($transporter);
    $inquiry = $resource->create(['payment_method_token' => 'PM1234567890abcdefgh']);

    expect($inquiry)->toBeInstanceOf(CardRefresherInquiry::class);
    expect($inquiry->token)->toBe('CRI123abc456DEF789ghi');
    expect($inquiry->state)->toBe('succeeded');
    expect($inquiry->succeeded)->toBeTrue();
});

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('card_refresher/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('card_refresher/inquiry/CRI123abc456DEF789ghi.json')
        ->andReturn($fixture);

    $resource = new CardRefresherResource($transporter);
    $inquiry = $resource->retrieve('CRI123abc456DEF789ghi');

    expect($inquiry)->toBeInstanceOf(CardRefresherInquiry::class);
    expect($inquiry->token)->toBe('CRI123abc456DEF789ghi');
    expect($inquiry->paymentMethodToken)->toBe('PM1234567890abcdefgh');
});

test('list returns paginated collection', function (): void {
    $fixture = $this->loadFixture('card_refresher/list.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('card_refresher/inquiry.json', [])
        ->andReturn($fixture);

    $resource = new CardRefresherResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(2);
    expect($collection->items[0])->toBeInstanceOf(CardRefresherInquiry::class);
    expect($collection->items[0]->token)->toBe('CRI123abc456DEF789ghi');
    expect($collection->items[1]->token)->toBe('CRI987xyz654ABC321uvw');
});

test('list passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('card_refresher/inquiry.json', ['since_token' => 'some_token'])
        ->andReturn(['inquiries' => []]);

    $resource = new CardRefresherResource($transporter);
    $collection = $resource->list('some_token');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

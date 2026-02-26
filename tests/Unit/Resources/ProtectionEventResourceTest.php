<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\ProtectionEvent;
use Laratusk\Spreedly\Resources\ProtectionEventResource;

test('list returns paginated collection', function (): void {
    $fixture = $this->loadFixture('protection_events/list.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('protection_events.json', [])
        ->andReturn($fixture);

    $resource = new ProtectionEventResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(2);
    expect($collection->items[0])->toBeInstanceOf(ProtectionEvent::class);
    expect($collection->items[0]->token)->toBe('PE1234567890abcdefghi');
    expect($collection->items[1]->token)->toBe('PE9876543210zyxwvutsrq');
});

test('list passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('protection_events.json', ['since_token' => 'some_token'])
        ->andReturn(['protection_events' => []]);

    $resource = new ProtectionEventResource($transporter);
    $collection = $resource->list('some_token');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(0);
});

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('protection_events/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('protection_events/PE1234567890abcdefghi.json')
        ->andReturn($fixture);

    $resource = new ProtectionEventResource($transporter);
    $event = $resource->retrieve('PE1234567890abcdefghi');

    expect($event)->toBeInstanceOf(ProtectionEvent::class);
    expect($event->token)->toBe('PE1234567890abcdefghi');
    expect($event->eventType)->toBe('card_updated');
    expect($event->state)->toBe('succeeded');
    expect($event->paymentMethodToken)->toBe('PM1234567890abcdefgh');
});

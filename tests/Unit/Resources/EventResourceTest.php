<?php

declare(strict_types=1);

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Event;
use Laratusk\Spreedly\Resources\EventResource;

test('retrieve sends GET request to correct endpoint', function (): void {
    $fixture = $this->loadFixture('events/show.json');

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('events/Ev1gI6fHgIuUkVnmUGPA3xoVyB.json')
        ->andReturn($fixture);

    $resource = new EventResource($transporter);
    $event = $resource->retrieve('Ev1gI6fHgIuUkVnmUGPA3xoVyB');

    expect($event)->toBeInstanceOf(Event::class);
    expect($event->token)->toBe('Ev1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($event->eventType)->toBe('purchase');
    expect($event->state)->toBe('delivered');
});

test('list returns paginated collection', function (): void {
    $fixture = ['events' => [$this->loadFixture('events/show.json')['event']]];

    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('events.json', ['order' => 'desc'])
        ->andReturn($fixture);

    $resource = new EventResource($transporter);
    $collection = $resource->list();

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
    expect($collection->count())->toBe(1);
    expect($collection->items[0])->toBeInstanceOf(Event::class);
});

test('list passes since_token for pagination', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('events.json', ['order' => 'desc', 'since_token' => 'some_token'])
        ->andReturn(['events' => []]);

    $resource = new EventResource($transporter);
    $collection = $resource->list('some_token');

    expect($collection)->toBeInstanceOf(PaginatedCollection::class);
});

test('list forwards the event_type and count filters', function (): void {
    $transporter = Mockery::mock(TransporterInterface::class);
    $transporter->shouldReceive('get')
        ->once()
        ->with('events.json', ['order' => 'desc', 'event_type' => 'card_updated', 'count' => 100])
        ->andReturn(['events' => []]);

    $resource = new EventResource($transporter);

    expect($resource->list(eventType: 'card_updated', count: 100)->count())->toBe(0);
});

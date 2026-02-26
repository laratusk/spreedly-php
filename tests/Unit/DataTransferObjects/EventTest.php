<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\Event;

test('can be created from array with event wrapper', function (): void {
    $data = $this->loadFixture('events/show.json');

    $event = Event::fromArray($data);

    expect($event->token)->toBe('Ev1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($event->eventType)->toBe('purchase');
    expect($event->state)->toBe('delivered');
    expect($event->createdAt)->toBeInstanceOf(CarbonImmutable::class);
    expect($event->updatedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without event wrapper', function (): void {
    $data = [
        'token' => 'ev_abc123',
        'event_type' => 'authorize',
        'state' => 'pending',
        'data' => [],
        'created_at' => '2024-01-15T10:00:00Z',
        'updated_at' => '2024-01-15T10:00:00Z',
    ];

    $event = Event::fromArray($data);

    expect($event->token)->toBe('ev_abc123');
    expect($event->eventType)->toBe('authorize');
    expect($event->data)->toBeArray();
});

test('data contains event payload', function (): void {
    $data = $this->loadFixture('events/show.json');
    $event = Event::fromArray($data);

    expect($event->data)->toBeArray();
    expect($event->data['transaction_token'])->toBe('tx_123');
});

test('to array round trip', function (): void {
    $data = $this->loadFixture('events/show.json');
    $event = Event::fromArray($data);
    $array = $event->toArray();

    expect($array['token'])->toBe('Ev1gI6fHgIuUkVnmUGPA3xoVyB');
    expect($array['event_type'])->toBe('purchase');
    expect($array['created_at'])->toBeString();
});

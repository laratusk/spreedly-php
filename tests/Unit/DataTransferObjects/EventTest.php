<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\Event;

test('can be created from array with event wrapper', function (): void {
    $data = $this->loadFixture('events/show.json');

    $event = Event::fromArray($data);

    expect($event->id)->toBe('40790047-6ba0-41ea-88c8-42fe224e617b');
    expect($event->eventType)->toBe('UpdatePaymentMethodReceiver');
    expect($event->objectType)->toBe('PaymentMethodReceiver');
    expect($event->objectKey)->toBe('5QQ5532YER89MS729YG1R15DV3');
    expect($event->requestId)->toBeNull();
    expect($event->createdAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('can be created from array without event wrapper', function (): void {
    $event = Event::fromArray([
        'id' => 'e738dcd7-7a99-4192-ac05-73ba7263377d',
        'request_id' => 'a1b2c3d4',
        'event_type' => 'RedactPaymentMethodReceiver',
        'object_type' => 'PaymentMethodReceiver',
        'object_key' => 'PM123',
        'created_at' => '2024-01-15T10:00:00Z',
    ]);

    expect($event->id)->toBe('e738dcd7-7a99-4192-ac05-73ba7263377d');
    expect($event->requestId)->toBe('a1b2c3d4');
    expect($event->eventType)->toBe('RedactPaymentMethodReceiver');
});

test('raw keeps the payload the event was built from', function (): void {
    $event = Event::fromArray($this->loadFixture('events/show.json'));

    expect($event->raw)->toHaveKey('object_key');
    expect($event->raw['object_key'])->toBe('5QQ5532YER89MS729YG1R15DV3');
});

test('to array round trip', function (): void {
    $array = Event::fromArray($this->loadFixture('events/show.json'))->toArray();

    expect($array['id'])->toBe('40790047-6ba0-41ea-88c8-42fe224e617b');
    expect($array['event_type'])->toBe('UpdatePaymentMethodReceiver');
    expect($array['object_type'])->toBe('PaymentMethodReceiver');
    expect($array['created_at'])->toBeString();
});

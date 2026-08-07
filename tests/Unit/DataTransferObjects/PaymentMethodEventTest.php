<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethodEvent;

test('can be created from the payment_method_event wrapper', function (): void {
    $event = PaymentMethodEvent::fromArray($this->loadFixture('payment_methods/event.json'));

    expect($event->token)->toBe('PME123abc456DEF789ghi');
    expect($event->paymentMethodKey)->toBe('56wyNnSmuA6en32YnlLFoJNFLSI');
    expect($event->eventType)->toBe('card_updated');
    expect($event->state)->toBe('succeeded');
    expect($event->message)->toBe('Card updated');
    expect($event->createdAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('event_data carries the change the event describes', function (): void {
    $event = PaymentMethodEvent::fromArray($this->loadFixture('payment_methods/event.json'));

    expect($event->eventData)->toMatchArray([
        'previous_last_four_digits' => '1111',
        'new_last_four_digits' => '4242',
    ]);
});

test('can be created without a wrapper', function (): void {
    $event = PaymentMethodEvent::fromArray([
        'token' => 'PME999',
        'event_type' => 'network_token_deactivated',
    ]);

    expect($event->token)->toBe('PME999');
    expect($event->eventType)->toBe('network_token_deactivated');
    expect($event->eventData)->toBe([]);
    expect($event->state)->toBeNull();
});

test('to array round trip', function (): void {
    $array = PaymentMethodEvent::fromArray($this->loadFixture('payment_methods/event.json'))->toArray();

    expect($array['token'])->toBe('PME123abc456DEF789ghi');
    expect($array['event_type'])->toBe('card_updated');
    expect($array['event_data'])->toBeArray();
    expect($array['created_at'])->toBeString();
});

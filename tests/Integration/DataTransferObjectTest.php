<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\Certificate;
use Laratusk\Spreedly\DataTransferObjects\Event;
use Laratusk\Spreedly\DataTransferObjects\Gateway;
use Laratusk\Spreedly\DataTransferObjects\MerchantProfile;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;
use Laratusk\Spreedly\DataTransferObjects\Receiver;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Checks that each DTO actually unwraps the envelope the live API sends.
 *
 * `paymentMethods->create()` returned a payment method whose every field was empty for
 * as long as this SDK has existed, because the response is wrapped in a transaction and
 * the hand-written fixture said otherwise — a mocked test agreed with the bug. An empty
 * identifier on a call that succeeded is the signature of that failure, so that is what
 * these assert.
 *
 * Read-only wherever an existing record can be used, so the suite leaves nothing behind.
 */
test('a created gateway parses', function (): void {
    $gateway = $this->spreedly->gateways->retrieve($this->sandboxToken('gateway'));

    expect($gateway)->toBeInstanceOf(Gateway::class);
    expect($gateway->token)->not->toBeEmpty();
    expect($gateway->gatewayType)->toBe('test');
});

test('listed gateways parse', function (): void {
    $gateways = $this->spreedly->gateways->list(count: 5);

    expect($gateways->items)->not->toBeEmpty();

    foreach ($gateways->items as $gateway) {
        expect($gateway->token)->not->toBeEmpty();
        expect($gateway->gatewayType)->not->toBeEmpty();
    }
});

test('a created payment method parses', function (): void {
    $token = $this->sandboxToken('payment method');
    $paymentMethod = $this->spreedly->paymentMethods->retrieve($token);

    expect($paymentMethod)->toBeInstanceOf(PaymentMethod::class);
    expect($paymentMethod->token)->toBe($token);
    expect($paymentMethod->lastFourDigits)->toBe('1111');
    expect($paymentMethod->paymentMethodType)->toBe('credit_card');
});

test('creating a payment method returns one that is actually populated', function (): void {
    $paymentMethod = $this->spreedly->paymentMethods->create([
        'credit_card' => [
            'first_name' => 'Integration',
            'last_name' => 'Test',
            'number' => '4111111111111111',
            'verification_value' => '123',
            'month' => '12',
            'year' => (string) ((int) date('Y') + 3),
        ],
        'retained' => true,
    ]);

    $this->trackPaymentMethod($paymentMethod->token);

    expect($paymentMethod->token)->not->toBeEmpty();
    expect($paymentMethod->storageState)->toBe('retained');
    expect($paymentMethod->cardType)->toBe('visa');
});

test('listed payment methods parse', function (): void {
    $paymentMethods = $this->spreedly->paymentMethods->list(count: 5);

    expect($paymentMethods->items)->not->toBeEmpty();

    foreach ($paymentMethods->items as $paymentMethod) {
        expect($paymentMethod->token)->not->toBeEmpty();
    }
});

test('a created transaction parses', function (): void {
    $transaction = $this->spreedly->transactions->retrieve($this->sandboxToken('transaction'));

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->token)->not->toBeEmpty();
    expect($transaction->transactionType)->not->toBeEmpty();
    expect($transaction->amount)->toBe(1000);
});

test('listed transactions parse', function (): void {
    $transactions = $this->spreedly->transactions->list(count: 5);

    expect($transactions->items)->not->toBeEmpty();

    foreach ($transactions->items as $transaction) {
        expect($transaction->token)->not->toBeEmpty();
    }
});

test('listed and retrieved events parse', function (): void {
    $events = $this->spreedly->events->list(count: 5);

    if ($events->items === []) {
        $this->markTestSkipped('No events in this environment.');
    }

    foreach ($events->items as $event) {
        expect($event)->toBeInstanceOf(Event::class);
        expect($event->id)->not->toBeEmpty();
        expect($event->eventType)->not->toBeEmpty();
    }

    expect($this->spreedly->events->retrieve($events->items[0]->id)->id)
        ->toBe($events->items[0]->id);
});

test('listed payment method events parse', function (): void {
    $events = $this->spreedly->paymentMethods->listEvents(count: 5);

    if ($events->items === []) {
        $this->markTestSkipped('No payment method events in this environment.');
    }

    foreach ($events->items as $event) {
        expect($event->token)->not->toBeEmpty();
    }
});

test('listed merchant profiles parse', function (): void {
    $profiles = $this->spreedly->merchantProfiles->list(count: 5);

    if ($profiles->items === []) {
        $this->markTestSkipped('No merchant profiles in this environment.');
    }

    foreach ($profiles->items as $profile) {
        expect($profile)->toBeInstanceOf(MerchantProfile::class);
        expect($profile->token)->not->toBeEmpty();
    }

    expect($this->spreedly->merchantProfiles->retrieve($profiles->items[0]->token)->token)
        ->toBe($profiles->items[0]->token);
});

test('listed certificates parse', function (): void {
    $certificates = $this->spreedly->certificates->list();

    if ($certificates->items === []) {
        $this->markTestSkipped('No certificates in this environment.');
    }

    foreach ($certificates->items as $certificate) {
        expect($certificate)->toBeInstanceOf(Certificate::class);
        expect($certificate->token)->not->toBeEmpty();
    }
});

test('a created receiver parses', function (): void {
    $receiver = $this->spreedly->receivers->create([
        'receiver_type' => 'test',
        'hostnames' => 'https://spreedly-echo.herokuapp.com',
    ]);

    $this->trackReceiver($receiver->token);

    expect($receiver)->toBeInstanceOf(Receiver::class);
    expect($receiver->token)->not->toBeEmpty();
    expect($receiver->receiverType)->toBe('test');

    expect($this->spreedly->receivers->retrieve($receiver->token)->token)->toBe($receiver->token);
});

test('listed protection events parse', function (): void {
    $events = $this->spreedly->protectionEvents->list(count: 5);

    if ($events->items === []) {
        $this->markTestSkipped('No protection events in this environment.');
    }

    foreach ($events->items as $event) {
        expect($event->token)->not->toBeEmpty();
    }
});

test('listed card refresher inquiries parse', function (): void {
    $inquiries = $this->spreedly->cardRefresher->list(count: 5);

    if ($inquiries->items === []) {
        $this->markTestSkipped('No card refresher inquiries in this environment.');
    }

    foreach ($inquiries->items as $inquiry) {
        expect($inquiry->token)->not->toBeEmpty();
    }
});

test('the supported gateway and receiver option lists parse', function (): void {
    expect($this->spreedly->gateways->supportedGateways())->not->toBeEmpty();
    expect($this->spreedly->receivers->supportedReceivers())->not->toBeEmpty();
});

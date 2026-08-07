<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Drives the resource methods whose arguments changed in 2.0.0 through the real API,
 * so the fixes are proven end to end and not just at the path level.
 */
test('a purchase settles against the test gateway', function (): void {
    $tx = $this->spreedly->transactions->purchase($this->sandboxToken('gateway'), [
        'payment_method_token' => $this->sandboxToken('payment method'),
        'amount' => 1234,
        'currency_code' => 'USD',
    ]);

    expect($tx)->toBeInstanceOf(Transaction::class);
    expect($tx->succeeded)->toBeTrue();
    expect($tx->amount)->toBe(1234);
});

test('authorize then capture then void round-trips', function (): void {
    $auth = $this->spreedly->transactions->authorize($this->sandboxToken('gateway'), [
        'payment_method_token' => $this->sandboxToken('payment method'),
        'amount' => 500,
        'currency_code' => 'USD',
    ]);
    expect($auth->succeeded)->toBeTrue();

    $capture = $this->spreedly->transactions->capture($auth->token, ['amount' => 500]);
    expect($capture->succeeded)->toBeTrue();

    $void = $this->spreedly->transactions->void($capture->token);
    expect($void->token)->not->toBeEmpty();
});

test('store copies the payment method into the gateway vault', function (): void {
    $tx = $this->spreedly->paymentMethods->store($this->sandboxToken('gateway'), [
        'payment_method_token' => $this->sandboxToken('payment method'),
    ]);

    $this->trackPaymentMethod($tx->paymentMethod?->token);

    expect($tx->succeeded)->toBeTrue();
    expect($tx->paymentMethod?->paymentMethodType)->toBe('third_party_token');
});

test('a reference purchase charges against a previous transaction', function (): void {
    $tx = $this->spreedly->transactions->referencePurchase($this->sandboxToken('transaction'), ['amount' => 800]);

    expect($tx->succeeded)->toBeTrue();
    expect($tx->amount)->toBe(800);
});

test('retrieved transactions expose api_urls as a hash', function (): void {
    $tx = $this->spreedly->transactions->retrieve($this->sandboxToken('transaction'));

    expect($tx->apiUrls)->toBeArray();
    expect($tx->raw)->toHaveKey('token');
});

test('list filters reach the API and narrow the result', function (): void {
    $succeeded = $this->spreedly->transactions->list(state: 'succeeded', count: 5);

    expect($succeeded->count())->toBeLessThanOrEqual(5);

    foreach ($succeeded->items as $tx) {
        expect($tx->state)->toBe('succeeded');
    }
});

test('metadata keys can be removed', function (): void {
    $this->spreedly->paymentMethods->update($this->sandboxToken('payment method'), [
        'metadata' => ['keep' => 'yes', 'drop' => 'please'],
    ]);

    $this->spreedly->paymentMethods->deleteMetadata($this->sandboxToken('payment method'), ['drop']);

    $pm = $this->spreedly->paymentMethods->retrieve($this->sandboxToken('payment method'));

    expect($pm->raw['metadata'] ?? [])->toHaveKey('keep');
    expect($pm->raw['metadata'] ?? [])->not->toHaveKey('drop');
});

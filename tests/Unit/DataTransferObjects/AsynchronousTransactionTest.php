<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;
use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Enums\TransactionState;

test('a pending 3DS transaction exposes where to send the cardholder', function (): void {
    $tx = Transaction::fromArray($this->loadFixture('transactions/purchase_pending_3ds.json'));

    expect($tx->state)->toBe(TransactionState::Pending->value);
    expect($tx->checkoutUrl)->toBe('https://core.spreedly.com/sprel/8XJtbE1p4NTZ6fFqwwn0GrkjEmW/checkout/TC7i2O0mhzpes1BaCaIdzGzHQuL');
    expect($tx->checkoutForm)->toContain('PaReq');
    expect($tx->redirectUrl)->toBe('https://merchant.example/checkout/return');
    expect($tx->callbackUrl)->toBe('https://merchant.example/spreedly/callback');
});

test('the three asynchronous sub-responses survive', function (): void {
    $tx = Transaction::fromArray($this->loadFixture('transactions/purchase_pending_3ds.json'));

    expect($tx->setupResponse)->toMatchArray(['success' => true, 'message' => 'Checked enrollment status']);
    expect($tx->redirectResponse)->toHaveKey('success');
    expect($tx->callbackResponse)->toHaveKey('success');
});

test('requiresCardholderAction is true only while pending with somewhere to go', function (): void {
    $pending = Transaction::fromArray($this->loadFixture('transactions/purchase_pending_3ds.json'));
    $settled = Transaction::fromArray($this->loadFixture('transactions/purchase.json'));

    expect($pending->requiresCardholderAction())->toBeTrue();
    expect($settled->requiresCardholderAction())->toBeFalse();
});

test('a succeeded transaction leaves the asynchronous fields empty', function (): void {
    $tx = Transaction::fromArray($this->loadFixture('transactions/purchase.json'));

    expect($tx->checkoutUrl)->toBeNull();
    expect($tx->checkoutForm)->toBeNull();
    expect($tx->setupResponse)->toBe([]);
    expect($tx->redirectResponse)->toBe([]);
    expect($tx->callbackResponse)->toBe([]);
});

test('raw keeps fields the transaction does not model', function (): void {
    $tx = Transaction::fromArray([
        'transaction' => [
            'token' => 'tx_token',
            'transaction_type' => 'Purchase',
            'succeeded' => true,
            'state' => 'succeeded',
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
            'a_field_the_sdk_does_not_know_about' => 'kept',
        ],
    ]);

    expect($tx->raw)->toHaveKey('a_field_the_sdk_does_not_know_about');
    expect($tx->raw['a_field_the_sdk_does_not_know_about'])->toBe('kept');
    expect($tx->raw['token'])->toBe('tx_token');
});

test('raw on a payment method keeps third_party_token', function (): void {
    $tx = Transaction::fromArray($this->loadFixture('transactions/purchase_pending_3ds.json'));

    $paymentMethod = $tx->paymentMethod;

    expect($paymentMethod)->toBeInstanceOf(PaymentMethod::class);
    assert($paymentMethod instanceof PaymentMethod);

    expect($paymentMethod->raw)->toHaveKey('third_party_token');
    expect($paymentMethod->raw['third_party_token'])->toBe('pm_1OaBcDeFgHiJkLmN');
});

test('toArray carries the asynchronous fields', function (): void {
    $tx = Transaction::fromArray($this->loadFixture('transactions/purchase_pending_3ds.json'));

    expect($tx->toArray())
        ->toHaveKeys(['checkout_url', 'checkout_form', 'redirect_url', 'callback_url', 'setup_response', 'redirect_response', 'callback_response']);
});

test('the documented asynchronous states are representable', function (): void {
    expect(TransactionState::tryFrom('processing'))->toBe(TransactionState::Processing);
    expect(TransactionState::tryFrom('gateway_setup_failed'))->toBe(TransactionState::GatewaySetupFailed);
});

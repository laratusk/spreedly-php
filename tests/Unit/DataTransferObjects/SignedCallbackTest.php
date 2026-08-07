<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Enums\TransactionState;

// The secret, payload and expected signature all come from the worked example in
// https://developer.spreedly.com/docs/signed-requests
const SIGNING_SECRET = '4ziASKWGGV1zdrUbSN6vq2CjjDPw2hzJSvsGLhxces1aORBKKsRyJwb8DfGQ6J3q';

test('a callback payload parses into every transaction it carries', function (): void {
    $transactions = Transaction::fromCallbackPayload($this->loadFixture('transactions/offsite_callback.json'));

    expect($transactions)->toHaveCount(2);
    expect($transactions[0]->token)->toBe('5AG4P7FPjlfIA6aED6AgZvUEehx');
    expect($transactions[1]->state)->toBe(TransactionState::GatewaySetupFailed->value);
});

test('a payload with no transactions parses into nothing', function (): void {
    expect(Transaction::fromCallbackPayload([]))->toBe([]);
    expect(Transaction::fromCallbackPayload(['transactions' => 'not-a-list']))->toBe([]);
});

test('a genuine signature verifies against the signing secret', function (): void {
    $tx = Transaction::fromCallbackPayload($this->loadFixture('transactions/offsite_callback.json'))[0];

    expect($tx->signed['algorithm'])->toBe('sha1');
    expect($tx->verifySignature(SIGNING_SECRET))->toBeTrue();
});

test('a tampered field breaks the signature', function (): void {
    $payload = $this->loadFixture('transactions/offsite_callback.json');
    $payload['transactions'][0]['amount'] = 1;

    $tx = Transaction::fromCallbackPayload($payload)[0];

    expect($tx->verifySignature(SIGNING_SECRET))->toBeFalse();
});

test('the wrong secret breaks the signature', function (): void {
    $tx = Transaction::fromCallbackPayload($this->loadFixture('transactions/offsite_callback.json'))[0];

    expect($tx->verifySignature('not-the-signing-secret'))->toBeFalse();
});

test('an unsigned transaction never verifies', function (): void {
    $tx = Transaction::fromCallbackPayload($this->loadFixture('transactions/offsite_callback.json'))[1];

    expect($tx->signed)->toBe([]);
    expect($tx->verifySignature(SIGNING_SECRET))->toBeFalse();
});

test('an unknown algorithm never verifies', function (): void {
    $payload = $this->loadFixture('transactions/offsite_callback.json');
    $payload['transactions'][0]['signed']['algorithm'] = 'rot13';

    $tx = Transaction::fromCallbackPayload($payload)[0];

    expect($tx->verifySignature(SIGNING_SECRET))->toBeFalse();
});

test('a signed field the payload does not carry signs as empty', function (): void {
    $payload = $this->loadFixture('transactions/offsite_callback.json');
    $payload['transactions'][0]['signed']['fields'] = 'token missing_field';
    $payload['transactions'][0]['signed']['signature'] = hash_hmac('sha1', '5AG4P7FPjlfIA6aED6AgZvUEehx|', SIGNING_SECRET);

    $tx = Transaction::fromCallbackPayload($payload)[0];

    expect($tx->verifySignature(SIGNING_SECRET))->toBeTrue();
});

test('a signed field holding a nested value signs as empty', function (): void {
    $payload = $this->loadFixture('transactions/offsite_callback.json');
    $payload['transactions'][0]['signed']['fields'] = 'token api_urls';
    $payload['transactions'][0]['signed']['signature'] = hash_hmac('sha1', '5AG4P7FPjlfIA6aED6AgZvUEehx|', SIGNING_SECRET);

    $tx = Transaction::fromCallbackPayload($payload)[0];

    expect($tx->verifySignature(SIGNING_SECRET))->toBeTrue();
});

test('api_urls survives as the hash Spreedly sends', function (): void {
    $tx = Transaction::fromCallbackPayload($this->loadFixture('transactions/offsite_callback.json'))[0];

    expect($tx->apiUrls)->toHaveKey('callback_conversations');
    expect($tx->toArray()['api_urls'])->toBe($tx->apiUrls);
});

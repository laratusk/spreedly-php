<?php

declare(strict_types=1);

/**
 * Proves that the paths corrected in 2.0.0 are the ones Spreedly serves.
 *
 * Each is exercised with tokens that genuinely exist, so a 404 can only mean the
 * path is wrong. A 422 passes: the route was understood and the body rejected.
 */
test('a route that does not exist answers 404, so 404 is a meaningful signal', function (): void {
    $this->assertSame(404, $this->httpStatus('GET', 'definitely_not_a_spreedly_route.json'));
});

test('composer transactions are served under /transactions', function (): void {
    $body = ['transaction' => [
        'payment_method_token' => $this->sandboxToken('payment method'),
        'amount' => 100,
        'currency_code' => 'USD',
    ]];

    $this->assertRouteExists('POST', 'transactions/purchase.json', $body);
    $this->assertRouteExists('POST', 'transactions/authorize.json', $body);
    $this->assertRouteExists('POST', 'transactions/verify.json', $body);
});

test('protection events are served under /protection/events', function (): void {
    $this->assertRouteExists('GET', 'protection/events.json');
    $this->assertRouteExists('GET', 'protection/events.json', ['order' => 'asc', 'count' => 5, 'state' => 'succeeded']);
});

test('protection providers are served under /protection/providers', function (): void {
    $this->assertRouteExists('POST', 'protection/providers.json', [
        'merchant_profile_key' => $this->sandboxToken('merchant profile'),
        'type' => 'test',
    ]);
});

test('claims are served under /protection/{transaction_token}/claims', function (): void {
    $this->assertRouteExists('POST', "protection/{$this->sandboxToken('transaction')}/claims.json", [
        'claim' => ['reason_type' => 'FRAUD', 'amount' => 100, 'currency' => 'USD'],
    ]);
});

test('sca providers are served under /sca/providers', function (): void {
    $this->assertRouteExists('POST', 'sca/providers.json', [
        'merchant_profile_key' => $this->sandboxToken('merchant profile'),
        'type' => 'spreedly',
    ]);
});

test('network tokenization is served under /network_tokenization', function (): void {
    $query = ['payment_method_token' => $this->sandboxToken('payment method')];

    $this->assertRouteExists('GET', 'network_tokenization/card_metadata.json', $query);
    $this->assertRouteExists('GET', 'network_tokenization/token_status.json', $query);
});

test('card refresher inquiries are listed under /card_refresher/inquiries', function (): void {
    $this->assertRouteExists('GET', 'card_refresher/inquiries.json');
    $this->assertRouteExists('POST', 'card_refresher/inquiry.json', [
        'card_refresher_inquiry' => [
            'payment_method_token' => $this->sandboxToken('payment method'),
            'region' => 'NA',
        ],
    ]);
});

/**
 * Spreedly has no endpoint to delete or redact a certificate, and it accepts an
 * incomplete body rather than rejecting it, so there is no way to exercise this route
 * without leaving a record behind forever. By default only the superseded path is
 * probed, which is side-effect free; generating a real certificate is opt-in.
 */
test('the certificate generation path corrected in 2.0.0 is the one that exists', function (): void {
    $this->assertSame(
        404,
        $this->httpStatus('POST', 'certificates/SomeCertificateToken/generate.json'),
        'The superseded per-certificate generate path unexpectedly still resolves.',
    );

    if (getenv('SPREEDLY_INTEGRATION_CREATE_CERTIFICATE') !== 'true') {
        $this->markTestSkipped('Creates an undeletable certificate: set SPREEDLY_INTEGRATION_CREATE_CERTIFICATE=true to verify.');
    }

    $this->assertRouteExists('POST', 'certificates/generate.json', [
        'certificate' => ['algorithm' => 'ec-prime256v1', 'cn' => 'SDK integration test'],
    ]);
});

test('storing at a gateway is served under /gateways/{gateway_token}/store', function (): void {
    $this->assertRouteExists('POST', "gateways/{$this->sandboxToken('gateway')}/store.json", [
        'transaction' => ['payment_method_token' => $this->sandboxToken('payment method')],
    ]);
});

test('the payment method a store mints is tracked for cleanup', function (): void {
    $tx = $this->spreedly->paymentMethods->store($this->sandboxToken('gateway'), [
        'payment_method_token' => $this->sandboxToken('payment method'),
    ]);

    $this->trackPaymentMethod($tx->paymentMethod?->token);

    expect($tx->paymentMethod?->token)->not->toBeEmpty();
});

test('reference purchase is served under /transactions/{transaction_token}/purchase', function (): void {
    $this->assertRouteExists('POST', "transactions/{$this->sandboxToken('transaction')}/purchase.json", [
        'transaction' => ['amount' => 100],
    ]);
});

test('deleting metadata accepts a request body', function (): void {
    $this->assertRouteExists('DELETE', "payment_methods/{$this->sandboxToken('payment method')}/metadata.json", [
        'keys' => ['a_key_that_is_not_set'],
    ]);
});

test('retain accepts provision_network_token', function (): void {
    $this->assertRouteExists('PUT', "payment_methods/{$this->sandboxToken('payment method')}/retain.json", [
        'provision_network_token' => false,
    ]);
});

test('the documented list filters are accepted', function (): void {
    $this->assertRouteExists('GET', 'transactions.json', ['order' => 'desc', 'state' => 'succeeded', 'count' => 5]);
    $this->assertRouteExists('GET', 'payment_methods.json', ['order' => 'desc', 'state' => 'retained', 'count' => 5]);
    $this->assertRouteExists('GET', 'events.json', ['order' => 'desc', 'count' => 5]);
    $this->assertRouteExists('GET', 'payment_methods/events.json', ['count' => 5, 'include_transactions' => true]);
    $this->assertRouteExists('GET', 'gateways.json', ['order' => 'desc', 'count' => 5]);
    $this->assertRouteExists('GET', 'merchant_profiles.json', ['order' => 'asc', 'count' => 5]);
    $this->assertRouteExists('GET', 'certificates.json', ['order' => 'asc']);
    $this->assertRouteExists('GET', "gateways/{$this->sandboxToken('gateway')}/transactions.json", ['state' => 'succeeded']);
});

/**
 * Sub merchants and environments are organization-scoped, so environment credentials
 * cannot reach them. Kept apart so skipping them hides nothing else.
 */
test('the organization scoped list filters are accepted', function (): void {
    $this->assertRouteExists('GET', 'sub_merchants.json', ['order' => 'asc', 'count' => 5]);
    $this->assertRouteExists('GET', 'environments.json', ['order' => 'asc', 'count' => 5]);
});

/**
 * Spreedly answers 401 for a route that exists but is out of scope for these
 * credentials, and 404 for one that does not exist — so the corrected signing secret
 * path can be proven without actually rotating the secret and invalidating every
 * callback signature the environment has already issued.
 */
test('the signing secret path corrected in 2.0.0 is the one that exists', function (): void {
    $environmentKey = (string) getenv('SPREEDLY_ENVIRONMENT_KEY');

    $corrected = $this->httpStatus('POST', "environments/{$environmentKey}/regenerate_signing_secret.json");
    $superseded = $this->httpStatus('POST', 'environments/regenerate_signing_secret.json');

    $this->assertNotSame(404, $corrected, 'The corrected signing secret path is not served by Spreedly.');
    $this->assertSame(404, $superseded, 'The superseded signing secret path unexpectedly still resolves.');
});

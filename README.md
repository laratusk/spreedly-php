# Spreedly PHP SDK

A production-ready PHP SDK for the [Spreedly payment orchestration API](https://spreedly.com), following the Stripe PHP SDK architecture. Works as a standalone PHP library or as a Laravel package.

## Requirements

- PHP ^8.2
- Laravel ^10.0 || ^11.0 || ^12.0 || ^13.0 (optional)

## Installation

```bash
composer require laratusk/spreedly
```

## Standalone PHP Usage

```php
$spreedly = new \Laratusk\Spreedly\SpreedlyClient(
    environmentKey: 'your_environment_key',
    accessSecret: 'your_access_secret',
);
```

### Configuration Options

```php
$spreedly = new \Laratusk\Spreedly\SpreedlyClient(
    environmentKey: 'your_environment_key',
    accessSecret: 'your_access_secret',
    options: [
        'base_url'        => 'https://core.spreedly.com/v1/',
        'timeout'         => 30,
        'connect_timeout' => 10,
        'retries'         => 3,
    ],
);
```

## Laravel Usage

Publish the config file:

```bash
php artisan vendor:publish --provider="Laratusk\Spreedly\Laravel\SpreedlyServiceProvider"
```

Add credentials to your `.env`:

```env
SPREEDLY_ENVIRONMENT_KEY=your_environment_key
SPREEDLY_ACCESS_SECRET=your_access_secret
```

Use the facade:

```php
use Laratusk\Spreedly\Laravel\Facades\Spreedly;

$gateway = Spreedly::gateways()->create(['gateway_type' => 'test']);
$transaction = Spreedly::transactions()->purchase($gateway->token, [
    'payment_method_token' => 'pm_token',
    'amount' => 1000,
    'currency_code' => 'USD',
]);
```

Or inject the client:

```php
use Laratusk\Spreedly\SpreedlyClient;

class PaymentController extends Controller
{
    public function __construct(private readonly SpreedlyClient $spreedly) {}

    public function charge(Request $request)
    {
        $transaction = $this->spreedly->transactions->purchase(
            gatewayToken: config('spreedly.gateway_token'),
            params: [
                'payment_method_token' => $request->payment_method_token,
                'amount' => $request->amount, // in cents
                'currency_code' => 'USD',
            ],
        );

        if (! $transaction->succeeded) {
            throw new \Exception("Payment failed: {$transaction->message}");
        }

        return $transaction;
    }
}
```

## Certificate Automation (Laravel)

Spreedly supports [certificate pinning](https://developer.spreedly.com/reference/certificates) for additional API security. The SDK can automatically generate, upload, and renew self-signed certificates on a per-machine basis, binding each certificate to the machine's MAC address so that multi-server deployments each maintain their own certificate.

### Setup

Publish and run the migration:

```bash
php artisan vendor:publish --tag="spreedly-migrations"
php artisan migrate
```

Add the relevant variables to your `.env`:

```env
# Optional: override MAC address auto-detection (e.g. in containerised environments)
SPREEDLY_MAC_ADDRESS=aa:bb:cc:dd:ee:ff

# Certificate settings (optional — shown with defaults):
SPREEDLY_CERTIFICATE_DAYS_VALID=365
SPREEDLY_CERTIFICATE_KEY_BITS=2048
SPREEDLY_CERTIFICATE_EXPIRING_DAYS=7
```

### How it works

Each server keeps exactly **one active certificate** at a time, identified by its MAC address. The key pair is generated locally (the private key never leaves the server), then uploaded to Spreedly. The encrypted private key is stored in your database.

| Scenario | Behaviour |
|---|---|
| No certificate exists | A new certificate is created and uploaded |
| Certificate expires within threshold (default: 7 days) | Certificate is renewed; old record is deleted |
| Certificate is still valid | No action taken |
| `--force` flag | Certificate is replaced immediately regardless of expiry |

### Artisan command

```bash
# Normal: renew only if expiring within the configured threshold
php artisan spreedly:certificate-install

# Force-replace the current certificate immediately
php artisan spreedly:certificate-install --force
```

### Scheduled auto-renewal

Register the command in your scheduler so certificates are renewed automatically. Running it once a day is sufficient — the command exits immediately when the certificate is not close to expiring.

**Laravel 11+ (`routes/console.php`):**

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('spreedly:certificate-install')
    ->dailyAt('02:00')
    ->runInBackground()
    ->withoutOverlapping()
    ->onFailure(function () {
        // alert your team
    });
```

**Laravel 10 (`app/Console/Kernel.php`):**

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('spreedly:certificate-install')
        ->dailyAt('02:00')
        ->runInBackground()
        ->withoutOverlapping();
}
```

> **Tip:** Set `SPREEDLY_CERTIFICATE_EXPIRING_DAYS` to control how many days before expiry a renewal is triggered. The default is `7`.

### Resolving the current certificate

Retrieve the active certificate for the current machine at runtime:

```php
use Laratusk\Spreedly\Laravel\Models\SpreedlyCertificate;

// Returns the certificate for this machine; creates one automatically if none exists.
$certificate = SpreedlyCertificate::current();

$certificate->getPem();           // PEM-encoded certificate body
$certificate->getPublicKey();     // RSA public key
$certificate->getPublicKeyHash(); // base64(sha256(publicKey)) — for TLS pinning
$certificate->getToken();         // Spreedly certificate token
$certificate->getPrivateKey();    // Decrypted private key PEM
```

---

## Resources

### Gateways

> **Docs:** [Gateways API](https://developer.spreedly.com/reference/gateways)

```php
// Create a gateway
$gateway = $spreedly->gateways->create([
    'gateway_type' => 'stripe',
    'login' => 'sk_test_xxx',
]);

// Retrieve
$gateway = $spreedly->gateways->retrieve('gateway_token');

// List (with pagination)
$gateways = $spreedly->gateways->list();
foreach ($gateways->autoPaginate() as $gateway) {
    echo $gateway->token;
}

// Update
$gateway = $spreedly->gateways->update('gateway_token', ['description' => 'New description']);

// Redact (removes sensitive credentials)
$spreedly->gateways->redact('gateway_token');

// Retain
$spreedly->gateways->retain('gateway_token');
```

### Payment Methods

> **Docs:** [Payment Methods API](https://developer.spreedly.com/reference/payment-methods)

```php
// Create/tokenize (note: usually done via Spreedly Express or iframe)
$pm = $spreedly->paymentMethods->create([
    'credit_card' => [
        'number' => '4111111111111111',
        'month' => '12',
        'year' => '2025',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'verification_value' => '123',
    ],
]);

// Retrieve
$pm = $spreedly->paymentMethods->retrieve('pm_token');

// List
$pms = $spreedly->paymentMethods->list();

// Update
$spreedly->paymentMethods->update('pm_token', ['first_name' => 'Jane']);

// Retain (prevent auto-removal), optionally provisioning a network token
$spreedly->paymentMethods->retain('pm_token');
$spreedly->paymentMethods->retain('pm_token', provisionNetworkToken: true);

// Redact (remove sensitive data)
$spreedly->paymentMethods->redact('pm_token');

// Recache CVV
$spreedly->paymentMethods->recache('pm_token', ['verification_value' => '456']);

// Copy the card into a gateway's own vault (third-party vaulting).
// The first argument is the gateway to store at, not the payment method.
$spreedly->paymentMethods->store('gw_token', ['payment_method_token' => 'pm_token']);

// Remove specific metadata keys
$spreedly->paymentMethods->deleteMetadata('pm_token', ['plan', 'campaign']);

// Filter the list
$pms = $spreedly->paymentMethods->list(
    state: 'retained',
    metadata: 'plan:pro',
    count: 100,
);
```

### Transactions

> **Docs:** [Transactions API](https://developer.spreedly.com/reference/transactions)
>
> **Note:** All monetary amounts are in the smallest currency unit (cents for USD). `1000` = $10.00.

```php
// Purchase (charge immediately)
$purchase = $spreedly->transactions->purchase('gateway_token', [
    'payment_method_token' => 'pm_token',
    'amount' => 1000,          // $10.00 in cents
    'currency_code' => 'USD',
    'retain_on_success' => true,
]);

if ($purchase->succeeded) {
    echo "Charged: {$purchase->amount} {$purchase->currencyCode}";
}

// Authorize (reserve funds)
$auth = $spreedly->transactions->authorize('gateway_token', [
    'payment_method_token' => 'pm_token',
    'amount' => 1000,
    'currency_code' => 'USD',
]);

// Capture (charge a previous authorization)
$capture = $spreedly->transactions->capture($auth->token, ['amount' => 1000]);

// Void (cancel before settlement)
$void = $spreedly->transactions->void($purchase->token);

// Credit/Refund
$refund = $spreedly->transactions->credit($purchase->token, ['amount' => 500]); // partial refund

// General credit (not tied to existing transaction)
$spreedly->transactions->generalCredit('gateway_token', [
    'payment_method_token' => 'pm_token',
    'amount' => 1000,
    'currency_code' => 'USD',
]);

// Verify (zero-dollar authorization)
$spreedly->transactions->verify('gateway_token', [
    'payment_method_token' => 'pm_token',
]);

// Retrieve a transaction
$tx = $spreedly->transactions->retrieve('transaction_token');

// List transactions, optionally filtered by state and page size
$transactions = $spreedly->transactions->list(state: 'gateway_processing_failed', count: 100);

// Get transcript (raw gateway communication)
$transcript = $spreedly->transactions->transcript('transaction_token');

// Reference purchase — charge again against a previous transaction, reusing its
// payment method and stored credential details
$again = $spreedly->transactions->referencePurchase($purchase->token, ['amount' => 800]);
```

### Receivers

> **Docs:** [Receivers API](https://developer.spreedly.com/reference/receivers)

```php
$receiver = $spreedly->receivers->create([
    'receiver_type' => 'oauth2_bearer',
    'credentials' => [
        ['name' => 'access_token', 'value' => 'token_here'],
    ],
    'hostnames' => ['api.example.com'],
]);

$receiver = $spreedly->receivers->retrieve('receiver_token');
$receivers = $spreedly->receivers->list();
$spreedly->receivers->update('receiver_token', [...]);
$spreedly->receivers->redact('receiver_token');
$spreedly->receivers->deliver('receiver_token', [...]);
```

### Certificates

> **Docs:** [Certificates API](https://developer.spreedly.com/reference/certificates)

```php
$cert = $spreedly->certificates->create([...]);
$certs = $spreedly->certificates->list();
$spreedly->certificates->update('cert_token', [...]);

// Have Spreedly generate the key pair, so the private key never leaves their vault
$cert = $spreedly->certificates->generate([
    'algorithm' => 'ec-prime256v1',
    'cn' => 'MyApp ApplePay Production Certificate',
]);
```

### Environments

> **Docs:** [Environments API](https://developer.spreedly.com/reference/environments)

```php
$envs = $spreedly->environments->list();
$env = $spreedly->environments->create([...]);
$env = $spreedly->environments->retrieve('env_token');
$spreedly->environments->update('env_token', [...]);
$spreedly->environments->regenerateSigningSecret('env_key');
```

### Events

> **Docs:** [Events API](https://developer.spreedly.com/reference/events)

Environment events record what changed and which object it changed. They are
identified by `id`, not a token.

```php
$events = $spreedly->events->list(count: 100);

foreach ($events->items as $event) {
    echo "{$event->eventType} on {$event->objectType} {$event->objectKey}";
}

$event = $spreedly->events->retrieve($events->items[0]->id);
```

> Payment method events are a **different** resource with a different shape — see
> [Payment Method Events](#payment-method-events) below.

### Merchant Profiles

> **Docs:** [Merchant Profiles API](https://developer.spreedly.com/reference/merchant-profiles)

```php
$profile = $spreedly->merchantProfiles->create([...]);
$profiles = $spreedly->merchantProfiles->list();
$profile = $spreedly->merchantProfiles->retrieve('token');
$spreedly->merchantProfiles->update('token', [...]);
```

### Composer (Workflows)

> **Docs:** [Composer API](https://developer.spreedly.com/reference/composer)

```php
$spreedly->composer->authorize([...]);
$spreedly->composer->purchase([...]);
$spreedly->composer->verify([...]);
```

### SCA Authentication

> **Docs:** [SCA Authentication API](https://developer.spreedly.com/reference/sca-authentication)

```php
$spreedly->scaAuthentication->authenticate('sca_provider_key', [
    'payment_method_token' => 'pm_token',
    'amount' => 1000,
    'currency_code' => 'EUR',
]);
```

### Asynchronous transactions (3DS2 and offsite)

> **Docs:** [Gateway Specific 3DS2 Guide](https://developer.spreedly.com/docs/gateway-specific-3ds2-guide)

A transaction that needs the cardholder to authenticate comes back in the `pending`
state carrying somewhere to send them:

```php
$tx = $spreedly->transactions->purchase($gatewayToken, [
    'payment_method_token' => $token,
    'amount'               => 3004,
    'currency_code'        => 'EUR',
    'attempt_3dsecure'     => true,
    'three_ds_version'     => '2',
    'redirect_url'         => 'https://merchant.example/checkout/return',
    'callback_url'         => 'https://merchant.example/spreedly/callback',
    'browser_info'         => $browserInfo,
]);

if ($tx->requiresCardholderAction()) {
    // Hand the cardholder over, then complete the transaction afterwards.
    return redirect($tx->checkoutUrl);
}
```

| Property | Description |
| --- | --- |
| `checkoutUrl` | Issuer page to send the cardholder to |
| `checkoutForm` | Pre-built form to post instead of redirecting |
| `redirectUrl` | Where the cardholder lands afterwards |
| `callbackUrl` | Where Spreedly POSTs state changes |
| `setupResponse` | Result of setting the transaction up on the gateway |
| `redirectResponse` | Result of the cardholder returning |
| `callbackResponse` | Result delivered out of band |

Finish the transaction with `$spreedly->transactions->complete($tx->token)`.

The three sub-responses often hold error detail that the top-level `message` does
not, which is what makes a failed authentication diagnosable.

### Callbacks and signed requests

> **Docs:** [Signed requests](https://developer.spreedly.com/docs/signed-requests)

The redirect back to your site is not guaranteed to happen, so Spreedly also POSTs
every transaction that changed to your `callback_url`. That channel is not
authenticated, so the critical fields are signed with the environment's signing
secret — never an access secret.

```php
$transactions = Transaction::fromCallbackPayload($request->json()->all());

foreach ($transactions as $tx) {
    if (! $tx->verifySignature(config('services.spreedly.signing_secret'))) {
        abort(400);
    }

    // Safe to act on.
    $order->markPaid($tx->token);
}
```

`verifySignature()` recomputes the HMAC over exactly the fields the payload says
were signed, and compares in constant time. An unsigned transaction returns
`false`, so "not signed" and "signed but tampered with" fail the same way.

Roll the secret with `$spreedly->environments->regenerateSigningSecret('env_key')`.

### Raw payloads

Every `Transaction` and `PaymentMethod` keeps the payload it was built from, so a
field the SDK does not model yet is still reachable:

```php
$tx->raw['some_new_field'];
$tx->paymentMethod->raw['third_party_token'];
```

Typed properties stay the supported surface; `raw` is the escape hatch for
gateway-specific extras and for fields Spreedly adds between SDK releases.

### Sub Merchants

> **Docs:** [Sub Merchants API](https://developer.spreedly.com/reference/sub-merchants)

```php
$spreedly->subMerchants->create([...]);
$spreedly->subMerchants->list();
$spreedly->subMerchants->retrieve('token');
$spreedly->subMerchants->update('token', [...]);
```

### Card Refresher

> **Docs:** [Card Refresher API](https://developer.spreedly.com/reference/card-refresher)

Keeps stored payment methods up-to-date by fetching the latest card details from card networks.

```php
// Submit a card for refreshing
$inquiry = $spreedly->cardRefresher->create([
    'payment_method_token' => 'pm_token',
    'region' => 'NA',
]);

// Retrieve an existing inquiry
$inquiry = $spreedly->cardRefresher->retrieve('inquiry_token');

// List all inquiries
$inquiries = $spreedly->cardRefresher->list();
```

### Claim

> **Docs:** [Claim API](https://developer.spreedly.com/reference/claim)

Forward a chargeback claim for a transaction to its protection provider.

```php
$result = $spreedly->claim->create('transaction_token', [
    'reason_type' => 'FRAUD',
    'amount' => 1000,
    'currency' => 'USD',
]);
```

### Payments

> **Docs:** [Payments API](https://developer.spreedly.com/reference/payments)

```php
$payment = $spreedly->payments->retrieve('payment_token');
```

### Protection Events

> **Docs:** [Protection Events API](https://developer.spreedly.com/reference/protection-events)

Protection events are created when Spreedly detects a change to a stored payment method (e.g. updated card number or expiration date).

```php
// List all protection events
$events = $spreedly->protectionEvents->list(state: 'succeeded', count: 100);

// Retrieve a specific event
$event = $spreedly->protectionEvents->retrieve('event_token');

echo $event->eventType;           // e.g. 'card_updated'
echo $event->paymentMethodToken;
```

### Access Secrets (Environments)

> **Docs:** [Access Secrets API](https://developer.spreedly.com/reference/access-secrets)

```php
// Create an access secret for an environment
$secret = $spreedly->environments->createAccessSecret('env_token', [
    'name' => 'Production Key',
    'description' => 'Used by the payments service',
]);

// List all access secrets
$secrets = $spreedly->environments->listAccessSecrets('env_token');

// Retrieve a specific access secret
$secret = $spreedly->environments->retrieveAccessSecret('env_token', 'secret_token');

// Delete an access secret
$spreedly->environments->deleteAccessSecret('env_token', 'secret_token');
```

### Network Tokenization (Payment Methods)

> **Docs:** [Network Tokenization API](https://developer.spreedly.com/reference/network-tokenization)

```php
// Get network token metadata
$metadata = $spreedly->paymentMethods->networkTokenizationMetadata('pm_token');

// Get network token status
$status = $spreedly->paymentMethods->networkTokenizationStatus('pm_token');
```

### Payment Method Events

> **Docs:** [Payment Method Events API](https://developer.spreedly.com/reference/payment-method-events)

```php
// List all payment method events (across all payment methods)
$events = $spreedly->paymentMethods->listEvents(eventType: 'card_updated', includeTransactions: true);

// List events for a specific payment method
$events = $spreedly->paymentMethods->listEventsForPaymentMethod('pm_token');

// Retrieve a specific event; these are PaymentMethodEvent, not Event
$event = $spreedly->paymentMethods->retrieveEvent('event_token');

echo $event->eventType;         // e.g. 'card_updated'
echo $event->paymentMethodKey;
$event->eventData;              // what changed

// Update a payment method without a charge (gratis)
$pm = $spreedly->paymentMethods->updateGratis('pm_token', [
    'month' => '12',
    'year'  => '2027',
]);
```

### Protection Provider & SCA Provider (Merchant Profiles)

> **Docs:** [Merchant Profiles API](https://developer.spreedly.com/reference/merchant-profiles)

```php
Providers are created on a merchant profile but are retrieved by their own token.
At least one card type object must be given.

```php
// Protection provider
$provider = $spreedly->merchantProfiles->createProtectionProvider('mp_token', [
    'type' => 'spreedly',
    'visa' => [...],
]);
$spreedly->merchantProfiles->retrieveProtectionProvider($provider['protection_provider']['token']);

// SCA provider
$sca = $spreedly->merchantProfiles->createScaProvider('mp_token', [
    'type' => 'spreedly',
    'visa' => [...],
]);
$spreedly->merchantProfiles->retrieveScaProvider($sca['sca_provider']['token']);
```

## Pagination

Spreedly uses token-based pagination (`since_token`). The SDK provides a `PaginatedCollection` that handles this:

```php
// Fetch first page
$gateways = $spreedly->gateways->list();

// Fetch next page manually
$nextPage = $gateways->nextPage();

// Auto-paginate through all pages (lazy generator)
foreach ($gateways->autoPaginate() as $gateway) {
    echo $gateway->token . "\n";
}

// Standard iteration (current page only)
foreach ($gateways as $gateway) {
    echo $gateway->token . "\n";
}

// Count items on current page
echo count($gateways);
```

## Error Handling

```php
use Laratusk\Spreedly\Exceptions\AuthenticationException;
use Laratusk\Spreedly\Exceptions\InvalidRequestException;
use Laratusk\Spreedly\Exceptions\NotFoundException;
use Laratusk\Spreedly\Exceptions\RateLimitException;
use Laratusk\Spreedly\Exceptions\ApiException;
use Laratusk\Spreedly\Exceptions\TimeoutException;
use Laratusk\Spreedly\Exceptions\SpreedlyException;

try {
    $gateway = $spreedly->gateways->retrieve('invalid_token');
} catch (AuthenticationException $e) {
    // 401 - Invalid credentials
    echo $e->getMessage();
} catch (NotFoundException $e) {
    // 404 - Resource not found
    echo $e->getMessage();
} catch (InvalidRequestException $e) {
    // 422 - Validation errors
    foreach ($e->errors as $error) {
        echo $error['message'];
    }
} catch (RateLimitException $e) {
    // 429 - Too many requests
    sleep(1);
} catch (ApiException $e) {
    // 500+ - Server error
    echo "Status: {$e->httpStatus}";
} catch (TimeoutException $e) {
    // Connection timeout
} catch (SpreedlyException $e) {
    // Any other Spreedly error
}
```

All exceptions extend `SpreedlyException` and provide:
- `$e->getMessage()` — Human-readable message
- `$e->httpStatus` — HTTP status code
- `$e->errors` — Array of validation errors (for 422)
- `$e->spreedlyErrorKey` — Spreedly error key (e.g., `errors.not_found`)

## Custom HTTP Transport

Implement `TransporterInterface` to use a custom HTTP client:

```php
use Laratusk\Spreedly\Contracts\TransporterInterface;

class MyTransporter implements TransporterInterface
{
    public function get(string $endpoint, array $query = []): array { ... }
    public function post(string $endpoint, array $payload = []): array { ... }
    public function put(string $endpoint, array $payload = []): array { ... }
    public function patch(string $endpoint, array $payload = []): array { ... }
    public function delete(string $endpoint, array $query = []): array { ... }
    public function getRaw(string $endpoint): string { ... }
}

$spreedly = new SpreedlyClient(
    environmentKey: 'key',
    accessSecret: 'secret',
    transporter: new MyTransporter(),
);
```

## Testing

### Testing in Your Application

The SDK ships with `SpreedlyFake` and `MockTransporter` to make testing easy — no real HTTP calls, no Spreedly credentials needed.

#### Standalone PHP

```php
use Laratusk\Spreedly\Testing\SpreedlyFake;

$fake = SpreedlyFake::make();

// Configure responses before making calls
$fake->mock->addResponse('GET', 'gateways/gw_token.json', [
    'gateway' => [
        'token'        => 'gw_token',
        'gateway_type' => 'test',
        'name'         => 'Test',
        'state'        => 'retained',
        // ...
    ],
]);

$gateway = $fake->client()->gateways->retrieve('gw_token');

assert($gateway->token === 'gw_token');

// Assert that the expected call was made
$fake->mock->assertCalled('GET', 'gateways/gw_token.json');

// Count how many calls were made
echo $fake->mock->getCallCount(); // 1
```

#### Laravel (swap the container binding)

In your Laravel feature tests, swap the `SpreedlyClient` binding before the code under test runs. After the swap the `Spreedly` facade automatically uses the fake.

```php
use Laratusk\Spreedly\Laravel\Facades\Spreedly;
use Laratusk\Spreedly\SpreedlyClient;
use Laratusk\Spreedly\Testing\SpreedlyFake;

class PaymentTest extends TestCase
{
    public function test_purchase_succeeds(): void
    {
        $fake = SpreedlyFake::make();

        // Register a canned response for the endpoint your code will hit
        $fake->mock->addResponse('POST', 'gateways/gw_token/purchase.json', [
            'transaction' => [
                'token'            => 'tx_abc123',
                'transaction_type' => 'Purchase',
                'succeeded'        => true,
                'amount'           => 1000,
                'currency_code'    => 'USD',
                'state'            => 'succeeded',
                'message'          => 'Succeeded!',
                'created_at'       => now()->toIso8601String(),
                'updated_at'       => now()->toIso8601String(),
            ],
        ]);

        // Swap the real client for the fake one
        $this->app->instance(SpreedlyClient::class, $fake->client());

        // Call your application code (which uses the Spreedly facade internally)
        $response = $this->postJson('/api/charge', [
            'payment_method_token' => 'pm_token',
            'amount'               => 1000,
        ]);

        $response->assertOk();

        // Verify Spreedly was actually called
        $fake->mock->assertCalled('POST', 'gateways/gw_token/purchase.json');
    }
}
```

Or test the facade directly:

```php
$fake = SpreedlyFake::make();
$fake->mock->addResponse('GET', 'gateways/gw_token.json', ['gateway' => [...]]);

$this->app->instance(SpreedlyClient::class, $fake->client());

$gateway = Spreedly::gateways()->retrieve('gw_token');

expect($gateway->token)->toBe('gw_token');
$fake->mock->assertCalled('GET', 'gateways/gw_token.json');
```

#### `MockTransporter` API

| Method | Description |
|---|---|
| `addResponse(method, endpoint, array)` | Register a canned response. Chainable. |
| `assertCalled(method, endpoint)` | Throws `RuntimeException` if the call was never made. |
| `getCallCount()` | Total number of HTTP calls recorded. |

---

### Running the SDK's Own Tests

Run tests:

```bash
composer test
```

Run quality checks:

```bash
composer quality
```

### Integration Tests

Unit tests mock the transporter, so they can only confirm that the SDK sends the path
the test already expects. The integration suite calls the real API and proves that
every endpoint the SDK addresses is one Spreedly actually serves.

It creates a test gateway, a payment method and a transaction first, then exercises
each endpoint with tokens that genuinely exist — so a `404` can only mean the path is
wrong, never that a record is missing. A `422` passes: the route was understood and
the body rejected, which is all the assertion is about.

```bash
SPREEDLY_INTEGRATION=true \
SPREEDLY_ENVIRONMENT_KEY=your_key \
SPREEDLY_ACCESS_SECRET=your_secret \
vendor/bin/pest --testsuite Integration
```

Without `SPREEDLY_INTEGRATION=true` the suite skips, so CI stays green without
credentials.

Use a **test/sandbox environment**. The suite creates real records (a gateway, a
payment method, transactions, a merchant profile, a certificate) and does not clean
them up.

Nothing destructive runs. Spreedly answers `401` for a route that exists but is out of
scope for the credentials and `404` for one that does not exist, so endpoints that
cannot be called safely — regenerating the signing secret would invalidate every
callback signature already issued — are proven by that distinction instead.

Organization-scoped endpoints (sub merchants, environments) skip with a clear message
when only environment credentials are supplied.

## License

MIT. See [LICENSE.md](LICENSE.md).

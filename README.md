# Spreedly PHP SDK

A production-ready PHP SDK for the [Spreedly payment orchestration API](https://spreedly.com), following the Stripe PHP SDK architecture. Works as a standalone PHP library or as a Laravel package.

## Requirements

- PHP ^8.2
- Laravel ^10.0 || ^11.0 || ^12.0 (optional)

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
SPREEDLY_MAC_ADDRESS_ENABLED=true
SPREEDLY_MAC_ADDRESS_COMMAND="ifconfig en0 | awk '/ether/{print $2}'" #for mac
SPREEDLY_MAC_ADDRESS="your_machine_mac_address"
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

// Retain (prevent auto-removal)
$spreedly->paymentMethods->retain('pm_token');

// Redact (remove sensitive data)
$spreedly->paymentMethods->redact('pm_token');

// Recache CVV
$spreedly->paymentMethods->recache('pm_token', ['verification_value' => '456']);

// Store at gateway
$spreedly->paymentMethods->store('pm_token', ['gateway_token' => 'gw_token']);
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

// List transactions
$transactions = $spreedly->transactions->list();

// Get transcript (raw gateway communication)
$transcript = $spreedly->transactions->transcript('transaction_token');
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
$spreedly->certificates->generate('cert_token');
```

### Environments

> **Docs:** [Environments API](https://developer.spreedly.com/reference/environments)

```php
$envs = $spreedly->environments->list();
$env = $spreedly->environments->create([...]);
$env = $spreedly->environments->retrieve('env_token');
$spreedly->environments->update('env_token', [...]);
$spreedly->environments->regenerateSigningSecret();
```

### Events

> **Docs:** [Events API](https://developer.spreedly.com/reference/events)

```php
$events = $spreedly->events->list();
$event = $spreedly->events->retrieve('event_token');
```

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
$spreedly->scaAuthentication->authenticate([...]);
```

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
]);

// Retrieve an existing inquiry
$inquiry = $spreedly->cardRefresher->retrieve('inquiry_token');

// List all inquiries
$inquiries = $spreedly->cardRefresher->list();
```

### Claim

> **Docs:** [Claim API](https://developer.spreedly.com/reference/claim)

```php
$result = $spreedly->claim->create([
    'payment_method_token' => 'pm_token',
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
$events = $spreedly->protectionEvents->list();

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
$events = $spreedly->paymentMethods->listEvents();

// List events for a specific payment method
$events = $spreedly->paymentMethods->listEventsForPaymentMethod('pm_token');

// Retrieve a specific event
$event = $spreedly->paymentMethods->retrieveEvent('event_token');

// Update a payment method without a charge (gratis)
$pm = $spreedly->paymentMethods->updateGratis('pm_token', [
    'month' => '12',
    'year'  => '2027',
]);
```

### Protection Provider & SCA Provider (Merchant Profiles)

> **Docs:** [Merchant Profiles API](https://developer.spreedly.com/reference/merchant-profiles)

```php
// Protection provider
$spreedly->merchantProfiles->createProtectionProvider('mp_token', [
    'provider_type' => 'kount',
]);
$spreedly->merchantProfiles->retrieveProtectionProvider('mp_token');

// SCA provider
$spreedly->merchantProfiles->createScaProvider('mp_token', [
    'provider_type' => 'stripe_radar',
]);
$spreedly->merchantProfiles->retrieveScaProvider('mp_token');
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

Integration tests require real Spreedly credentials and run against the test gateway:

```bash
SPREEDLY_INTEGRATION=true \
SPREEDLY_ENVIRONMENT_KEY=your_key \
SPREEDLY_ACCESS_SECRET=your_secret \
SPREEDLY_MAC_ADDRESS_ENABLED=true  \
SPREEDLY_MAC_ADDRESS_COMMAND="ifconfig en0 | awk '/ether/{print $2}'" \
composer test -- --testsuite Integration
```

## License

MIT. See [LICENSE.md](LICENSE.md).

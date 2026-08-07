# Changelog

All notable changes to `laratusk/spreedly` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-08-08

### Changed — breaking

Correcting the endpoints below changed what their arguments mean, so every call site needs review:

| Method | Before | After |
| --- | --- | --- |
| `paymentMethods->store()` | payment method token | **gateway token**, with `payment_method_token` in the params |
| `transactions->referencePurchase()` | gateway token | **the referenced transaction's token** |
| `claim->create()` | params only | **transaction token** first |
| `scaAuthentication->authenticate()` | params only | **SCA provider key** first |
| `merchantProfiles->retrieve{Sca,Protection}Provider()` | merchant profile token | **the provider's own token** |
| `certificates->generate()` | certificate token | **params** (`algorithm`, `cn`, …) |
| `environments->regenerateSigningSecret()` | no argument | **environment key** |
| `Transaction::$apiUrls` | `?string` | `array` |
| `TransporterInterface::delete()` | `(string, array)` | `(string, array, array)` — custom implementations must add the third parameter |

### Added
- **An integration suite that calls the real API** (`vendor/bin/pest --testsuite Integration`). Mocked tests can only confirm that the SDK sends the path the test already expects, which is how the wrong paths survived. The suite creates a gateway, payment method and transaction first, then exercises each endpoint with tokens that genuinely exist, so a 404 can only mean the path is wrong. It skips without `SPREEDLY_INTEGRATION=true`, so CI is unaffected
- **Asynchronous transaction fields on `Transaction`** — `checkoutUrl`, `checkoutForm`, `redirectUrl`, `callbackUrl`, `setupResponse`, `redirectResponse` and `callbackResponse`. These are what a `pending` 3DS2 or offsite transaction carries, so the 3DS2 flow could not be driven from the typed DTO before: the SDK parsed the response and dropped every field describing where to send the cardholder
- **`Transaction::requiresCardholderAction()`** — true while the transaction is `pending` and has a checkout URL or form to hand the cardholder
- **`raw` on `Transaction` and `PaymentMethod`** — the payload each DTO was built from, so fields the SDK does not model yet (`third_party_token`, network-tokenisation detail, gateway-specific extras) remain reachable instead of being lost in parsing
- **`TransactionState::Processing` and `TransactionState::GatewaySetupFailed`** — both documented transaction states that the enum could not represent
- **Signed callback verification** — `Transaction::fromCallbackPayload()` parses the batch of transactions Spreedly POSTs to a `callback_url`, and `Transaction::verifySignature()` recomputes the HMAC over the signed fields in constant time. The `signed` block was previously dropped, so there was no way to tell a real callback from a forged one
- **`raw` on `Certificate` and `ProtectionEvent`** — same escape hatch as `Transaction` and `PaymentMethod`, reaching `public_key_hash` on a generated certificate and the fraud-check detail on a protection event
- **Documented list filters that the SDK never sent** — `count` on transactions, payment methods, events, payment method events, gateways, environments, merchant profiles, sub merchants, card refresher inquiries and protection events; `state` on transactions, payment methods, gateway transactions and protection events; `event_type` and `include_transactions` on events; `metadata` on payment methods; `order` on certificates, environments, merchant profiles, sub merchants, card refresher inquiries and protection events
- **`PaymentMethodResource::deleteMetadata()` now takes the keys to remove**, and `TransporterInterface::delete()` accepts a request body, which the endpoint requires
- **`PaymentMethodResource::retain()` accepts `provisionNetworkToken`**
- **Transaction and payment method type enum cases** — `Authorization`, `Verification`, `OffsitePurchase`, `OffsiteAuthorization`, `ExportPaymentMethods`, `ReplacePaymentMethod`, `ContactCardHolder`, `NoUpdate`, `Inquiry`, `Sca::Authentication`, and `PaymentMethodType::Sprel`

### Fixed
- **Ten endpoints were addressed at paths the API does not serve** and would have failed against Spreedly. Verified against the official OpenAPI description:
  - Composer: `composer/{authorize,purchase,verify}` → `transactions/{authorize,purchase,verify}`
  - SCA authentication: `sca_authentication/authenticate` → `sca/providers/{sca_provider_key}/authenticate`, which now takes the provider key
  - SCA and protection providers: `merchant_profiles/{token}/{sca,protection}_provider` → `sca/providers` and `protection/providers`, created with `merchant_profile_key` in the body and retrieved by the provider's own token
  - Protection events: `protection_events` → `protection/events`
  - Claims: `claim` → `protection/{transaction_token}/claims`, which now takes the transaction token
  - Network tokenization: `payment_methods/{token}/network_tokenization_{metadata,status}` → `network_tokenization/{card_metadata,token_status}?payment_method_token=`
  - Store at gateway: `payment_methods/{token}/store` → `gateways/{gateway_token}/store`, which now takes the gateway token
  - Reference purchase: it posted a fresh gateway purchase, and now posts `transactions/{transaction_token}/purchase` against the referenced transaction
  - Certificate generation: `certificates/{token}/generate` → `certificates/generate`, which takes the algorithm and subject rather than a token
  - Signing secret: `environments/regenerate_signing_secret` → `environments/{environment_key}/regenerate_signing_secret`
  - Card refresher listing: `card_refresher/inquiry` → `card_refresher/inquiries`, and creating one now uses the `card_refresher_inquiry` request envelope
- **`paymentMethods->create()` returned an empty payment method.** Creating one answers with a `transaction` envelope carrying the payment method, not a bare `payment_method`, so `PaymentMethod::fromArray()` found nothing and every field came back empty — including the token. The unit fixture had been written by hand with the wrong envelope, so the mocked test agreed with the bug. Found by the new integration suite, and the fixture is now a captured real response
- **`Transaction::$apiUrls` is an array**. Spreedly returns `api_urls` as a hash, so casting it to a string emitted an "Array to string conversion" warning and stored the literal `"Array"`

## [1.4.0] - 2026-08-05

### Added
- **Laravel 13 support** is now official and documented in the requirements — `Laravel ^10.0 || ^11.0 || ^12.0 || ^13.0`

### Changed
- Replaced the abandoned `nunomaduro/larastan` dev dependency with the maintained `larastan/larastan`, and updated the `phpstan.neon` extension include path accordingly
- The code-quality CI job (Pint, PHPStan, Rector) now runs against `laravel/framework:13.*`; the Laravel 10 and 11 test matrix legs install with `--no-blocking`, since those branches no longer receive security patches and Composer otherwise refuses to install them

### Fixed
- Removed redundant `errors: null` / `httpStatus: null` named arguments from the parent constructor call in `TransactionFailedException`

## [1.3.1] - 2026-04-28

### Added
- **`StoredCredentialInitiator` enum** (`cardholder`, `merchant`) for Spreedly stored-credential transactions
- **`StoredCredentialReasonType` enum** (`recurring`, `unscheduled`, `installment`)
- **`EnumToArray` trait** providing `keys()`, `values()`, `array()`, `flip()` and `only()` helpers for backed enums

## [1.3.0] - 2026-03-26

### Fixed
- Excluded PHP 8.2 from the Laravel 13 CI matrix — Laravel 13 requires PHP 8.3+

### Changed
- Code style: fully-qualified inline class references replaced with imported class names (Pint `fully_qualified_strict_types`) across the resource classes, `RetryMiddleware`, `PaginatedCollection` and the `SpreedlyCertificate` docblocks

## [1.2.0] - 2026-03-16

### Added
- **Laravel 13** added to the CI test matrix (PHPStan 2 / Larastan 3 are installed for that leg, as they are for Laravel 12)

### Changed
- `orchestra/testbench` dev dependency widened to `^8.0 || ^9.0 || ^10.0 || ^11.0` to allow testing against Laravel 13

## [1.1.0] - 2026-03-03

### Added
- **Certificate automation** (`spreedly:certificate-install` artisan command) — automatically generates, uploads, and renews self-signed certificates on a per-machine basis
- **`SpreedlyCertificate` Eloquent model** with MAC-address binding, encrypted private key storage, and `current()` factory method
- **`CreateSpreedlyCertificate` action** encapsulating key-pair generation, Spreedly API upload, and database persistence
- **`SpreedlyCertificateManager` facade** and `CertificateManagerInterface` contract for swappable certificate generation
- **`CertificateKeyPair` DTO** carrying `pem`, `privateKey`, `publicKey`, and `publicKeyHash`
- **`MacAddress` helper** with three-level caching: static property → Laravel app cache → shell command; MAC address is auto-detected by OS (Linux/macOS/Windows) and can be overridden via `SPREEDLY_MAC_ADDRESS`
- **`--force` flag** on `spreedly:certificate-install` to replace a non-expiring certificate immediately
- **Configurable expiry threshold** via `SPREEDLY_CERTIFICATE_EXPIRING_DAYS` (default: 7 days)
- **Database migration** for the `spreedly_certificates` table (publishable)
- **`SPREEDLY_CERTIFICATE_DAYS_VALID`** and **`SPREEDLY_CERTIFICATE_KEY_BITS`** env variables for certificate generation settings
- **GitHub Actions CI workflow** — code-quality job (Pint, PHPStan, Rector) + test matrix across PHP 8.2/8.3/8.4 and Laravel 10/11/12

### Fixed
- Restored `$statusCode >= 500` branch in `Transporter::handleErrorResponse()` that was accidentally removed

### Changed
- `CertificateManager` and `MacAddress` moved to `src/Laravel/` — they depend on Laravel facades and must not live outside the Laravel namespace
- `openssl_pkey_export()` and `openssl_x509_export()` return values are now checked; failures throw a `RuntimeException` with the OpenSSL error string

## [1.0.0] - 2026-02-27

### Added
- **15 resources** covering the full Spreedly API surface:
  - Gateways, Payment Methods, Transactions, Receivers, Certificates
  - Environments (+ Access Secrets), Events, Merchant Profiles (+ Protection Provider, SCA Provider)
  - Composer, SCA Authentication, Sub Merchants
  - Card Refresher, Claim, Payments, Protection Events
- **Payment Method** extras: network tokenization metadata/status, payment method events, `updateGratis`
- **Laravel integration** — service provider, `Spreedly` facade, published config file, supports Laravel 10/11/12
- **Token-based pagination** via `PaginatedCollection` with `nextPage()` and `autoPaginate()` generator
- **Strongly-typed DTOs** (`final readonly`) for all API responses with `fromArray()` / `toArray()`
- **Exception hierarchy** — `AuthenticationException`, `NotFoundException`, `InvalidRequestException`, `RateLimitException`, `ApiException`, `TimeoutException`, `TransactionFailedException`
- **Retry middleware** with exponential backoff, respects `Retry-After` header on 429 responses
- **Testing utilities** — `SpreedlyFake` and `MockTransporter` for zero-HTTP testing in both standalone PHP and Laravel applications
- **238 unit + feature tests**, PHPStan Level 8 clean, Laravel Pint formatted

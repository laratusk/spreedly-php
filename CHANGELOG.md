# Changelog

All notable changes to `laratusk/spreedly` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

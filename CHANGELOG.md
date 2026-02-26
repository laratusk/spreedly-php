# Changelog

All notable changes to `laratusk/spreedly` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

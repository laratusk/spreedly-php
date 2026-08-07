<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Tests\Integration;

use InvalidArgumentException;
use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\Exceptions\SpreedlyException;
use Laratusk\Spreedly\Http\Transporter;
use Laratusk\Spreedly\SpreedlyClient;
use Laratusk\Spreedly\Tests\Integration\Support\Sandbox;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Runs against the real Spreedly API.
 *
 * These tests exist to prove that every endpoint the SDK addresses is one the API
 * actually serves — a unit test mocks the transporter, so it can only ever confirm
 * that the SDK sends the path the test already expects.
 *
 * Skipped unless SPREEDLY_INTEGRATION=true, so CI stays green without credentials.
 */
abstract class IntegrationTestCase extends BaseTestCase
{
    protected SpreedlyClient $spreedly;

    protected TransporterInterface $transporter;

    /** Declared rather than assigned dynamically, which PHP 8.2 deprecates. */
    protected ?Sandbox $sandbox = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('SPREEDLY_INTEGRATION') !== 'true') {
            $this->markTestSkipped('Set SPREEDLY_INTEGRATION=true with credentials to run integration tests.');
        }

        $key = (string) getenv('SPREEDLY_ENVIRONMENT_KEY');
        $secret = (string) getenv('SPREEDLY_ACCESS_SECRET');

        if ($key === '' || $secret === '') {
            $this->markTestSkipped('SPREEDLY_ENVIRONMENT_KEY and SPREEDLY_ACCESS_SECRET must both be set.');
        }

        $this->transporter = new Transporter($key, $secret, [
            'base_url' => (string) (getenv('SPREEDLY_BASE_URL') ?: 'https://core.spreedly.com/v1/'),
        ]);

        $this->spreedly = new SpreedlyClient($key, $secret, $this->transporter);

        // Prove the credentials work before any per-endpoint result is interpreted, so a
        // 401 later can only mean that endpoint is out of scope for them.
        if ($this->httpStatus('GET', 'gateways.json') === 401) {
            $this->fail('Spreedly rejected the credentials; no route can be verified.');
        }
    }

    /**
     * A token from the shared sandbox, skipping the test when that resource could not
     * be created — a missing prerequisite is not evidence that a route is wrong.
     */
    protected function sandboxToken(string $name): string
    {
        $sandbox = $this->sandbox ??= Sandbox::for($this->spreedly);

        $token = match ($name) {
            'gateway' => $sandbox->gatewayToken,
            'payment method' => $sandbox->paymentMethodToken,
            'transaction' => $sandbox->transactionToken,
            'merchant profile' => $sandbox->merchantProfileToken,
            default => null,
        };

        if ($token === null) {
            $this->markTestSkipped(sprintf(
                'No %s available in this environment: %s',
                $name,
                $sandbox->failures[$name] ?? 'not created',
            ));
        }

        return $token;
    }

    /**
     * Send a request and report the HTTP status, whatever the outcome.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function httpStatus(string $method, string $endpoint, array $payload = []): int
    {
        try {
            match (strtoupper($method)) {
                'GET' => $this->transporter->get($endpoint, $payload),
                'POST' => $this->transporter->post($endpoint, $payload),
                'PUT' => $this->transporter->put($endpoint, $payload),
                'PATCH' => $this->transporter->patch($endpoint, $payload),
                'DELETE' => $this->transporter->delete($endpoint, [], $payload),
                default => throw new InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            return 200;
        } catch (SpreedlyException $e) {
            return $e->httpStatus ?? 0;
        }
    }

    /**
     * Assert that an endpoint is one the API routes.
     *
     * A 404 here is the signal that matters: every endpoint is exercised with tokens
     * that genuinely exist, so "not found" can only mean the path itself is wrong.
     * A 422 is a pass — the API understood the route and rejected the body, which is
     * all this assertion is about.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function assertRouteExists(string $method, string $endpoint, array $payload = []): void
    {
        $status = $this->httpStatus($method, $endpoint, $payload);

        // setUp already proved the credentials are good, so a 401 here means the endpoint
        // is organization-scoped and these environment credentials cannot reach it.
        if ($status === 401) {
            $this->markTestSkipped(sprintf(
                '%s %s needs organization-scoped credentials; route not verified.',
                strtoupper($method),
                $endpoint,
            ));
        }

        $this->assertNotSame(404, $status, sprintf(
            '%s %s is not served by Spreedly — the SDK is addressing the wrong path.',
            strtoupper($method),
            $endpoint,
        ));

        $this->assertNotSame(0, $status, sprintf(
            '%s %s produced no HTTP status; the request never reached Spreedly.',
            strtoupper($method),
            $endpoint,
        ));
    }
}

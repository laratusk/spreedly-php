<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Tests\Integration\Support;

use Laratusk\Spreedly\Exceptions\SpreedlyException;
use Laratusk\Spreedly\SpreedlyClient;
use RuntimeException;
use Throwable;

/**
 * Real resources on the test gateway, created once and shared by the integration
 * tests, so every endpoint can be exercised with tokens that genuinely exist.
 *
 * Each resource is built independently and a failure is recorded rather than thrown,
 * so one unavailable feature (3DS onboarding, say) cannot mask the routes that the
 * rest of the suite is there to verify — and so a failure cannot leave the singleton
 * unassigned, which would make every test build its own copy.
 *
 * Everything created is redacted when the process ends. Records Spreedly has no
 * delete endpoint for (merchant profiles, certificates, transactions) are never
 * created here: an existing one is reused, or the route is proven with a rejected
 * body instead.
 */
final class Sandbox
{
    private static ?self $instance = null;

    public ?string $gatewayToken;

    public ?string $paymentMethodToken;

    public ?string $transactionToken = null;

    public ?string $merchantProfileToken;

    /** @var array<string, string> */
    public array $failures = [];

    /** @var list<array{string, string}> */
    private array $created = [];

    private function __construct(private readonly SpreedlyClient $spreedly)
    {
        $this->gatewayToken = $this->attempt('gateway', function (): string {
            $token = $this->spreedly->gateways->create(['gateway_type' => 'test'])->token;
            $this->created[] = ['gateways', $token];

            return $token;
        });

        $this->paymentMethodToken = $this->attempt('payment method', fn (): string => $this->createCard());

        if ($this->gatewayToken !== null && $this->paymentMethodToken !== null) {
            $this->transactionToken = $this->attempt(
                'transaction',
                fn (): string => $this->spreedly->transactions->purchase($this->gatewayToken ?? '', [
                    'payment_method_token' => $this->paymentMethodToken,
                    'amount' => 1000,
                    'currency_code' => 'USD',
                    'retain_on_success' => true,
                ])->token,
            );
        }

        // Reused rather than created: Spreedly has no endpoint to delete a merchant
        // profile, so a fresh one per run would accumulate in the environment forever.
        $this->merchantProfileToken = $this->attempt('merchant profile', function (): string {
            $existing = $this->spreedly->merchantProfiles->list(count: 1);

            return $existing->items[0]->token
                ?? throw new RuntimeException('No merchant profile to reuse; create one in the Spreedly dashboard.');
        });

        register_shutdown_function($this->cleanUp(...));
    }

    public static function for(SpreedlyClient $spreedly): self
    {
        return self::$instance ??= new self($spreedly);
    }

    /**
     * Register a payment method the tests caused to exist, so it is redacted too —
     * storing at a gateway mints a third party token alongside the original card.
     */
    public function trackPaymentMethod(?string $token): void
    {
        if ($token !== null && $token !== '') {
            $this->created[] = ['payment_methods', $token];
        }
    }

    public function trackReceiver(?string $token): void
    {
        if ($token !== null && $token !== '') {
            $this->created[] = ['receivers', $token];
        }
    }

    public function createCard(): string
    {
        $token = $this->spreedly->paymentMethods->create([
            'credit_card' => [
                'first_name' => 'Integration',
                'last_name' => 'Test',
                'number' => '4111111111111111',
                'verification_value' => '123',
                'month' => '12',
                'year' => (string) ((int) date('Y') + 3),
            ],
            'retained' => true,
        ])->token;

        $this->created[] = ['payment_methods', $token];

        return $token;
    }

    /**
     * Redact everything this run created. Transactions are immutable at Spreedly and
     * are left as they are.
     */
    public function cleanUp(): void
    {
        foreach ($this->created as [$resource, $token]) {
            try {
                match ($resource) {
                    'gateways' => $this->spreedly->gateways->redact($token),
                    'payment_methods' => $this->spreedly->paymentMethods->redact($token),
                    'receivers' => $this->spreedly->receivers->redact($token),
                    default => null,
                };
            } catch (Throwable) {
                // Best effort: a failed redaction must not fail the suite.
            }
        }

        $this->created = [];
    }

    /**
     * @param  callable(): string  $build
     */
    private function attempt(string $label, callable $build): ?string
    {
        try {
            $token = $build();

            return $token === '' ? null : $token;
        } catch (SpreedlyException $e) {
            $this->failures[$label] = sprintf('%d %s', $e->httpStatus ?? 0, $e->getMessage());
        } catch (Throwable $e) {
            $this->failures[$label] = $e->getMessage();
        }

        return null;
    }
}

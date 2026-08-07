<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Tests\Integration\Support;

use Laratusk\Spreedly\Exceptions\SpreedlyException;
use Laratusk\Spreedly\SpreedlyClient;
use Throwable;

/**
 * Real resources on the test gateway, created once and shared by the integration
 * tests, so every endpoint can be exercised with tokens that genuinely exist.
 *
 * Each resource is built independently and a failure is recorded rather than thrown,
 * so one unavailable feature (3DS onboarding, say) cannot mask the routes that the
 * rest of the suite is there to verify.
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

    private function __construct(SpreedlyClient $spreedly)
    {
        $this->gatewayToken = $this->attempt(
            'gateway',
            fn (): string => $spreedly->gateways->create(['gateway_type' => 'test'])->token,
        );

        $this->paymentMethodToken = $this->attempt(
            'payment method',
            fn (): string => $spreedly->paymentMethods->create([
                'credit_card' => [
                    'first_name' => 'Integration',
                    'last_name' => 'Test',
                    'number' => '4111111111111111',
                    'verification_value' => '123',
                    'month' => '12',
                    'year' => (string) ((int) date('Y') + 3),
                ],
                'retained' => true,
            ])->token,
        );

        if ($this->gatewayToken !== null && $this->paymentMethodToken !== null) {
            $this->transactionToken = $this->attempt(
                'transaction',
                fn (): string => $spreedly->transactions->purchase($this->gatewayToken ?? '', [
                    'payment_method_token' => $this->paymentMethodToken,
                    'amount' => 1000,
                    'currency_code' => 'USD',
                    'retain_on_success' => true,
                ])->token,
            );
        }

        $this->merchantProfileToken = $this->attempt(
            'merchant profile',
            fn (): string => $spreedly->merchantProfiles->create([
                'description' => 'SDK integration test',
                'visa' => [
                    'acquirer_merchant_id' => '1234567890',
                    'mcc' => '5734',
                    'merchant_name' => 'SDK Integration Test',
                    'country_code' => '840',
                ],
            ])->token,
        );
    }

    public static function for(SpreedlyClient $spreedly): self
    {
        return self::$instance ??= new self($spreedly);
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

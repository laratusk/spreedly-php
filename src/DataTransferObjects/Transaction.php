<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;
use Laratusk\Spreedly\Enums\TransactionState;

/**
 * Represents a Spreedly transaction.
 * Note: amounts are always integers (cents).
 */
final readonly class Transaction
{
    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $gatewaySpecificFields
     * @param  array<string, mixed>  $gatewaySpecificResponseFields
     * @param  array<string, mixed>  $setupResponse
     * @param  array<string, mixed>  $redirectResponse
     * @param  array<string, mixed>  $callbackResponse
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $token,
        public string $transactionType,
        public bool $succeeded,
        public ?string $state,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public ?int $amount,
        public bool $onTestGateway,
        public ?string $gatewayLatencyMs,
        public ?string $currencyCode,
        public ?string $retain,
        public bool $retainOnSuccess,
        public ?string $paymentMethodToken,
        public ?PaymentMethod $paymentMethod,
        public ?string $gatewayToken,
        public ?string $gatewayType,
        public ?string $gatewayTransactionId,
        public ?string $gatewayResponseCode,
        public ?string $gatewayResponseMessage,
        public ?string $gatewayAvsResponseCode,
        public ?string $gatewayCvvResponseCode,
        public array $response,
        public ?string $message,
        public ?string $messageKey,
        public array $gatewaySpecificFields,
        public array $gatewaySpecificResponseFields,
        public ?string $ip,
        public ?string $description,
        public ?string $merchant,
        public bool $test,
        public ?string $referenceToken,
        public ?string $apiUrls,
        // Asynchronous flow (3DS2 and offsite payments). A transaction in the
        // `pending` state carries these so the caller can hand the cardholder
        // over to the issuer and be told where they land afterwards.
        public ?string $checkoutUrl = null,
        public ?string $checkoutForm = null,
        public ?string $redirectUrl = null,
        public ?string $callbackUrl = null,
        // Up to three sub-responses accompany an asynchronous transaction; they
        // hold error detail the top-level message does not.
        public array $setupResponse = [],
        public array $redirectResponse = [],
        public array $callbackResponse = [],
        // Payload as received, so a field this class does not model yet is
        // still reachable instead of being dropped on the floor.
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $tx = $data['transaction'] ?? $data;

        $paymentMethod = null;
        if (isset($tx['payment_method']) && is_array($tx['payment_method'])) {
            $paymentMethod = PaymentMethod::fromArray(['payment_method' => $tx['payment_method']]);
        }

        return new self(
            token: (string) ($tx['token'] ?? ''),
            transactionType: (string) ($tx['transaction_type'] ?? ''),
            succeeded: (bool) ($tx['succeeded'] ?? false),
            state: isset($tx['state']) ? (string) $tx['state'] : null,
            createdAt: CarbonImmutable::parse($tx['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($tx['updated_at'] ?? 'now'),
            amount: isset($tx['amount']) ? (int) $tx['amount'] : null,
            onTestGateway: (bool) ($tx['on_test_gateway'] ?? false),
            gatewayLatencyMs: isset($tx['gateway_latency_ms']) ? (string) $tx['gateway_latency_ms'] : null,
            currencyCode: isset($tx['currency_code']) ? (string) $tx['currency_code'] : null,
            retain: isset($tx['retain']) ? (string) $tx['retain'] : null,
            retainOnSuccess: (bool) ($tx['retain_on_success'] ?? false),
            paymentMethodToken: isset($tx['payment_method_token']) ? (string) $tx['payment_method_token'] : null,
            paymentMethod: $paymentMethod,
            gatewayToken: isset($tx['gateway_token']) ? (string) $tx['gateway_token'] : null,
            gatewayType: isset($tx['gateway_type']) ? (string) $tx['gateway_type'] : null,
            gatewayTransactionId: isset($tx['gateway_transaction_id']) ? (string) $tx['gateway_transaction_id'] : null,
            gatewayResponseCode: isset($tx['gateway_response_code']) ? (string) $tx['gateway_response_code'] : null,
            gatewayResponseMessage: isset($tx['gateway_response_message']) ? (string) $tx['gateway_response_message'] : null,
            gatewayAvsResponseCode: isset($tx['gateway_avs_response_code']) ? (string) $tx['gateway_avs_response_code'] : null,
            gatewayCvvResponseCode: isset($tx['gateway_cvv_response_code']) ? (string) $tx['gateway_cvv_response_code'] : null,
            response: (array) ($tx['response'] ?? []),
            message: isset($tx['message']) ? (string) $tx['message'] : null,
            messageKey: isset($tx['message_key']) ? (string) $tx['message_key'] : null,
            gatewaySpecificFields: (array) ($tx['gateway_specific_fields'] ?? []),
            gatewaySpecificResponseFields: (array) ($tx['gateway_specific_response_fields'] ?? []),
            ip: isset($tx['ip']) ? (string) $tx['ip'] : null,
            description: isset($tx['description']) ? (string) $tx['description'] : null,
            merchant: isset($tx['merchant']) ? (string) $tx['merchant'] : null,
            test: (bool) ($tx['test'] ?? false),
            referenceToken: isset($tx['reference_token']) ? (string) $tx['reference_token'] : null,
            apiUrls: isset($tx['api_urls']) ? (string) $tx['api_urls'] : null,
            checkoutUrl: isset($tx['checkout_url']) ? (string) $tx['checkout_url'] : null,
            checkoutForm: isset($tx['checkout_form']) ? (string) $tx['checkout_form'] : null,
            redirectUrl: isset($tx['redirect_url']) ? (string) $tx['redirect_url'] : null,
            callbackUrl: isset($tx['callback_url']) ? (string) $tx['callback_url'] : null,
            setupResponse: (array) ($tx['setup_response'] ?? []),
            redirectResponse: (array) ($tx['redirect_response'] ?? []),
            callbackResponse: (array) ($tx['callback_response'] ?? []),
            raw: (array) $tx,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'transaction_type' => $this->transactionType,
            'succeeded' => $this->succeeded,
            'state' => $this->state,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
            'amount' => $this->amount,
            'on_test_gateway' => $this->onTestGateway,
            'currency_code' => $this->currencyCode,
            'retain_on_success' => $this->retainOnSuccess,
            'payment_method_token' => $this->paymentMethodToken,
            'payment_method' => $this->paymentMethod?->toArray(),
            'gateway_token' => $this->gatewayToken,
            'gateway_type' => $this->gatewayType,
            'gateway_transaction_id' => $this->gatewayTransactionId,
            'gateway_response_code' => $this->gatewayResponseCode,
            'gateway_response_message' => $this->gatewayResponseMessage,
            'response' => $this->response,
            'message' => $this->message,
            'message_key' => $this->messageKey,
            'gateway_specific_fields' => $this->gatewaySpecificFields,
            'gateway_specific_response_fields' => $this->gatewaySpecificResponseFields,
            'test' => $this->test,
            'reference_token' => $this->referenceToken,
            'checkout_url' => $this->checkoutUrl,
            'checkout_form' => $this->checkoutForm,
            'redirect_url' => $this->redirectUrl,
            'callback_url' => $this->callbackUrl,
            'setup_response' => $this->setupResponse,
            'redirect_response' => $this->redirectResponse,
            'callback_response' => $this->callbackResponse,
        ];
    }

    /**
     * True when the cardholder still has to be sent somewhere — a 3DS2 challenge
     * or an offsite payment page — before this transaction can settle.
     */
    public function requiresCardholderAction(): bool
    {
        return $this->state === TransactionState::Pending->value
            && ($this->checkoutUrl !== null || $this->checkoutForm !== null);
    }
}

<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly payment.
 */
final readonly class Payment
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $token,
        public string $state,
        public ?int $amount,
        public ?string $currencyCode,
        public ?string $paymentMethodToken,
        public ?string $description,
        public array $data,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $payment = $data['payment'] ?? $data;

        return new self(
            token: (string) ($payment['token'] ?? ''),
            state: (string) ($payment['state'] ?? ''),
            amount: isset($payment['amount']) ? (int) $payment['amount'] : null,
            currencyCode: isset($payment['currency_code']) ? (string) $payment['currency_code'] : null,
            paymentMethodToken: isset($payment['payment_method_token']) ? (string) $payment['payment_method_token'] : null,
            description: isset($payment['description']) ? (string) $payment['description'] : null,
            data: (array) ($payment['data'] ?? []),
            createdAt: CarbonImmutable::parse($payment['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($payment['updated_at'] ?? 'now'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'state' => $this->state,
            'amount' => $this->amount,
            'currency_code' => $this->currencyCode,
            'payment_method_token' => $this->paymentMethodToken,
            'description' => $this->description,
            'data' => $this->data,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

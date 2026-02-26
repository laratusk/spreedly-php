<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly Card Refresher inquiry.
 */
final readonly class CardRefresherInquiry
{
    public function __construct(
        public string $token,
        public string $state,
        public bool $succeeded,
        public ?string $paymentMethodToken,
        public ?string $message,
        public ?string $messageKey,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $inquiry = $data['inquiry'] ?? $data;

        return new self(
            token: (string) ($inquiry['token'] ?? ''),
            state: (string) ($inquiry['state'] ?? ''),
            succeeded: (bool) ($inquiry['succeeded'] ?? false),
            paymentMethodToken: isset($inquiry['payment_method_token']) ? (string) $inquiry['payment_method_token'] : null,
            message: isset($inquiry['message']) ? (string) $inquiry['message'] : null,
            messageKey: isset($inquiry['message_key']) ? (string) $inquiry['message_key'] : null,
            createdAt: CarbonImmutable::parse($inquiry['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($inquiry['updated_at'] ?? 'now'),
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
            'succeeded' => $this->succeeded,
            'payment_method_token' => $this->paymentMethodToken,
            'message' => $this->message,
            'message_key' => $this->messageKey,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

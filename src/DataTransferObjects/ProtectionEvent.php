<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly protection event.
 */
final readonly class ProtectionEvent
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $token,
        public string $eventType,
        public string $state,
        public ?string $paymentMethodToken,
        public ?string $gatewayToken,
        public array $data,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $event = $data['protection_event'] ?? $data['event'] ?? $data;

        return new self(
            token: (string) ($event['token'] ?? ''),
            eventType: (string) ($event['event_type'] ?? ''),
            state: (string) ($event['state'] ?? ''),
            paymentMethodToken: isset($event['payment_method_token']) ? (string) $event['payment_method_token'] : null,
            gatewayToken: isset($event['gateway_token']) ? (string) $event['gateway_token'] : null,
            data: (array) ($event['data'] ?? []),
            createdAt: CarbonImmutable::parse($event['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($event['updated_at'] ?? 'now'),
            raw: (array) $event,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'event_type' => $this->eventType,
            'state' => $this->state,
            'payment_method_token' => $this->paymentMethodToken,
            'gateway_token' => $this->gatewayToken,
            'data' => $this->data,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

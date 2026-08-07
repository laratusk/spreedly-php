<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents something that happened to a stored payment method — a card updater
 * result, a network token lifecycle change, and so on.
 *
 * A different resource from {@see Event}, with a different shape and its own envelope.
 *
 * @see https://developer.spreedly.com/reference/list-all-payment-method-events
 */
final readonly class PaymentMethodEvent
{
    /**
     * @param  array<string, mixed>  $eventData
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $token,
        public ?string $requestId,
        public ?string $paymentMethodKey,
        public string $eventType,
        public array $eventData,
        public ?string $state,
        public ?string $message,
        public CarbonImmutable $createdAt,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $event = $data['payment_method_event'] ?? $data['event'] ?? $data;

        return new self(
            token: (string) ($event['token'] ?? ''),
            requestId: isset($event['request_id']) ? (string) $event['request_id'] : null,
            paymentMethodKey: isset($event['payment_method_key']) ? (string) $event['payment_method_key'] : null,
            eventType: (string) ($event['event_type'] ?? ''),
            eventData: (array) ($event['event_data'] ?? []),
            state: isset($event['state']) ? (string) $event['state'] : null,
            message: isset($event['message']) ? (string) $event['message'] : null,
            createdAt: CarbonImmutable::parse($event['created_at'] ?? 'now'),
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
            'request_id' => $this->requestId,
            'payment_method_key' => $this->paymentMethodKey,
            'event_type' => $this->eventType,
            'event_data' => $this->eventData,
            'state' => $this->state,
            'message' => $this->message,
            'created_at' => $this->createdAt->toIso8601String(),
        ];
    }
}

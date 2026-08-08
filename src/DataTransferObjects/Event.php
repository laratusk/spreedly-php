<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents an entry in a Spreedly environment's event log — what changed, and which
 * object it changed.
 *
 * Not to be confused with a {@see PaymentMethodEvent}, which is a different resource
 * with a different shape.
 *
 * @see https://developer.spreedly.com/reference/list-events
 */
final readonly class Event
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $id,
        public ?string $requestId,
        public string $eventType,
        public ?string $objectType,
        public ?string $objectKey,
        public CarbonImmutable $createdAt,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $event = $data['event'] ?? $data;

        return new self(
            id: (string) ($event['id'] ?? ''),
            requestId: isset($event['request_id']) ? (string) $event['request_id'] : null,
            eventType: (string) ($event['event_type'] ?? ''),
            objectType: isset($event['object_type']) ? (string) $event['object_type'] : null,
            objectKey: isset($event['object_key']) ? (string) $event['object_key'] : null,
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
            'id' => $this->id,
            'request_id' => $this->requestId,
            'event_type' => $this->eventType,
            'object_type' => $this->objectType,
            'object_key' => $this->objectKey,
            'created_at' => $this->createdAt->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly event (webhook event).
 */
final readonly class Event
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $token,
        public string $eventType,
        public string $state,
        public array $data,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $event = $data['event'] ?? $data;

        return new self(
            token: (string) ($event['token'] ?? ''),
            eventType: (string) ($event['event_type'] ?? ''),
            state: (string) ($event['state'] ?? ''),
            data: (array) ($event['data'] ?? []),
            createdAt: CarbonImmutable::parse($event['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($event['updated_at'] ?? 'now'),
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
            'data' => $this->data,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly receiver for delivering payment methods to third parties.
 */
final readonly class Receiver
{
    /**
     * @param  array<string, mixed>  $credentials
     * @param  array<string>  $hostnames
     */
    public function __construct(
        public string $token,
        public string $receiverType,
        public string $state,
        public ?string $description,
        public array $credentials,
        public array $hostnames,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public ?string $redactedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $receiver = $data['receiver'] ?? $data;

        return new self(
            token: (string) ($receiver['token'] ?? ''),
            receiverType: (string) ($receiver['receiver_type'] ?? ''),
            state: (string) ($receiver['state'] ?? ''),
            description: isset($receiver['description']) ? (string) $receiver['description'] : null,
            credentials: (array) ($receiver['credentials'] ?? []),
            hostnames: (array) ($receiver['hostnames'] ?? []),
            createdAt: CarbonImmutable::parse($receiver['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($receiver['updated_at'] ?? 'now'),
            redactedAt: isset($receiver['redacted_at']) ? (string) $receiver['redacted_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'receiver_type' => $this->receiverType,
            'state' => $this->state,
            'description' => $this->description,
            'credentials' => $this->credentials,
            'hostnames' => $this->hostnames,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
            'redacted_at' => $this->redactedAt,
        ];
    }
}

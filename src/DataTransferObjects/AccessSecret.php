<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly environment access secret.
 */
final readonly class AccessSecret
{
    public function __construct(
        public string $token,
        public string $name,
        public ?string $description,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $secret = $data['access_secret'] ?? $data;

        return new self(
            token: (string) ($secret['token'] ?? ''),
            name: (string) ($secret['name'] ?? ''),
            description: isset($secret['description']) ? (string) $secret['description'] : null,
            createdAt: CarbonImmutable::parse($secret['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($secret['updated_at'] ?? 'now'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

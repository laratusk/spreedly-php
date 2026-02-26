<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly environment.
 */
final readonly class Environment
{
    /**
     * @param  array<string, mixed>  $callbackUrls
     */
    public function __construct(
        public string $key,
        public string $name,
        public bool $test,
        public bool $hipaa,
        public ?string $callbackUrl,
        public array $callbackUrls,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $env = $data['environment'] ?? $data;

        return new self(
            key: (string) ($env['key'] ?? ''),
            name: (string) ($env['name'] ?? ''),
            test: (bool) ($env['test'] ?? false),
            hipaa: (bool) ($env['hipaa'] ?? false),
            callbackUrl: isset($env['callback_url']) ? (string) $env['callback_url'] : null,
            callbackUrls: (array) ($env['callback_urls'] ?? []),
            createdAt: CarbonImmutable::parse($env['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($env['updated_at'] ?? 'now'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'test' => $this->test,
            'hipaa' => $this->hipaa,
            'callback_url' => $this->callbackUrl,
            'callback_urls' => $this->callbackUrls,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

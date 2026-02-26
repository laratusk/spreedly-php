<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly certificate for Apple Pay or other network tokenization.
 */
final readonly class Certificate
{
    public function __construct(
        public string $token,
        public string $state,
        public ?string $commonName,
        public ?string $subject,
        public ?string $certBody,
        public ?string $privateKeyBody,
        public ?string $csr,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public ?string $expiresAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $cert = $data['certificate'] ?? $data;

        return new self(
            token: (string) ($cert['token'] ?? ''),
            state: (string) ($cert['state'] ?? ''),
            commonName: isset($cert['common_name']) ? (string) $cert['common_name'] : null,
            subject: isset($cert['subject']) ? (string) $cert['subject'] : null,
            certBody: isset($cert['cert_body']) ? (string) $cert['cert_body'] : null,
            privateKeyBody: isset($cert['private_key_body']) ? (string) $cert['private_key_body'] : null,
            csr: isset($cert['csr']) ? (string) $cert['csr'] : null,
            createdAt: CarbonImmutable::parse($cert['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($cert['updated_at'] ?? 'now'),
            expiresAt: isset($cert['expires_at']) ? (string) $cert['expires_at'] : null,
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
            'common_name' => $this->commonName,
            'subject' => $this->subject,
            'cert_body' => $this->certBody,
            'private_key_body' => $this->privateKeyBody,
            'csr' => $this->csr,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
            'expires_at' => $this->expiresAt,
        ];
    }
}

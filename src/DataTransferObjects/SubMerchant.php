<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly sub merchant.
 */
final readonly class SubMerchant
{
    /**
     * @param  array<string, mixed>  $fields
     */
    public function __construct(
        public string $token,
        public ?string $name,
        public ?string $email,
        public ?string $url,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public ?string $merchantCategoryCode,
        public array $fields,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $subMerchant = $data['sub_merchant'] ?? $data;

        return new self(
            token: (string) ($subMerchant['token'] ?? ''),
            name: isset($subMerchant['name']) ? (string) $subMerchant['name'] : null,
            email: isset($subMerchant['email']) ? (string) $subMerchant['email'] : null,
            url: isset($subMerchant['url']) ? (string) $subMerchant['url'] : null,
            city: isset($subMerchant['city']) ? (string) $subMerchant['city'] : null,
            state: isset($subMerchant['state']) ? (string) $subMerchant['state'] : null,
            country: isset($subMerchant['country']) ? (string) $subMerchant['country'] : null,
            merchantCategoryCode: isset($subMerchant['merchant_category_code']) ? (string) $subMerchant['merchant_category_code'] : null,
            fields: (array) ($subMerchant['fields'] ?? []),
            createdAt: CarbonImmutable::parse($subMerchant['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($subMerchant['updated_at'] ?? 'now'),
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
            'email' => $this->email,
            'url' => $this->url,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'merchant_category_code' => $this->merchantCategoryCode,
            'fields' => $this->fields,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

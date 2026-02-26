<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly merchant profile.
 */
final readonly class MerchantProfile
{
    /**
     * @param  array<string, mixed>  $profileFields
     */
    public function __construct(
        public string $token,
        public ?string $name,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public ?string $merchantCategoryCode,
        public ?string $merchantId,
        public array $profileFields,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $profile = $data['merchant_profile'] ?? $data;

        return new self(
            token: (string) ($profile['token'] ?? ''),
            name: isset($profile['name']) ? (string) $profile['name'] : null,
            city: isset($profile['city']) ? (string) $profile['city'] : null,
            state: isset($profile['state']) ? (string) $profile['state'] : null,
            country: isset($profile['country']) ? (string) $profile['country'] : null,
            merchantCategoryCode: isset($profile['merchant_category_code']) ? (string) $profile['merchant_category_code'] : null,
            merchantId: isset($profile['merchant_id']) ? (string) $profile['merchant_id'] : null,
            profileFields: (array) ($profile['profile_fields'] ?? []),
            createdAt: CarbonImmutable::parse($profile['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($profile['updated_at'] ?? 'now'),
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
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'merchant_category_code' => $this->merchantCategoryCode,
            'merchant_id' => $this->merchantId,
            'profile_fields' => $this->profileFields,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
        ];
    }
}

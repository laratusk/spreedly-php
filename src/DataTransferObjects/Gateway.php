<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\DataTransferObjects;

use Carbon\CarbonImmutable;

/**
 * Represents a Spreedly payment gateway.
 */
final readonly class Gateway
{
    /**
     * @param  array<string>  $paymentMethods
     * @param  array<string, mixed>  $characteristics
     * @param  array<string, mixed>  $credentials
     * @param  array<string, mixed>  $gatewaySettings
     * @param  array<string, mixed>  $gatewaySpecificFields
     */
    public function __construct(
        public string $token,
        public string $gatewayType,
        public ?string $description,
        public ?string $merchantProfileKey,
        public ?string $subMerchantKey,
        public array $paymentMethods,
        public string $state,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public string $name,
        public array $characteristics,
        public array $credentials,
        public array $gatewaySettings,
        public array $gatewaySpecificFields,
        public ?string $redactedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $gateway = $data['gateway'] ?? $data;

        return new self(
            token: (string) ($gateway['token'] ?? ''),
            gatewayType: (string) ($gateway['gateway_type'] ?? ''),
            description: isset($gateway['description']) ? (string) $gateway['description'] : null,
            merchantProfileKey: isset($gateway['merchant_profile_key']) ? (string) $gateway['merchant_profile_key'] : null,
            subMerchantKey: isset($gateway['sub_merchant_key']) ? (string) $gateway['sub_merchant_key'] : null,
            paymentMethods: (array) ($gateway['payment_methods'] ?? []),
            state: (string) ($gateway['state'] ?? ''),
            createdAt: CarbonImmutable::parse($gateway['created_at'] ?? 'now'),
            updatedAt: CarbonImmutable::parse($gateway['updated_at'] ?? 'now'),
            name: (string) ($gateway['name'] ?? ''),
            characteristics: (array) ($gateway['characteristics'] ?? []),
            credentials: (array) ($gateway['credentials'] ?? []),
            gatewaySettings: (array) ($gateway['gateway_settings'] ?? []),
            gatewaySpecificFields: (array) ($gateway['gateway_specific_fields'] ?? []),
            redactedAt: isset($gateway['redacted_at']) ? (string) $gateway['redacted_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'gateway_type' => $this->gatewayType,
            'description' => $this->description,
            'merchant_profile_key' => $this->merchantProfileKey,
            'sub_merchant_key' => $this->subMerchantKey,
            'payment_methods' => $this->paymentMethods,
            'state' => $this->state,
            'created_at' => $this->createdAt->toIso8601String(),
            'updated_at' => $this->updatedAt->toIso8601String(),
            'name' => $this->name,
            'characteristics' => $this->characteristics,
            'credentials' => $this->credentials,
            'gateway_settings' => $this->gatewaySettings,
            'gateway_specific_fields' => $this->gatewaySpecificFields,
            'redacted_at' => $this->redactedAt,
        ];
    }
}

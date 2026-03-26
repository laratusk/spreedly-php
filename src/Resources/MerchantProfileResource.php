<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\MerchantProfile;

/**
 * Manages Spreedly merchant profile resources.
 *
 * @see https://developer.spreedly.com/reference/merchant-profiles
 */
final readonly class MerchantProfileResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Create a new merchant profile.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): MerchantProfile
    {
        $response = $this->transporter->post('merchant_profiles.json', ['merchant_profile' => $params]);

        return MerchantProfile::fromArray($response);
    }

    /**
     * List all merchant profiles.
     *
     * @return PaginatedCollection<MerchantProfile>
     */
    public function list(?string $sinceToken = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('merchant_profiles.json', $query);
        $profiles = array_map(
            static fn (array $item): MerchantProfile => MerchantProfile::fromArray(['merchant_profile' => $item]),
            (array) ($response['merchant_profiles'] ?? []),
        );

        $lastToken = $profiles === [] ? null : end($profiles)->token;
        $hasMore = count($profiles) >= 20;

        return new PaginatedCollection(
            items: $profiles,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since),
        );
    }

    /**
     * Retrieve a merchant profile by token.
     */
    public function retrieve(string $token): MerchantProfile
    {
        $response = $this->transporter->get("merchant_profiles/{$token}.json");

        return MerchantProfile::fromArray($response);
    }

    /**
     * Update a merchant profile.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): MerchantProfile
    {
        $response = $this->transporter->put("merchant_profiles/{$token}.json", ['merchant_profile' => $params]);

        return MerchantProfile::fromArray($response);
    }

    /**
     * Create a protection provider for a merchant profile.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createProtectionProvider(string $token, array $params): array
    {
        return $this->transporter->post("merchant_profiles/{$token}/protection_provider.json", $params);
    }

    /**
     * Retrieve the protection provider for a merchant profile.
     *
     * @return array<string, mixed>
     */
    public function retrieveProtectionProvider(string $token): array
    {
        return $this->transporter->get("merchant_profiles/{$token}/protection_provider.json");
    }

    /**
     * Create an SCA provider for a merchant profile.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createScaProvider(string $token, array $params): array
    {
        return $this->transporter->post("merchant_profiles/{$token}/sca_provider.json", $params);
    }

    /**
     * Retrieve the SCA provider for a merchant profile.
     *
     * @return array<string, mixed>
     */
    public function retrieveScaProvider(string $token): array
    {
        return $this->transporter->get("merchant_profiles/{$token}/sca_provider.json");
    }
}

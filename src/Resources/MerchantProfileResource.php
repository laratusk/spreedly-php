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
     * @param  int|null  $count  Page size. Defaults to 20, maximum 100.
     * @return PaginatedCollection<MerchantProfile>
     */
    public function list(?string $sinceToken = null, ?string $order = null, ?int $count = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }
        if ($order !== null) {
            $query['order'] = $order;
        }
        if ($count !== null) {
            $query['count'] = $count;
        }

        $response = $this->transporter->get('merchant_profiles.json', $query);
        $profiles = array_map(
            static fn (array $item): MerchantProfile => MerchantProfile::fromArray(['merchant_profile' => $item]),
            (array) ($response['merchant_profiles'] ?? []),
        );

        $lastToken = $profiles === [] ? null : end($profiles)->token;
        $hasMore = count($profiles) >= ($count ?? 20);

        return new PaginatedCollection(
            items: $profiles,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since, $order, $count),
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
     * Create a protection provider on a merchant profile.
     * At least one card type object ('visa', 'mastercard', 'amex', ...) must be given.
     *
     * @param  array<string, mixed>  $params  Must include 'type' ('spreedly' or 'test')
     * @return array<string, mixed>
     */
    public function createProtectionProvider(string $merchantProfileToken, array $params): array
    {
        return $this->transporter->post('protection/providers.json', ['merchant_profile_key' => $merchantProfileToken] + $params);
    }

    /**
     * Retrieve a protection provider by its own token.
     *
     * @return array<string, mixed>
     */
    public function retrieveProtectionProvider(string $protectionProviderToken): array
    {
        return $this->transporter->get("protection/providers/{$protectionProviderToken}.json");
    }

    /**
     * Create an SCA provider on a merchant profile.
     * At least one card type object ('visa', 'mastercard', 'amex', 'discover') must be given.
     *
     * @param  array<string, mixed>  $params  Must include 'type' ('spreedly')
     * @return array<string, mixed>
     */
    public function createScaProvider(string $merchantProfileToken, array $params): array
    {
        return $this->transporter->post('sca/providers.json', ['merchant_profile_key' => $merchantProfileToken] + $params);
    }

    /**
     * Retrieve an SCA provider by its own token.
     *
     * @return array<string, mixed>
     */
    public function retrieveScaProvider(string $scaProviderToken): array
    {
        return $this->transporter->get("sca/providers/{$scaProviderToken}.json");
    }
}

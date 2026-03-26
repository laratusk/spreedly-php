<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\SubMerchant;

/**
 * Manages Spreedly sub merchant resources.
 *
 * @see https://developer.spreedly.com/reference/sub-merchants
 */
final readonly class SubMerchantResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Create a new sub merchant.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): SubMerchant
    {
        $response = $this->transporter->post('sub_merchants.json', ['sub_merchant' => $params]);

        return SubMerchant::fromArray($response);
    }

    /**
     * List all sub merchants.
     *
     * @return PaginatedCollection<SubMerchant>
     */
    public function list(?string $sinceToken = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('sub_merchants.json', $query);
        $subMerchants = array_map(
            static fn (array $item): SubMerchant => SubMerchant::fromArray(['sub_merchant' => $item]),
            (array) ($response['sub_merchants'] ?? []),
        );

        $lastToken = $subMerchants === [] ? null : end($subMerchants)->token;
        $hasMore = count($subMerchants) >= 20;

        return new PaginatedCollection(
            items: $subMerchants,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since),
        );
    }

    /**
     * Retrieve a sub merchant by token.
     */
    public function retrieve(string $token): SubMerchant
    {
        $response = $this->transporter->get("sub_merchants/{$token}.json");

        return SubMerchant::fromArray($response);
    }

    /**
     * Update a sub merchant.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): SubMerchant
    {
        $response = $this->transporter->put("sub_merchants/{$token}.json", ['sub_merchant' => $params]);

        return SubMerchant::fromArray($response);
    }
}

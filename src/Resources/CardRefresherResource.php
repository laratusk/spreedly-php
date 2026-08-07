<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\CardRefresherInquiry;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;

/**
 * Manages Spreedly Card Refresher resources.
 *
 * @see https://developer.spreedly.com/reference/card-refresher
 */
final readonly class CardRefresherResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Create a card refresher inquiry.
     *
     * @param  array<string, mixed>  $params  Must include 'payment_method_token' and 'region'
     *                                        (one of NA, EU, LATAM).
     */
    public function create(array $params): CardRefresherInquiry
    {
        $response = $this->transporter->post('card_refresher/inquiry.json', ['card_refresher_inquiry' => $params]);

        return CardRefresherInquiry::fromArray($response);
    }

    /**
     * Retrieve a card refresher inquiry by token.
     */
    public function retrieve(string $token): CardRefresherInquiry
    {
        $response = $this->transporter->get("card_refresher/inquiry/{$token}.json");

        return CardRefresherInquiry::fromArray($response);
    }

    /**
     * List all card refresher inquiries.
     *
     * @param  int|null  $count  Page size. Defaults to 20, maximum 100.
     * @return PaginatedCollection<CardRefresherInquiry>
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

        $response = $this->transporter->get('card_refresher/inquiries.json', $query);
        $inquiries = array_map(
            static fn (array $item): CardRefresherInquiry => CardRefresherInquiry::fromArray(['inquiry' => $item]),
            (array) ($response['inquiries'] ?? []),
        );

        $lastToken = $inquiries === [] ? null : end($inquiries)->token;
        $hasMore = count($inquiries) >= ($count ?? 20);

        return new PaginatedCollection(
            items: $inquiries,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since, $order, $count),
        );
    }
}

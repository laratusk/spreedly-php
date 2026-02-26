<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Event;

/**
 * Manages Spreedly event resources (webhook events).
 *
 * @see https://developer.spreedly.com/reference/events
 */
final readonly class EventResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * List events.
     *
     * @return PaginatedCollection<Event>
     */
    public function list(?string $sinceToken = null, string $order = 'desc'): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('events.json', $query);
        $events = array_map(
            static fn (array $item): \Laratusk\Spreedly\DataTransferObjects\Event => Event::fromArray(['event' => $item]),
            (array) ($response['events'] ?? []),
        );

        $lastToken = $events === [] ? null : end($events)->token;
        $hasMore = count($events) >= 20;

        return new PaginatedCollection(
            items: $events,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->list($since, $order),
        );
    }

    /**
     * Retrieve an event by token.
     */
    public function retrieve(string $token): Event
    {
        $response = $this->transporter->get("events/{$token}.json");

        return Event::fromArray($response);
    }
}

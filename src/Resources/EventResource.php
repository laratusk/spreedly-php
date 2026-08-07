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
    public function list(?string $sinceToken = null, string $order = 'desc', ?string $eventType = null, ?int $count = null): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }
        if ($eventType !== null) {
            $query['event_type'] = $eventType;
        }
        if ($count !== null) {
            $query['count'] = $count;
        }

        $response = $this->transporter->get('events.json', $query);
        $events = array_map(
            static fn (array $item): Event => Event::fromArray(['event' => $item]),
            (array) ($response['events'] ?? []),
        );

        $lastToken = $events === [] ? null : end($events)->id;
        $hasMore = count($events) >= ($count ?? 20);

        return new PaginatedCollection(
            items: $events,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since, $order, $eventType, $count),
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

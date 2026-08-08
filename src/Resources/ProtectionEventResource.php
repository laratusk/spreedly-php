<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\ProtectionEvent;

/**
 * Manages Spreedly protection event resources.
 *
 * @see https://developer.spreedly.com/reference/protection-events
 */
final readonly class ProtectionEventResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * List all protection events.
     *
     * @param  string|null  $state  One of succeeded, failed or pending
     * @param  int|null  $count  Page size. Defaults to 20, maximum 100.
     * @return PaginatedCollection<ProtectionEvent>
     */
    public function list(?string $sinceToken = null, ?string $order = null, ?string $state = null, ?int $count = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }
        if ($order !== null) {
            $query['order'] = $order;
        }
        if ($state !== null) {
            $query['state'] = $state;
        }
        if ($count !== null) {
            $query['count'] = $count;
        }

        $response = $this->transporter->get('protection/events.json', $query);
        $events = array_map(
            static fn (array $item): ProtectionEvent => ProtectionEvent::fromArray(['protection_event' => $item]),
            (array) ($response['events'] ?? $response['protection_events'] ?? []),
        );

        $lastToken = $events === [] ? null : end($events)->token;
        $hasMore = count($events) >= ($count ?? 20);

        return new PaginatedCollection(
            items: $events,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since, $order, $state, $count),
        );
    }

    /**
     * Retrieve a protection event by token.
     */
    public function retrieve(string $token): ProtectionEvent
    {
        $response = $this->transporter->get("protection/events/{$token}.json");

        return ProtectionEvent::fromArray($response);
    }
}

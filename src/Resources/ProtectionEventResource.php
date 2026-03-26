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
     * @return PaginatedCollection<ProtectionEvent>
     */
    public function list(?string $sinceToken = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('protection_events.json', $query);
        $events = array_map(
            static fn (array $item): ProtectionEvent => ProtectionEvent::fromArray(['protection_event' => $item]),
            (array) ($response['protection_events'] ?? []),
        );

        $lastToken = $events === [] ? null : end($events)->token;
        $hasMore = count($events) >= 20;

        return new PaginatedCollection(
            items: $events,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since),
        );
    }

    /**
     * Retrieve a protection event by token.
     */
    public function retrieve(string $token): ProtectionEvent
    {
        $response = $this->transporter->get("protection_events/{$token}.json");

        return ProtectionEvent::fromArray($response);
    }
}

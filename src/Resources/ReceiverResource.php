<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Receiver;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Manages Spreedly receiver resources.
 * Receivers allow you to deliver payment data to third-party endpoints.
 *
 * @see https://developer.spreedly.com/reference/receivers
 */
final readonly class ReceiverResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Create a new receiver.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): Receiver
    {
        $response = $this->transporter->post('receivers.json', ['receiver' => $params]);

        return Receiver::fromArray($response);
    }

    /**
     * Retrieve a receiver by token.
     */
    public function retrieve(string $token): Receiver
    {
        $response = $this->transporter->get("receivers/{$token}.json");

        return Receiver::fromArray($response);
    }

    /**
     * List all receivers.
     *
     * @return PaginatedCollection<Receiver>
     */
    public function list(?string $sinceToken = null, string $order = 'desc'): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('receivers.json', $query);
        $receivers = array_map(
            static fn (array $item): \Laratusk\Spreedly\DataTransferObjects\Receiver => Receiver::fromArray(['receiver' => $item]),
            (array) ($response['receivers'] ?? []),
        );

        $lastToken = $receivers === [] ? null : end($receivers)->token;
        $hasMore = count($receivers) >= 20;

        return new PaginatedCollection(
            items: $receivers,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->list($since, $order),
        );
    }

    /**
     * Update a receiver.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): Receiver
    {
        $response = $this->transporter->put("receivers/{$token}.json", ['receiver' => $params]);

        return Receiver::fromArray($response);
    }

    /**
     * Redact a receiver.
     */
    public function redact(string $token): Receiver
    {
        $response = $this->transporter->put("receivers/{$token}/redact.json");

        return Receiver::fromArray($response);
    }

    /**
     * Deliver a payment method to a receiver endpoint.
     *
     * @param  array<string, mixed>  $params
     */
    public function deliver(string $token, array $params): Transaction
    {
        $response = $this->transporter->post("receivers/{$token}/deliver.json", $params);

        return Transaction::fromArray($response);
    }

    /**
     * Export payment methods from a receiver.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function export(string $token, array $params): array
    {
        return $this->transporter->post("receivers/{$token}/export.json", $params);
    }

    /**
     * List all supported receiver types.
     *
     * @return array<string, mixed>
     */
    public function supportedReceivers(): array
    {
        return $this->transporter->get('receivers_options.json');
    }
}

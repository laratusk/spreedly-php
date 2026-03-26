<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Gateway;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Manages Spreedly gateway resources.
 *
 * @see https://developer.spreedly.com/reference/gateways
 */
final readonly class GatewayResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Create a new gateway.
     *
     * @param  array<string, mixed>  $params  Must include 'gateway_type'. Can include 'login', 'password', etc.
     */
    public function create(array $params): Gateway
    {
        $response = $this->transporter->post('gateways.json', ['gateway' => $params]);

        return Gateway::fromArray($response);
    }

    /**
     * Retrieve a gateway by token.
     */
    public function retrieve(string $token): Gateway
    {
        $response = $this->transporter->get("gateways/{$token}.json");

        return Gateway::fromArray($response);
    }

    /**
     * List all gateways.
     *
     * @return PaginatedCollection<Gateway>
     */
    public function list(?string $sinceToken = null, string $order = 'desc'): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('gateways.json', $query);
        $gateways = array_map(
            static fn (array $item): Gateway => Gateway::fromArray(['gateway' => $item]),
            (array) ($response['gateways'] ?? []),
        );

        $lastToken = $gateways === [] ? null : end($gateways)->token;
        $hasMore = count($gateways) >= 20;

        return new PaginatedCollection(
            items: $gateways,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since, $order),
        );
    }

    /**
     * Update a gateway.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): Gateway
    {
        $response = $this->transporter->put("gateways/{$token}.json", ['gateway' => $params]);

        return Gateway::fromArray($response);
    }

    /**
     * Redact a gateway (removes sensitive credentials).
     */
    public function redact(string $token): Gateway
    {
        $response = $this->transporter->put("gateways/{$token}/redact.json");

        return Gateway::fromArray($response);
    }

    /**
     * Retain a gateway (prevents it from being automatically removed).
     */
    public function retain(string $token): Gateway
    {
        $response = $this->transporter->put("gateways/{$token}/retain.json");

        return Gateway::fromArray($response);
    }

    /**
     * List transactions for a gateway.
     *
     * @return PaginatedCollection<Transaction>
     */
    public function transactions(string $token, ?string $sinceToken = null, string $order = 'desc'): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get("gateways/{$token}/transactions.json", $query);
        $transactions = array_map(
            static fn (array $item): Transaction => Transaction::fromArray(['transaction' => $item]),
            (array) ($response['transactions'] ?? []),
        );

        $lastToken = $transactions === [] ? null : end($transactions)->token;
        $hasMore = count($transactions) >= 20;

        return new PaginatedCollection(
            items: $transactions,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->transactions($token, $since, $order),
        );
    }

    /**
     * List all supported gateway types.
     *
     * @return array<string, mixed>
     */
    public function supportedGateways(): array
    {
        return $this->transporter->get('gateways_options.json');
    }
}

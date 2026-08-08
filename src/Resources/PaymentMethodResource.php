<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethodEvent;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Manages Spreedly payment method resources.
 *
 * @see https://developer.spreedly.com/reference/payment-methods
 */
final readonly class PaymentMethodResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Create/tokenize a payment method.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): PaymentMethod
    {
        $response = $this->transporter->post('payment_methods.json', ['payment_method' => $params]);

        return PaymentMethod::fromArray($response);
    }

    /**
     * Retrieve a payment method by token.
     */
    public function retrieve(string $token): PaymentMethod
    {
        $response = $this->transporter->get("payment_methods/{$token}.json");

        return PaymentMethod::fromArray($response);
    }

    /**
     * List all payment methods.
     *
     * @param  string|null  $state  One of retained, redacted, cached or used
     * @param  string|null  $metadata  A `key:value` pair to filter on
     * @param  int|null  $count  Page size. Defaults to 20, maximum 100.
     * @return PaginatedCollection<PaymentMethod>
     */
    public function list(?string $sinceToken = null, string $order = 'desc', ?string $state = null, ?string $metadata = null, ?int $count = null): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }
        if ($state !== null) {
            $query['state'] = $state;
        }
        if ($metadata !== null) {
            $query['metadata'] = $metadata;
        }
        if ($count !== null) {
            $query['count'] = $count;
        }

        $response = $this->transporter->get('payment_methods.json', $query);
        $paymentMethods = array_map(
            static fn (array $item): PaymentMethod => PaymentMethod::fromArray(['payment_method' => $item]),
            (array) ($response['payment_methods'] ?? []),
        );

        $lastToken = $paymentMethods === [] ? null : end($paymentMethods)->token;
        $hasMore = count($paymentMethods) >= ($count ?? 20);

        return new PaginatedCollection(
            items: $paymentMethods,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->list($since, $order, $state, $metadata, $count),
        );
    }

    /**
     * Update a payment method.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): PaymentMethod
    {
        $response = $this->transporter->put("payment_methods/{$token}.json", ['payment_method' => $params]);

        return PaymentMethod::fromArray($response);
    }

    /**
     * Retain a payment method (prevents automatic removal).
     *
     * @param  bool|null  $provisionNetworkToken  Also attempt to provision a network token
     */
    public function retain(string $token, ?bool $provisionNetworkToken = null): Transaction
    {
        $payload = $provisionNetworkToken === null ? [] : ['provision_network_token' => $provisionNetworkToken];

        $response = $this->transporter->put("payment_methods/{$token}/retain.json", $payload);

        return Transaction::fromArray($response);
    }

    /**
     * Redact a payment method (removes sensitive data).
     */
    public function redact(string $token): Transaction
    {
        $response = $this->transporter->put("payment_methods/{$token}/redact.json");

        return Transaction::fromArray($response);
    }

    /**
     * Recache the CVV for a payment method.
     *
     * @param  array<string, mixed>  $params  Must include 'payment_method.verification_value'
     */
    public function recache(string $token, array $params): Transaction
    {
        $response = $this->transporter->post("payment_methods/{$token}/recache.json", ['payment_method' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Copy a payment method into a gateway's own vault, producing a separate
     * third party token payment method locked to that gateway.
     *
     * @param  string  $gatewayToken  The gateway to store at, not a payment method token
     * @param  array<string, mixed>  $params  Must include 'payment_method_token'
     */
    public function store(string $gatewayToken, array $params): Transaction
    {
        $response = $this->transporter->post("gateways/{$gatewayToken}/store.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * List transactions for a payment method.
     *
     * @return PaginatedCollection<Transaction>
     */
    public function transactions(string $token, ?string $sinceToken = null, string $order = 'desc'): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get("payment_methods/{$token}/transactions.json", $query);
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
     * Remove key value pairs from a payment method's metadata.
     *
     * @param  list<string>  $keys  The metadata keys to remove
     * @return array<string, mixed>
     */
    public function deleteMetadata(string $token, array $keys = []): array
    {
        return $this->transporter->delete("payment_methods/{$token}/metadata.json", [], $keys === [] ? [] : ['keys' => $keys]);
    }

    /**
     * Get the network token card metadata (art, labels, issuer contact details) for a
     * payment method that was network tokenized.
     *
     * @param  string  $token  The token of the payment method that was originally tokenized
     * @return array<string, mixed>
     */
    public function networkTokenizationMetadata(string $token): array
    {
        return $this->transporter->get('network_tokenization/card_metadata.json', ['payment_method_token' => $token]);
    }

    /**
     * Get the lifecycle status of the network token provisioned for a payment method.
     *
     * @param  string  $token  The token of the payment method that was originally tokenized
     * @return array<string, mixed>
     */
    public function networkTokenizationStatus(string $token): array
    {
        return $this->transporter->get('network_tokenization/token_status.json', ['payment_method_token' => $token]);
    }

    /**
     * List all payment method events.
     *
     * @param  int|null  $count  Page size. Defaults to 20, maximum 100.
     * @return PaginatedCollection<PaymentMethodEvent>
     */
    public function listEvents(?string $sinceToken = null, ?string $order = null, ?string $eventType = null, ?int $count = null, ?bool $includeTransactions = null): PaginatedCollection
    {
        $query = $this->eventQuery($sinceToken, $order, $eventType, $count, $includeTransactions);

        $response = $this->transporter->get('payment_methods/events.json', $query);
        $events = array_map(
            static fn (array $item): PaymentMethodEvent => PaymentMethodEvent::fromArray(['payment_method_event' => $item]),
            (array) ($response['payment_method_events'] ?? []),
        );

        $lastToken = $events === [] ? null : end($events)->token;
        $hasMore = count($events) >= ($count ?? 20);

        return new PaginatedCollection(
            items: $events,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->listEvents($since, $order, $eventType, $count, $includeTransactions),
        );
    }

    /**
     * List all events for a specific payment method.
     *
     * @param  int|null  $count  Page size. Defaults to 20, maximum 100.
     * @return PaginatedCollection<PaymentMethodEvent>
     */
    public function listEventsForPaymentMethod(string $token, ?string $sinceToken = null, ?string $order = null, ?string $eventType = null, ?int $count = null, ?bool $includeTransactions = null): PaginatedCollection
    {
        $query = $this->eventQuery($sinceToken, $order, $eventType, $count, $includeTransactions);

        $response = $this->transporter->get("payment_methods/{$token}/events.json", $query);
        $events = array_map(
            static fn (array $item): PaymentMethodEvent => PaymentMethodEvent::fromArray(['payment_method_event' => $item]),
            (array) ($response['payment_method_events'] ?? []),
        );

        $lastToken = $events === [] ? null : end($events)->token;
        $hasMore = count($events) >= ($count ?? 20);

        return new PaginatedCollection(
            items: $events,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): PaginatedCollection => $this->listEventsForPaymentMethod($token, $since, $order, $eventType, $count, $includeTransactions),
        );
    }

    /**
     * Retrieve a specific payment method event by token.
     */
    public function retrieveEvent(string $eventToken): PaymentMethodEvent
    {
        $response = $this->transporter->get("payment_methods/events/{$eventToken}.json");

        return PaymentMethodEvent::fromArray($response);
    }

    /**
     * Update a payment method using gratis (no charge).
     *
     * @param  array<string, mixed>  $params
     */
    public function updateGratis(string $token, array $params): PaymentMethod
    {
        $response = $this->transporter->put("payment_methods/{$token}/update_gratis.json", ['payment_method' => $params]);

        return PaymentMethod::fromArray($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventQuery(?string $sinceToken, ?string $order, ?string $eventType, ?int $count, ?bool $includeTransactions): array
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }
        if ($order !== null) {
            $query['order'] = $order;
        }
        if ($eventType !== null) {
            $query['event_type'] = $eventType;
        }
        if ($count !== null) {
            $query['count'] = $count;
        }
        if ($includeTransactions !== null) {
            $query['include_transactions'] = $includeTransactions;
        }

        return $query;
    }
}

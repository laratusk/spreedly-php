<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Event;
use Laratusk\Spreedly\DataTransferObjects\PaymentMethod;
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
     * @return PaginatedCollection<PaymentMethod>
     */
    public function list(?string $sinceToken = null, string $order = 'desc'): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('payment_methods.json', $query);
        $paymentMethods = array_map(
            static fn (array $item): \Laratusk\Spreedly\DataTransferObjects\PaymentMethod => PaymentMethod::fromArray(['payment_method' => $item]),
            (array) ($response['payment_methods'] ?? []),
        );

        $lastToken = $paymentMethods === [] ? null : end($paymentMethods)->token;
        $hasMore = count($paymentMethods) >= 20;

        return new PaginatedCollection(
            items: $paymentMethods,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->list($since, $order),
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
     */
    public function retain(string $token): Transaction
    {
        $response = $this->transporter->put("payment_methods/{$token}/retain.json");

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
     * Store a payment method at a gateway.
     *
     * @param  array<string, mixed>  $params
     */
    public function store(string $token, array $params): Transaction
    {
        $response = $this->transporter->post("payment_methods/{$token}/store.json", $params);

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
            static fn (array $item): \Laratusk\Spreedly\DataTransferObjects\Transaction => Transaction::fromArray(['transaction' => $item]),
            (array) ($response['transactions'] ?? []),
        );

        $lastToken = $transactions === [] ? null : end($transactions)->token;
        $hasMore = count($transactions) >= 20;

        return new PaginatedCollection(
            items: $transactions,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->transactions($token, $since, $order),
        );
    }

    /**
     * Delete metadata for a payment method.
     *
     * @return array<string, mixed>
     */
    public function deleteMetadata(string $token): array
    {
        return $this->transporter->delete("payment_methods/{$token}/metadata.json");
    }

    /**
     * Get network tokenization metadata for a payment method.
     *
     * @return array<string, mixed>
     */
    public function networkTokenizationMetadata(string $token): array
    {
        return $this->transporter->get("payment_methods/{$token}/network_tokenization_metadata.json");
    }

    /**
     * Get network tokenization status for a payment method.
     *
     * @return array<string, mixed>
     */
    public function networkTokenizationStatus(string $token): array
    {
        return $this->transporter->get("payment_methods/{$token}/network_tokenization_status.json");
    }

    /**
     * List all payment method events.
     *
     * @return PaginatedCollection<Event>
     */
    public function listEvents(?string $sinceToken = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('payment_methods/events.json', $query);
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
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->listEvents($since),
        );
    }

    /**
     * List all events for a specific payment method.
     *
     * @return PaginatedCollection<Event>
     */
    public function listEventsForPaymentMethod(string $token, ?string $sinceToken = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get("payment_methods/{$token}/events.json", $query);
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
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->listEventsForPaymentMethod($token, $since),
        );
    }

    /**
     * Retrieve a specific payment method event by token.
     */
    public function retrieveEvent(string $eventToken): Event
    {
        $response = $this->transporter->get("payment_methods/events/{$eventToken}.json");

        return Event::fromArray($response);
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
}

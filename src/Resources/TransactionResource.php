<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Manages Spreedly transaction resources.
 *
 * Note: All monetary amounts are in the smallest currency unit (cents for USD).
 *
 * @see https://developer.spreedly.com/reference/transactions
 */
final readonly class TransactionResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Purchase against a gateway.
     * A purchase immediately charges the payment method.
     *
     * @param  array<string, mixed>  $params  Must include 'payment_method_token', 'amount' (cents), 'currency_code'
     */
    public function purchase(string $gatewayToken, array $params): Transaction
    {
        $response = $this->transporter->post("gateways/{$gatewayToken}/purchase.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Authorize against a gateway.
     * Reserves funds without charging. Must be captured later.
     *
     * @param  array<string, mixed>  $params  Must include 'payment_method_token', 'amount' (cents), 'currency_code'
     */
    public function authorize(string $gatewayToken, array $params): Transaction
    {
        $response = $this->transporter->post("gateways/{$gatewayToken}/authorize.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Capture a previously authorized transaction.
     *
     * @param  array<string, mixed>  $params  Optionally include 'amount' (cents) for partial capture
     */
    public function capture(string $transactionToken, array $params = []): Transaction
    {
        $response = $this->transporter->post("transactions/{$transactionToken}/capture.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Void a transaction (cancel before settlement).
     *
     * @param  array<string, mixed>  $params
     */
    public function void(string $transactionToken, array $params = []): Transaction
    {
        $response = $this->transporter->post("transactions/{$transactionToken}/void.json", $params !== [] ? ['transaction' => $params] : []);

        return Transaction::fromArray($response);
    }

    /**
     * Credit (refund) a transaction.
     *
     * @param  array<string, mixed>  $params  Optionally include 'amount' (cents) for partial refund
     */
    public function credit(string $transactionToken, array $params = []): Transaction
    {
        $response = $this->transporter->post("transactions/{$transactionToken}/credit.json", $params !== [] ? ['transaction' => $params] : []);

        return Transaction::fromArray($response);
    }

    /**
     * Issue a general credit (not tied to an existing transaction).
     *
     * @param  array<string, mixed>  $params  Must include 'payment_method_token', 'amount' (cents), 'currency_code'
     */
    public function generalCredit(string $gatewayToken, array $params): Transaction
    {
        $response = $this->transporter->post("gateways/{$gatewayToken}/general_credit.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Verify a payment method against a gateway (zero-dollar authorization).
     *
     * @param  array<string, mixed>  $params  Must include 'payment_method_token'
     */
    public function verify(string $gatewayToken, array $params): Transaction
    {
        $response = $this->transporter->post("gateways/{$gatewayToken}/verify.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Retrieve a transaction by token.
     */
    public function retrieve(string $token): Transaction
    {
        $response = $this->transporter->get("transactions/{$token}.json");

        return Transaction::fromArray($response);
    }

    /**
     * Update a transaction.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): Transaction
    {
        $response = $this->transporter->patch("transactions/{$token}.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Complete a 3DS transaction.
     *
     * @param  array<string, mixed>  $params
     */
    public function complete(string $transactionToken, array $params = []): Transaction
    {
        $response = $this->transporter->post("transactions/{$transactionToken}/complete.json", $params !== [] ? ['transaction' => $params] : []);

        return Transaction::fromArray($response);
    }

    /**
     * Confirm a transaction.
     *
     * @param  array<string, mixed>  $params
     */
    public function confirm(string $transactionToken, array $params = []): Transaction
    {
        $response = $this->transporter->post("transactions/{$transactionToken}/confirm.json", $params !== [] ? ['transaction' => $params] : []);

        return Transaction::fromArray($response);
    }

    /**
     * List transactions.
     *
     * @return PaginatedCollection<Transaction>
     */
    public function list(?string $sinceToken = null, string $order = 'desc'): PaginatedCollection
    {
        $query = ['order' => $order];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('transactions.json', $query);
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
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->list($since, $order),
        );
    }

    /**
     * Get the transcript (raw gateway communication) for a transaction.
     */
    public function transcript(string $token): string
    {
        return $this->transporter->getRaw("transactions/{$token}/transcript.json");
    }

    /**
     * Perform a reference purchase (using a reference_token from a previous transaction).
     *
     * @param  array<string, mixed>  $params  Must include 'reference_token', 'amount', 'currency_code'
     */
    public function referencePurchase(string $gatewayToken, array $params): Transaction
    {
        $response = $this->transporter->post("gateways/{$gatewayToken}/purchase.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }
}

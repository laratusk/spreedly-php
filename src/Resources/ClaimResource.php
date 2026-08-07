<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;

/**
 * Manages Spreedly claim resources.
 *
 * @see https://developer.spreedly.com/reference/claim
 */
final readonly class ClaimResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Forward a chargeback claim for a transaction to its protection provider.
     *
     * @param  array<string, mixed>  $params  Must include 'reason_type'; see the reference for the
     *                                        supported claim fields
     * @return array<string, mixed>
     */
    public function create(string $transactionToken, array $params): array
    {
        return $this->transporter->post("protection/{$transactionToken}/claims.json", ['claim' => $params]);
    }
}

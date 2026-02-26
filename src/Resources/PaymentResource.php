<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Payment;

/**
 * Manages Spreedly payment resources.
 *
 * @see https://developer.spreedly.com/reference/payments
 */
final readonly class PaymentResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Retrieve a payment by token.
     */
    public function retrieve(string $token): Payment
    {
        $response = $this->transporter->get("payments/{$token}.json");

        return Payment::fromArray($response);
    }
}

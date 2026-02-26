<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Manages Spreedly SCA (Strong Customer Authentication) resources.
 * Used for 3DS authentication.
 *
 * @see https://developer.spreedly.com/reference/sca-authentication
 */
final readonly class ScaAuthenticationResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Authenticate a payment method using SCA.
     *
     * @param  array<string, mixed>  $params
     */
    public function authenticate(array $params): Transaction
    {
        $response = $this->transporter->post('sca_authentication/authenticate.json', ['transaction' => $params]);

        return Transaction::fromArray($response);
    }
}

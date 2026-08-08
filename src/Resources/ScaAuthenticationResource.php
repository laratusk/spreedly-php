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
     * The 3DS specific fields of the response (`three_ds_version`, `ecommerce_indicator`,
     * `authentication_value` and friends) are reachable through the returned transaction's
     * `raw` payload.
     *
     * @param  string  $scaProviderKey  The token returned when the SCA provider was created
     * @param  array<string, mixed>  $params  Must include 'payment_method_token'
     */
    public function authenticate(string $scaProviderKey, array $params): Transaction
    {
        $response = $this->transporter->post("sca/providers/{$scaProviderKey}/authenticate.json", ['transaction' => $params]);

        return Transaction::fromArray($response);
    }
}

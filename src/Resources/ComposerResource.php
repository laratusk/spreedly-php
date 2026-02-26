<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Transaction;

/**
 * Manages Spreedly Composer (workflow) resources.
 * Composer allows running transactions through intelligent routing workflows.
 *
 * @see https://developer.spreedly.com/reference/composer
 */
final readonly class ComposerResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Authorize using Composer workflows.
     *
     * @param  array<string, mixed>  $params
     */
    public function authorize(array $params): Transaction
    {
        $response = $this->transporter->post('composer/authorize.json', ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Purchase using Composer workflows.
     *
     * @param  array<string, mixed>  $params
     */
    public function purchase(array $params): Transaction
    {
        $response = $this->transporter->post('composer/purchase.json', ['transaction' => $params]);

        return Transaction::fromArray($response);
    }

    /**
     * Verify using Composer workflows.
     *
     * @param  array<string, mixed>  $params
     */
    public function verify(array $params): Transaction
    {
        $response = $this->transporter->post('composer/verify.json', ['transaction' => $params]);

        return Transaction::fromArray($response);
    }
}

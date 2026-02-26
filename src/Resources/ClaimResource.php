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
     * Create a claim.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        return $this->transporter->post('claim.json', $params);
    }
}

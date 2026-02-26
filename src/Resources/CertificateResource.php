<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\Certificate;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;

/**
 * Manages Spreedly certificate resources.
 * Used for Apple Pay and network tokenization.
 *
 * @see https://developer.spreedly.com/reference/certificates
 */
final readonly class CertificateResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * Create a new certificate.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): Certificate
    {
        $response = $this->transporter->post('certificates.json', ['certificate' => $params]);

        return Certificate::fromArray($response);
    }

    /**
     * List all certificates.
     *
     * @return PaginatedCollection<Certificate>
     */
    public function list(?string $sinceToken = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('certificates.json', $query);
        $certificates = array_map(
            static fn (array $item): \Laratusk\Spreedly\DataTransferObjects\Certificate => Certificate::fromArray(['certificate' => $item]),
            (array) ($response['certificates'] ?? []),
        );

        $lastToken = $certificates === [] ? null : end($certificates)->token;
        $hasMore = count($certificates) >= 20;

        return new PaginatedCollection(
            items: $certificates,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->list($since),
        );
    }

    /**
     * Update a certificate.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): Certificate
    {
        $response = $this->transporter->put("certificates/{$token}.json", ['certificate' => $params]);

        return Certificate::fromArray($response);
    }

    /**
     * Generate a certificate (creates the actual certificate from a CSR).
     */
    public function generate(string $token): Certificate
    {
        $response = $this->transporter->post("certificates/{$token}/generate.json");

        return Certificate::fromArray($response);
    }
}

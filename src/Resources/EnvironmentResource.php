<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Resources;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\DataTransferObjects\AccessSecret;
use Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection;
use Laratusk\Spreedly\DataTransferObjects\Environment;

/**
 * Manages Spreedly environment resources.
 *
 * @see https://developer.spreedly.com/reference/environments
 */
final readonly class EnvironmentResource
{
    public function __construct(
        private TransporterInterface $transporter,
    ) {}

    /**
     * List all environments.
     *
     * @return PaginatedCollection<Environment>
     */
    public function list(?string $sinceToken = null): PaginatedCollection
    {
        $query = [];
        if ($sinceToken !== null) {
            $query['since_token'] = $sinceToken;
        }

        $response = $this->transporter->get('environments.json', $query);
        $environments = array_map(
            static fn (array $item): \Laratusk\Spreedly\DataTransferObjects\Environment => Environment::fromArray(['environment' => $item]),
            (array) ($response['environments'] ?? []),
        );

        $lastToken = $environments === [] ? null : end($environments)->key;
        $hasMore = count($environments) >= 20;

        return new PaginatedCollection(
            items: $environments,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->list($since),
        );
    }

    /**
     * Create a new environment.
     *
     * @param  array<string, mixed>  $params
     */
    public function create(array $params): Environment
    {
        $response = $this->transporter->post('environments.json', ['environment' => $params]);

        return Environment::fromArray($response);
    }

    /**
     * Retrieve an environment by token.
     */
    public function retrieve(string $token): Environment
    {
        $response = $this->transporter->get("environments/{$token}.json");

        return Environment::fromArray($response);
    }

    /**
     * Update an environment.
     *
     * @param  array<string, mixed>  $params
     */
    public function update(string $token, array $params): Environment
    {
        $response = $this->transporter->put("environments/{$token}.json", ['environment' => $params]);

        return Environment::fromArray($response);
    }

    /**
     * Regenerate the signing secret for the current environment.
     *
     * @return array<string, mixed>
     */
    public function regenerateSigningSecret(): array
    {
        return $this->transporter->post('environments/regenerate_signing_secret.json');
    }

    /**
     * Create an access secret for an environment.
     *
     * @param  array<string, mixed>  $params  Must include 'name'.
     */
    public function createAccessSecret(string $envToken, array $params): AccessSecret
    {
        $response = $this->transporter->post("environments/{$envToken}/access_secrets.json", ['access_secret' => $params]);

        return AccessSecret::fromArray($response);
    }

    /**
     * List all access secrets for an environment.
     *
     * @return PaginatedCollection<AccessSecret>
     */
    public function listAccessSecrets(string $envToken): PaginatedCollection
    {
        $response = $this->transporter->get("environments/{$envToken}/access_secrets.json");
        $secrets = array_map(
            static fn (array $item): \Laratusk\Spreedly\DataTransferObjects\AccessSecret => AccessSecret::fromArray(['access_secret' => $item]),
            (array) ($response['access_secrets'] ?? []),
        );

        $lastToken = $secrets === [] ? null : end($secrets)->token;
        $hasMore = count($secrets) >= 20;

        return new PaginatedCollection(
            items: $secrets,
            sinceToken: $lastToken,
            hasMore: $hasMore,
            fetcher: fn (string $since): \Laratusk\Spreedly\DataTransferObjects\Collections\PaginatedCollection => $this->listAccessSecrets($envToken),
        );
    }

    /**
     * Retrieve an access secret for an environment.
     */
    public function retrieveAccessSecret(string $envToken, string $secretToken): AccessSecret
    {
        $response = $this->transporter->get("environments/{$envToken}/access_secrets/{$secretToken}.json");

        return AccessSecret::fromArray($response);
    }

    /**
     * Delete an access secret for an environment.
     *
     * @return array<string, mixed>
     */
    public function deleteAccessSecret(string $envToken, string $secretToken): array
    {
        return $this->transporter->delete("environments/{$envToken}/access_secrets/{$secretToken}.json");
    }
}

<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Contracts;

interface TransporterInterface
{
    /**
     * Send a GET request to the given endpoint.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array;

    /**
     * Send a POST request to the given endpoint.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $payload = []): array;

    /**
     * Send a PUT request to the given endpoint.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function put(string $endpoint, array $payload = []): array;

    /**
     * Send a PATCH request to the given endpoint.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function patch(string $endpoint, array $payload = []): array;

    /**
     * Send a DELETE request to the given endpoint.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function delete(string $endpoint, array $query = []): array;

    /**
     * Send a GET request and return the raw response body as a string.
     */
    public function getRaw(string $endpoint): string;
}

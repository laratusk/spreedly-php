<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Testing;

use Laratusk\Spreedly\Contracts\TransporterInterface;
use RuntimeException;

final class MockTransporter implements TransporterInterface
{
    /** @var array<string, array<string, mixed>> */
    private array $responses = [];

    /** @var array<string> */
    private array $calls = [];

    /**
     * @param  array<string, mixed>  $response
     */
    public function addResponse(string $method, string $endpoint, array $response): self
    {
        $this->responses["{$method} {$endpoint}"] = $response;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->resolve('GET', $endpoint);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $payload = []): array
    {
        return $this->resolve('POST', $endpoint);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function put(string $endpoint, array $payload = []): array
    {
        return $this->resolve('PUT', $endpoint);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function patch(string $endpoint, array $payload = []): array
    {
        return $this->resolve('PATCH', $endpoint);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function delete(string $endpoint, array $query = [], array $payload = []): array
    {
        return $this->resolve('DELETE', $endpoint);
    }

    public function getRaw(string $endpoint): string
    {
        $this->calls[] = "GET_RAW {$endpoint}";

        return '';
    }

    public function assertCalled(string $method, string $endpoint): void
    {
        $key = "{$method} {$endpoint}";
        if (! in_array($key, $this->calls, true)) {
            throw new RuntimeException("Expected {$key} to have been called, but it was not.");
        }
    }

    public function getCallCount(): int
    {
        return count($this->calls);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolve(string $method, string $endpoint): array
    {
        $key = "{$method} {$endpoint}";
        $this->calls[] = $key;

        return $this->responses[$key] ?? [];
    }
}

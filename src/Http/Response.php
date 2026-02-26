<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Http;

use Psr\Http\Message\ResponseInterface;

final readonly class Response
{
    private int $statusCode;

    private string $body;

    public function __construct(ResponseInterface $response)
    {
        $this->statusCode = $response->getStatusCode();
        $this->body = (string) $response->getBody();
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if (in_array($this->body, ['', '{}', 'null'], true)) {
            return [];
        }

        $decoded = json_decode($this->body, true);

        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }
}

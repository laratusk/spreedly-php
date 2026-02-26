<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Http\Middleware;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class RetryMiddleware
{
    /**
     * Create a Guzzle retry middleware handler.
     */
    public static function create(int $maxRetries = 3): callable
    {
        return Middleware::retry(
            decider: self::decider($maxRetries),
            delay: self::delay(),
        );
    }

    /**
     * Decide whether to retry the request.
     */
    private static function decider(int $maxRetries): callable
    {
        return static function (
            int $retries,
            RequestInterface $request,
            ?ResponseInterface $response = null,
            ?Throwable $exception = null,
        ) use ($maxRetries): bool {
            if ($retries >= $maxRetries) {
                return false;
            }

            // Retry on connection errors
            if ($exception instanceof ConnectException) {
                return true;
            }

            if (! $response instanceof \Psr\Http\Message\ResponseInterface) {
                return false;
            }

            $statusCode = $response->getStatusCode();

            // Retry on 429 (rate limit) and 5xx (server errors)
            return $statusCode === 429 || $statusCode >= 500;
        };
    }

    /**
     * Calculate the delay before the next retry (exponential backoff).
     */
    private static function delay(): callable
    {
        return static function (int $retries, ResponseInterface $response): int {
            // Check for Retry-After header on 429
            if ($response->getStatusCode() === 429 && $response->hasHeader('Retry-After')) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                if ($retryAfter > 0) {
                    return $retryAfter * 1000; // Convert to milliseconds
                }
            }

            // Exponential backoff: 1s, 2s, 4s, ...
            return (int) (1000 * (2 ** ($retries - 1)));
        };
    }
}

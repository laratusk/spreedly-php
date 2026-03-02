<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Laratusk\Spreedly\Contracts\TransporterInterface;
use Laratusk\Spreedly\Exceptions\ApiException;
use Laratusk\Spreedly\Exceptions\AuthenticationException;
use Laratusk\Spreedly\Exceptions\InvalidRequestException;
use Laratusk\Spreedly\Exceptions\NotFoundException;
use Laratusk\Spreedly\Exceptions\RateLimitException;
use Laratusk\Spreedly\Exceptions\TimeoutException;
use Laratusk\Spreedly\Http\Middleware\RetryMiddleware;
use Psr\Http\Message\ResponseInterface;

final readonly class Transporter implements TransporterInterface
{
    private Client $client;

    /**
     * @param  array<string, mixed>  $options  Supported keys:
     *                                         - base_url: string
     *                                         - timeout: int|float
     *                                         - connect_timeout: int|float
     *                                         - retries: int
     *                                         - handler: HandlerStack (useful for testing with MockHandler)
     */
    public function __construct(
        private string $environmentKey,
        private string $accessSecret,
        array $options = [],
    ) {
        $baseUrl = $options['base_url'] ?? 'https://core.spreedly.com/v1/';
        $timeout = (float) ($options['timeout'] ?? 30);
        $connectTimeout = (float) ($options['connect_timeout'] ?? 10);
        $maxRetries = (int) ($options['retries'] ?? 3);

        /** @var HandlerStack|null $customStack */
        $customStack = $options['handler'] ?? null;

        if ($customStack instanceof HandlerStack) {
            $stack = $customStack;
        } else {
            $stack = HandlerStack::create();
            $stack->push(RetryMiddleware::create($maxRetries));
        }

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
            'auth' => [$this->environmentKey, $this->accessSecret],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'handler' => $stack,
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     * @throws ApiException
     * @throws TimeoutException
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws RateLimitException
     */
    public function get(string $endpoint, array $query = []): array
    {
        $options = [];
        if ($query !== []) {
            $options['query'] = $query;
        }

        return $this->request('GET', $endpoint, $options);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws RateLimitException
     * @throws TimeoutException
     */
    public function post(string $endpoint, array $payload = []): array
    {
        $options = [];
        if ($payload !== []) {
            $options['json'] = $payload;
        }

        return $this->request('POST', $endpoint, $options);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     * @throws ApiException
     * @throws TimeoutException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws RateLimitException
     */
    public function put(string $endpoint, array $payload = []): array
    {
        $options = [];
        if ($payload !== []) {
            $options['json'] = $payload;
        }

        return $this->request('PUT', $endpoint, $options);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     * @throws ApiException
     * @throws TimeoutException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws RateLimitException
     */
    public function patch(string $endpoint, array $payload = []): array
    {
        $options = [];
        if ($payload !== []) {
            $options['json'] = $payload;
        }

        return $this->request('PATCH', $endpoint, $options);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     * @throws ApiException
     * @throws TimeoutException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws RateLimitException
     */
    public function delete(string $endpoint, array $query = []): array
    {
        $options = [];
        if ($query !== []) {
            $options['query'] = $query;
        }

        return $this->request('DELETE', $endpoint, $options);
    }

    /**
     * Send a GET request and return the raw response body as a string.
     * @throws ApiException
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws RateLimitException
     * @throws TimeoutException
     * @throws GuzzleException
     */
    public function getRaw(string $endpoint): string
    {
        try {
            $psrResponse = $this->client->request('GET', $endpoint);

            return (string) $psrResponse->getBody();
        } catch (ConnectException $e) {
            throw new TimeoutException(message: 'Connection to Spreedly timed out: '.$e->getMessage(), code: $e->getCode(), previous: $e);
        } catch (RequestException $e) {
            if ($e->getResponse() instanceof ResponseInterface) {
                $this->handleErrorResponse(new Response($e->getResponse()));
            }
            throw new ApiException(message: 'Spreedly API request failed: '.$e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     * @throws ApiException
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws InvalidRequestException
     * @throws NotFoundException
     * @throws RateLimitException
     * @throws TimeoutException
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $psrResponse = $this->client->request($method, $endpoint, $options);
            $response = new Response($psrResponse);

            if (! $response->isSuccessful()) {
                $this->handleErrorResponse($response);
            }

            return $response->json();
        } catch (ConnectException $e) {
            throw new TimeoutException(message: 'Connection to Spreedly timed out: '.$e->getMessage(), code: $e->getCode(), previous: $e);
        } catch (BadResponseException $e) {
            $response = new Response($e->getResponse());
            $this->handleErrorResponse($response);
        } catch (RequestException $e) {
            if ($e->getResponse() instanceof ResponseInterface) {
                $response = new Response($e->getResponse());
                $this->handleErrorResponse($response);
            }
            throw new ApiException(message: 'Spreedly API request failed: '.$e->getMessage(), code: $e->getCode(), previous: $e);
        }
    }

    /**
     * Handle an error response from the Spreedly API.
     *
     * @throws AuthenticationException
     * @throws NotFoundException
     * @throws InvalidRequestException
     * @throws RateLimitException
     * @throws ApiException
     */
    private function handleErrorResponse(Response $response): never
    {
        $statusCode = $response->getStatusCode();
        $data = $response->json();

        $message = $data['errors'][0]['message']
            ?? $data['error']
            ?? $data['message']
            ?? 'An unknown error occurred';

        $errors = $data['errors'] ?? null;
        $errorKey = $data['errors'][0]['key'] ?? null;

        match (true) {
            $statusCode === 401 => throw new AuthenticationException(
                message: (string) $message,
                httpStatus: $statusCode,
                spreedlyErrorKey: $errorKey,
            ),
            $statusCode === 404 => throw new NotFoundException(
                message: (string) $message,
                httpStatus: $statusCode,
                spreedlyErrorKey: $errorKey,
            ),
            $statusCode === 422 => throw new InvalidRequestException(
                message: (string) $message,
                errors: $errors,
                httpStatus: $statusCode,
                spreedlyErrorKey: $errorKey,
            ),
            $statusCode === 429 => throw new RateLimitException(
                message: (string) $message,
                httpStatus: $statusCode,
                spreedlyErrorKey: $errorKey,
            ),
            default => throw new ApiException(
                message: (string) $message,
                httpStatus: $statusCode,
                spreedlyErrorKey: $errorKey,
            ),
        };
    }
}

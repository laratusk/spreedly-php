<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Laratusk\Spreedly\Http\Middleware\RetryMiddleware;

test('create returns callable', function (): void {
    $middleware = RetryMiddleware::create();

    expect($middleware)->toBeCallable();
});

test('retries on 429 response and eventually succeeds', function (): void {
    $mock = new MockHandler([
        new Response(429, ['Retry-After' => '0'], '{"errors":[{"message":"Rate limited"}]}'),
        new Response(200, [], '{"gateways":[]}'),
    ]);

    $stack = HandlerStack::create($mock);
    $stack->push(RetryMiddleware::create(1));

    $client = new Client(['handler' => $stack]);
    $response = $client->request('GET', 'https://example.com/test');

    expect($response->getStatusCode())->toBe(200);
});

test('retries on 500 response and eventually succeeds', function (): void {
    $mock = new MockHandler([
        new Response(500, [], '{"error":"Server Error"}'),
        new Response(200, [], '{"gateways":[]}'),
    ]);

    $stack = HandlerStack::create($mock);
    $stack->push(RetryMiddleware::create(1));

    $client = new Client(['handler' => $stack]);
    $response = $client->request('GET', 'https://example.com/test');

    expect($response->getStatusCode())->toBe(200);
});

test('does not retry on 404 response', function (): void {
    $mock = new MockHandler([
        new Response(404, [], '{"errors":[{"message":"Not found"}]}'),
    ]);

    $stack = HandlerStack::create($mock);
    $stack->push(RetryMiddleware::create(3));

    $client = new Client(['handler' => $stack]);

    try {
        $client->request('GET', 'https://example.com/test');
    } catch (Exception) {
        // Expected - 404 not retried
    }

    // Only 1 request should have been made (no retries)
    expect($mock->count())->toBe(0);
});

test('stops after max retries and throws final exception', function (): void {
    $mock = new MockHandler([
        new Response(500, [], '{"error":"Error"}'),
        new Response(500, [], '{"error":"Error"}'),
        new Response(500, [], '{"error":"Error"}'),
    ]);

    $stack = HandlerStack::create($mock);
    $stack->push(RetryMiddleware::create(2));

    $client = new Client(['handler' => $stack]);

    try {
        $client->request('GET', 'https://example.com/test');
        expect(true)->toBeFalse('Should have thrown exception');
    } catch (ServerException $e) {
        expect($e->getResponse()->getStatusCode())->toBe(500);
    }
});

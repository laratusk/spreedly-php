<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Laratusk\Spreedly\Http\Response;

test('returns status code', function (): void {
    $response = new Response(new GuzzleResponse(200, [], '{}'));

    expect($response->getStatusCode())->toBe(200);
});

test('returns body', function (): void {
    $response = new Response(new GuzzleResponse(200, [], '{"key":"value"}'));

    expect($response->getBody())->toBe('{"key":"value"}');
});

test('json returns parsed array', function (): void {
    $response = new Response(new GuzzleResponse(200, [], '{"gateway":{"token":"abc"}}'));
    $json = $response->json();

    expect($json)->toBeArray();
    expect($json['gateway']['token'])->toBe('abc');
});

test('json returns empty array for empty body', function (): void {
    $response = new Response(new GuzzleResponse(200, [], ''));

    expect($response->json())->toBe([]);
});

test('json returns empty array for null body', function (): void {
    $response = new Response(new GuzzleResponse(200, [], 'null'));

    expect($response->json())->toBe([]);
});

test('json returns empty array for empty object body', function (): void {
    $response = new Response(new GuzzleResponse(200, [], '{}'));

    expect($response->json())->toBe([]);
});

test('isSuccessful returns true for 2xx status codes', function (): void {
    expect((new Response(new GuzzleResponse(200)))->isSuccessful())->toBeTrue();
    expect((new Response(new GuzzleResponse(201)))->isSuccessful())->toBeTrue();
    expect((new Response(new GuzzleResponse(204)))->isSuccessful())->toBeTrue();
});

test('isSuccessful returns false for non-2xx status codes', function (): void {
    expect((new Response(new GuzzleResponse(400)))->isSuccessful())->toBeFalse();
    expect((new Response(new GuzzleResponse(404)))->isSuccessful())->toBeFalse();
    expect((new Response(new GuzzleResponse(500)))->isSuccessful())->toBeFalse();
});

test('isClientError returns true for 4xx status codes', function (): void {
    expect((new Response(new GuzzleResponse(400)))->isClientError())->toBeTrue();
    expect((new Response(new GuzzleResponse(404)))->isClientError())->toBeTrue();
    expect((new Response(new GuzzleResponse(422)))->isClientError())->toBeTrue();
    expect((new Response(new GuzzleResponse(429)))->isClientError())->toBeTrue();
});

test('isClientError returns false for non-4xx status codes', function (): void {
    expect((new Response(new GuzzleResponse(200)))->isClientError())->toBeFalse();
    expect((new Response(new GuzzleResponse(500)))->isClientError())->toBeFalse();
});

test('isServerError returns true for 5xx status codes', function (): void {
    expect((new Response(new GuzzleResponse(500)))->isServerError())->toBeTrue();
    expect((new Response(new GuzzleResponse(503)))->isServerError())->toBeTrue();
});

test('isServerError returns false for non-5xx status codes', function (): void {
    expect((new Response(new GuzzleResponse(200)))->isServerError())->toBeFalse();
    expect((new Response(new GuzzleResponse(404)))->isServerError())->toBeFalse();
});

<?php

declare(strict_types=1);

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Laratusk\Spreedly\Exceptions\ApiException;
use Laratusk\Spreedly\Exceptions\AuthenticationException;
use Laratusk\Spreedly\Exceptions\InvalidRequestException;
use Laratusk\Spreedly\Exceptions\NotFoundException;
use Laratusk\Spreedly\Exceptions\RateLimitException;
use Laratusk\Spreedly\Http\Transporter;

/**
 * Create a Transporter using Guzzle's MockHandler for HTTP mocking.
 */
function makeTransporterWithMock(MockHandler $mock): Transporter
{
    $stack = HandlerStack::create($mock);

    return new Transporter('env_key', 'secret', [
        'handler' => $stack,
        'retries' => 0,
    ]);
}

test('throws AuthenticationException on 401', function (): void {
    $mock = new MockHandler([
        new Response(401, [], (string) json_encode([
            'errors' => [['key' => 'errors.unauthorized', 'message' => 'Unauthorized']],
        ])),
    ]);

    $transporter = makeTransporterWithMock($mock);

    expect(fn (): array => $transporter->get('gateways.json'))
        ->toThrow(AuthenticationException::class, 'Unauthorized');
});

test('throws NotFoundException on 404', function (): void {
    $mock = new MockHandler([
        new Response(404, [], (string) json_encode([
            'errors' => [['key' => 'errors.not_found', 'message' => 'Not Found']],
        ])),
    ]);

    $transporter = makeTransporterWithMock($mock);

    expect(fn (): array => $transporter->get('gateways/bad_token.json'))
        ->toThrow(NotFoundException::class, 'Not Found');
});

test('throws InvalidRequestException on 422 with errors', function (): void {
    $mock = new MockHandler([
        new Response(422, [], (string) json_encode([
            'errors' => [
                ['key' => 'errors.blank', 'message' => "Gateway type can't be blank."],
            ],
        ])),
    ]);

    $transporter = makeTransporterWithMock($mock);

    try {
        $transporter->post('gateways.json', []);
        expect(true)->toBeFalse('Expected InvalidRequestException');
    } catch (InvalidRequestException $e) {
        expect($e->httpStatus)->toBe(422);
        expect($e->errors)->not->toBeNull();
        expect($e->errors)->toBeArray();
    }
});

test('throws RateLimitException on 429', function (): void {
    $mock = new MockHandler([
        new Response(429, [], (string) json_encode([
            'errors' => [['key' => 'errors.rate_limit', 'message' => 'Too many requests']],
        ])),
    ]);

    $transporter = makeTransporterWithMock($mock);

    expect(fn (): array => $transporter->get('gateways.json'))
        ->toThrow(RateLimitException::class);
});

test('throws ApiException on 500', function (): void {
    $mock = new MockHandler([
        new Response(500, [], (string) json_encode([
            'errors' => [['key' => 'errors.server', 'message' => 'Internal Server Error']],
        ])),
    ]);

    $transporter = makeTransporterWithMock($mock);

    expect(fn (): array => $transporter->get('gateways.json'))
        ->toThrow(ApiException::class);
});

test('authentication exception has http status and error key', function (): void {
    $mock = new MockHandler([
        new Response(401, [], (string) json_encode([
            'errors' => [['key' => 'errors.unauthorized', 'message' => 'Unauthorized']],
        ])),
    ]);

    $transporter = makeTransporterWithMock($mock);

    try {
        $transporter->get('gateways.json');
        expect(true)->toBeFalse('Expected AuthenticationException');
    } catch (AuthenticationException $e) {
        expect($e->httpStatus)->toBe(401);
        expect($e->spreedlyErrorKey)->toBe('errors.unauthorized');
    }
});

test('successful response returns parsed json array', function (): void {
    $payload = ['gateways' => [['token' => 'abc', 'gateway_type' => 'test']]];
    $mock = new MockHandler([
        new Response(200, [], (string) json_encode($payload)),
    ]);

    $transporter = makeTransporterWithMock($mock);
    $result = $transporter->get('gateways.json');

    expect($result)->toBe($payload);
});

test('post sends json payload', function (): void {
    $responsePayload = ['transaction' => ['token' => 'tx_123', 'succeeded' => true]];
    $mock = new MockHandler([
        new Response(201, [], (string) json_encode($responsePayload)),
    ]);

    $transporter = makeTransporterWithMock($mock);
    $result = $transporter->post('gateways/gw_token/purchase.json', ['transaction' => ['amount' => 1000]]);

    expect($result)->toBe($responsePayload);
});

<?php

declare(strict_types=1);

use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Laratusk\Spreedly\Exceptions\ApiException;
use Laratusk\Spreedly\Exceptions\AuthenticationException;
use Laratusk\Spreedly\Exceptions\InvalidRequestException;
use Laratusk\Spreedly\Exceptions\NotFoundException;
use Laratusk\Spreedly\Exceptions\RateLimitException;
use Laratusk\Spreedly\Exceptions\SpreedlyException;
use Laratusk\Spreedly\Exceptions\TimeoutException;
use Laratusk\Spreedly\Exceptions\TransactionFailedException;

test('SpreedlyException stores http status and error key', function (): void {
    $e = new SpreedlyException(
        message: 'Test error',
        httpStatus: 422,
        spreedlyErrorKey: 'errors.blank',
    );

    expect($e->getMessage())->toBe('Test error');
    expect($e->httpStatus)->toBe(422);
    expect($e->spreedlyErrorKey)->toBe('errors.blank');
});

test('SpreedlyException stores errors array', function (): void {
    $errors = [['key' => 'errors.blank', 'message' => 'Field is blank']];
    $e = new SpreedlyException(
        message: 'Validation failed',
        errors: $errors,
    );

    expect($e->errors)->toBe($errors);
});

test('SpreedlyException defaults are null', function (): void {
    $e = new SpreedlyException(message: 'Error');

    expect($e->httpStatus)->toBeNull();
    expect($e->spreedlyErrorKey)->toBeNull();
    expect($e->errors)->toBeNull();
});

test('AuthenticationException extends SpreedlyException', function (): void {
    $e = new AuthenticationException(
        message: 'Unauthorized',
        httpStatus: 401,
        spreedlyErrorKey: 'errors.unauthorized',
    );

    expect($e)->toBeInstanceOf(SpreedlyException::class);
    expect($e->httpStatus)->toBe(401);
    expect($e->spreedlyErrorKey)->toBe('errors.unauthorized');
});

test('NotFoundException extends SpreedlyException', function (): void {
    $e = new NotFoundException(
        message: 'Not Found',
        httpStatus: 404,
    );

    expect($e)->toBeInstanceOf(SpreedlyException::class);
    expect($e->httpStatus)->toBe(404);
});

test('InvalidRequestException stores errors array', function (): void {
    $errors = [['key' => 'errors.blank', 'message' => "Field can't be blank"]];
    $e = new InvalidRequestException(
        message: "Field can't be blank",
        errors: $errors,
        httpStatus: 422,
    );

    expect($e)->toBeInstanceOf(SpreedlyException::class);
    expect($e->httpStatus)->toBe(422);
    expect($e->errors)->toBe($errors);
});

test('RateLimitException extends SpreedlyException', function (): void {
    $e = new RateLimitException(
        message: 'Too many requests',
        httpStatus: 429,
    );

    expect($e)->toBeInstanceOf(SpreedlyException::class);
    expect($e->httpStatus)->toBe(429);
});

test('ApiException extends SpreedlyException', function (): void {
    $e = new ApiException(
        message: 'Internal Server Error',
        httpStatus: 500,
    );

    expect($e)->toBeInstanceOf(SpreedlyException::class);
    expect($e->httpStatus)->toBe(500);
});

test('TimeoutException extends SpreedlyException', function (): void {
    $e = new TimeoutException(message: 'Connection timed out');

    expect($e)->toBeInstanceOf(SpreedlyException::class);
    expect($e->getMessage())->toBe('Connection timed out');
});

test('TransactionFailedException stores transaction', function (): void {
    $txData = [
        'transaction' => [
            'token' => 'tx_failed_123',
            'transaction_type' => 'Purchase',
            'succeeded' => false,
            'state' => 'failed',
            'message' => 'Card declined',
            'on_test_gateway' => true,
            'retain_on_success' => false,
            'test' => true,
            'response' => [],
            'gateway_specific_fields' => [],
            'gateway_specific_response_fields' => [],
            'created_at' => '2024-01-15T10:00:00Z',
            'updated_at' => '2024-01-15T10:00:00Z',
        ],
    ];

    $transaction = Transaction::fromArray($txData);
    $e = new TransactionFailedException(transaction: $transaction);

    expect($e)->toBeInstanceOf(SpreedlyException::class);
    expect($e->transaction)->toBeInstanceOf(Transaction::class);
    expect($e->transaction->token)->toBe('tx_failed_123');
});

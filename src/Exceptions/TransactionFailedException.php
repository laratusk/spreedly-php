<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Exceptions;

use Laratusk\Spreedly\DataTransferObjects\Transaction;
use Throwable;

/**
 * Thrown when a transaction response has succeeded: false.
 * This is opt-in via config — not thrown by default.
 * By default, the SDK returns the Transaction DTO and users check $transaction->succeeded.
 */
final class TransactionFailedException extends SpreedlyException
{
    public function __construct(
        public readonly Transaction $transaction,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message ?: sprintf('Transaction %s failed: %s', $transaction->token, $transaction->message ?? 'Unknown error'),
            code: 0,
            errors: null,
            httpStatus: null,
            previous: $previous,
        );
    }
}

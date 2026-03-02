<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Exceptions;

use Exception;
use Throwable;

class SpreedlyException extends Exception
{
    /**
     * @param  list<array<string, mixed>>|null  $errors
     */
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?array $errors = null,
        public readonly ?int $httpStatus = null,
        public readonly ?string $spreedlyErrorKey = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}

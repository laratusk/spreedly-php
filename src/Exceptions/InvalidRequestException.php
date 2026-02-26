<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Exceptions;

/**
 * Thrown when Spreedly returns a 422 Unprocessable Entity response.
 * Contains validation errors in the $errors property.
 */
final class InvalidRequestException extends SpreedlyException {}

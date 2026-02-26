<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Exceptions;

/**
 * Thrown when Spreedly returns a 500+ server error response.
 */
final class ApiException extends SpreedlyException {}

<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Exceptions;

/**
 * Thrown when Spreedly returns a 429 Too Many Requests response.
 */
final class RateLimitException extends SpreedlyException {}

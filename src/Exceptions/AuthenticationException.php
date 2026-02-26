<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Exceptions;

/**
 * Thrown when Spreedly returns a 401 Unauthorized response.
 */
final class AuthenticationException extends SpreedlyException {}

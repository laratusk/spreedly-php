<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Enums;

enum GatewayState: string
{
    case Retained = 'retained';
    case Redacted = 'redacted';
    case Cached = 'cached';
}

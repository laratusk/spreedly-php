<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Enums;

use Laratusk\Spreedly\Laravel\Traits\Enums\EnumToArray;

enum StoredCredentialReasonType: string
{
    use EnumToArray;

    case RECURRING = 'recurring';
    case UNSCHEDULED = 'unscheduled';
    case INSTALLMENT = 'installment';
}

<?php

namespace Laratusk\Spreedly\Laravel\Enums;

use Laratusk\Spreedly\Laravel\Traits\Enums\EnumToArray;

enum StoredCredentialInitiator: string
{
    use EnumToArray;

    case CARDHOLDER = 'cardholder';
    case MERCHANT = 'merchant';
}

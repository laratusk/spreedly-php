<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Support;

use Illuminate\Support\Facades\Process;

final class MacAddress
{
    public static function get(): ?string
    {
        $address = config('spreedly.mac_address');

        if ($address) {
            return $address;
        }

        $command = config('spreedly.mac_address_command');

        if (empty($command)) {
            return null;
        }

        $result = Process::run(['sh', '-c', $command]);

        return $result->successful() ? trim($result->output()) : null;
    }
}

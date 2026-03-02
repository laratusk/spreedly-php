<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Support;

use Illuminate\Support\Facades\Process;
use RuntimeException;

final class MacAddress
{
    public static function get(): ?string
    {
        $address = config('spreedly.mac_address');

        if ($address) {
            return $address;
        }

        $command = config('spreedly.mac_address_command') ?? self::resolveCommandByOS();

        if (empty($command)) {
            return null;
        }

        $result = Process::run(['sh', '-c', $command]);

        return $result->successful() ? trim($result->output()) : null;
    }

    protected static function getOperatingSystem(): string
    {
        return PHP_OS_FAMILY;
    }

    /**
     * @return string
     */
    protected static function resolveCommandByOS(): string
    {
        return match (self::getOperatingSystem()) {
            'Windows' => 'getmac',
            'Linux' => "ip link | grep -oE '([0-9a-f]{2}:){5}[0-9a-f]{2}' | head -n 1",
            'Darwin' => "ifconfig | grep ether | awk '{print $2}' | head -n 1",
            default => throw new RuntimeException('Unsupported operating system'),
        };
    }
}

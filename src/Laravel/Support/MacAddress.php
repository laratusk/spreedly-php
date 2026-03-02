<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use RuntimeException;

final class MacAddress
{
    private const CACHE_KEY = 'spreedly:mac_address';

    private static ?string $cached = null;

    public static function get(): ?string
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $address = config('spreedly.mac_address');

        if ($address) {
            return self::$cached = (string) $address;
        }

        $fromCache = Cache::get(self::CACHE_KEY);

        if ($fromCache !== null) {
            return self::$cached = $fromCache;
        }

        $result = Process::run(['sh', '-c', self::resolveCommandByOS()]);
        $mac = $result->successful() ? trim($result->output()) : null;

        if ($mac !== null) {
            Cache::forever(self::CACHE_KEY, $mac);
        }

        return self::$cached = $mac;
    }

    public static function reset(): void
    {
        self::$cached = null;
        Cache::forget(self::CACHE_KEY);
    }

    protected static function getOperatingSystem(): string
    {
        return PHP_OS_FAMILY;
    }

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

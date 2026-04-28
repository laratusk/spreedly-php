<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Traits\Enums;

trait EnumToArray
{
    /** @return list<string> */
    public static function keys(): array
    {
        return array_column(self::cases(), 'name');
    }

    /** @return list<string|int> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string|int, string> */
    public static function array(): array
    {
        return array_combine(self::values(), self::keys());
    }

    /** @return array<string, string|int> */
    public static function flip(): array
    {
        return array_combine(self::keys(), self::values());
    }

    /**
     * @param  string|int  ...$keys
     * @return array<string|int, string>
     */
    public static function only(...$keys): array
    {
        $all = static::array();

        return array_intersect_key($all, array_flip($keys));
    }
}

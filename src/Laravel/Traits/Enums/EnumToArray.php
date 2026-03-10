<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Laravel\Traits\Enums;

trait EnumToArray
{
    public static function keys(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::values(), self::keys());
    }

    public static function flip(): array
    {
        return array_combine(self::keys(), self::values());
    }

    public static function only(...$keys): array
    {
        $all = static::array();

        return array_intersect_key($all, array_flip($keys));
    }
}

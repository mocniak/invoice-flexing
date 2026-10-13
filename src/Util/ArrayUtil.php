<?php
declare(strict_types=1);

namespace App\Util;

class ArrayUtil
{
    public static function clone(array $array): array
    {
        return array_map(function ($v) {
            return clone $v;
        }, $array);
    }
}

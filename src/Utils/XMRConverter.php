<?php

namespace App\Utils;

class XMRConverter
{
    public static function toXMR(int $atomicUnits): string
    {
        return number_format($atomicUnits / pow(10, 12), 8, '.', '');
    }
}

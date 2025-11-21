<?php

namespace App\Services;

use App\Enums\Prefix;

class Code {
    public static function short(string | null $code)
    {
        if (!$code) return null;
        return str($code)->explode('-')->get(1);
    }

    public static function full(string $code, Prefix $prefix)
    {
        $date = now()->format('Y-m-d');
        return "{$prefix->value}-{$code}-{$date}";
    }
}

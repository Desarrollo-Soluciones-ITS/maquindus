<?php

namespace App\Enums;

trait HasOptions {
    public static function options(): array
    {
        return collect(static::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->value
            ])->all();
    }
}

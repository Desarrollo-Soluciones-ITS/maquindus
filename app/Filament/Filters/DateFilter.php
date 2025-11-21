<?php

namespace App\Filament\Filters;

use Malzariey\FilamentDaterangepickerFilter\Enums\DropDirection;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class DateFilter {
    public static function make() {
        return DateRangeFilter::make('created_at')
            ->label('Fecha')
            ->placeholder('Seleccionar...')
            ->drops(DropDirection::UP)
            ->icon('heroicon-c-x-circle');
    }
}

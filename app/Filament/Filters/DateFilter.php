<?php

namespace App\Filament\Filters;

use Malzariey\FilamentDaterangepickerFilter\Enums\OpenDirection;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class DateFilter {
    public static function make($name = 'created_at') {
        return DateRangeFilter::make($name)
            ->label('Fecha')
            ->placeholder('Seleccionar...')
            ->opens(OpenDirection::CENTER)
            ->icon('heroicon-c-x-circle');
    }
}

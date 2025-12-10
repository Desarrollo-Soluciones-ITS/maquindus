<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\TrashedFilter;

class ArchivedFilter
{
    public static function make()
    {
        return TrashedFilter::make('trashed')
            ->label('Archivados')
            ->placeholder('Ocultar archivados')
            ->trueLabel('Incluir archivados')
            ->falseLabel('Solo archivados');
    }
}

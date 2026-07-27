<?php

namespace App\Filament\Resources\Parts\Schemas;

use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('part_number')
                    ->label('N° de parte')
                    ->placeholder('Ej. RF-100')
                    ->rule(PreventIllegalCharacters::apply())
                    ->maxLength(80)
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                TextInput::make('catalog_number')
                    ->label('N° de catálogo')
                    ->placeholder('Ej. CAT-001')
                    ->maxLength(80)
                    ->nullable(),
                TextInput::make('customer_part_number')
                    ->label('N° de parte del cliente')
                    ->placeholder('Ej. CL-001')
                    ->maxLength(80)
                    ->nullable(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Bomba de transferencia')
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }
}

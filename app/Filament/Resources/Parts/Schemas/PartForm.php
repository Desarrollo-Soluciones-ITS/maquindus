<?php

namespace App\Filament\Resources\Parts\Schemas;

use App\Enums\Prefix;
use App\Filament\Inputs\CodeInput;
use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Ej. Bomba hidráulica')
                    ->rule(PreventIllegalCharacters::apply())
                    ->maxLength(80)
                    ->unique()
                    ->required(),
                CodeInput::make(Prefix::Part),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Bomba de transferencia')
                    ->maxLength(255)
                    ->required(),
                KeyValue::make('details')
                    ->label('Características')
                    ->keyLabel('Nombre')
                    ->keyPlaceholder('Ej. Material')
                    ->valuePlaceholder('Ej. Acero')
                    ->columnSpanFull(),
            ]);
    }
}

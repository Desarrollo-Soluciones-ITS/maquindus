<?php

namespace App\Filament\Resources\Parts\Schemas;

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
                    ->label('Modelo')
                    ->placeholder('Bomba hidráulica')
                    ->required(),
                TextInput::make('code')
                    ->label('Código')
                    ->placeholder('PT-BMP-02')
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Bomba de transferencia')
                    ->required(),
                KeyValue::make('details')
                    ->label('Características')
                    ->keyPlaceholder('Nombre de la característica...')
                    ->valuePlaceholder('Valor de la característica...')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}

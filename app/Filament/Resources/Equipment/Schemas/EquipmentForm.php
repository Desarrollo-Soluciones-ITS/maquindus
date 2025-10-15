<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->label('Modelo')
                    ->placeholder('Compresor Atlas')
                    ->required(),
                TextInput::make('code')
                    ->label('Código')
                    ->placeholder('EQ-AT-001')
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Compresor centrífugo')
                    ->required(),
                KeyValue::make('details')
                    ->label('Características')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}

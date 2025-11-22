<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Enums\Prefix;
use App\Filament\Inputs\CodeInput;
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
                    ->label('Nombre')
                    ->placeholder('Ej. Compresor Atlas')
                    ->maxLength(80)
                    ->unique()
                    ->required(),
                CodeInput::make(Prefix::Equipment),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Compresor centrífugo')
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

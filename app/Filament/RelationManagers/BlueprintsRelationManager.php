<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class BlueprintsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'blueprints';

    protected static ?string $title = 'Especificación técnica · Planos';

    protected static ?string $modelLabel = 'plano';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('blueprint_number')->label('N de planos')->required()->maxLength(80),
            TextInput::make('name')->label('Nombre')->required()->maxLength(120),
            TextInput::make('revision')->label('Rev')->maxLength(40),
            DatePicker::make('document_date')->label('Fecha')->native(false),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('blueprint_number')->label('N de planos')->searchable(),
            TextColumn::make('name')->label('Nombre')->searchable(),
            TextColumn::make('revision')->label('Rev'),
            TextColumn::make('document_date')->label('Fecha')->date('d/m/Y'),
        ];
    }

}

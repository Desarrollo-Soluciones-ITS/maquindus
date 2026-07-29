<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class StandardsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'standards';

    protected static ?string $title = 'Especificación técnica · Normas';

    protected static ?string $modelLabel = 'norma';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('name')->label('Nombre')->required()->maxLength(120),
            TextInput::make('revision')->label('Rev')->maxLength(40),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Nombre')->searchable(),
            TextColumn::make('revision')->label('Rev'),
        ];
    }

    public static function getExportData(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Nombre' => $record->name,
            'Rev' => $record->revision,
        ];
    }

    public static function getExportHeadings(): array
    {
        return ['Nombre', 'Rev'];
    }
}
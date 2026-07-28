<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class ManualsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'manuals';

    protected static ?string $title = 'Especificación técnica · Manuales';

    protected static ?string $modelLabel = 'manual';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('name')->label('Nombre')->required()->maxLength(120),
            TextInput::make('revision')->label('Revisión')->maxLength(40),
            DatePicker::make('document_date')->label('Fecha')->native(false),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Nombre')->searchable(),
            TextColumn::make('revision')->label('Revisión'),
            TextColumn::make('document_date')->label('Fecha')->date('d/m/Y'),
        ];
    }

    protected static function getExportData(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Nombre' => $record->name,
            'Revisión' => $record->revision,
            'Fecha' => $record->document_date?->format('d/m/Y'),
        ];
    }

    protected static function getExportHeadings(): array
    {
        return ['Nombre', 'Revisión', 'Fecha'];
    }
}

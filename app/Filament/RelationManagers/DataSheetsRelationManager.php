<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class DataSheetsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'dataSheets';

    protected static ?string $title = 'Especificación técnica · Hojas de datos';

    protected static ?string $modelLabel = 'hoja de datos';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('sheet_number')->label('N hoja datos')->required()->maxLength(80),
            TextInput::make('revision')->label('Rev')->maxLength(40),
            DatePicker::make('document_date')->label('Fecha')->native(false),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('sheet_number')->label('N hoja datos')->searchable(),
            TextColumn::make('revision')->label('Rev'),
            TextColumn::make('document_date')->label('Fecha')->date('d/m/Y'),
        ];
    }

    public static function getExportData(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'N hoja datos' => $record->sheet_number,
            'Rev' => $record->revision,
            'Fecha' => $record->document_date?->format('d/m/Y'),
        ];
    }

    public static function getExportHeadings(): array
    {
        return ['N hoja datos', 'Rev', 'Fecha'];
    }
}

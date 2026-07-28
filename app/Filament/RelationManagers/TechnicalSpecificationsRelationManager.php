<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class TechnicalSpecificationsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'technicalSpecifications';

    protected static ?string $title = 'Especificación técnica · Revisiones';

    protected static ?string $modelLabel = 'especificación técnica';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('revision_name')->label('Nombre revisión')->required()->maxLength(120),
            TextInput::make('revision')->label('Rev')->maxLength(40),
            DatePicker::make('document_date')->label('Fecha')->native(false),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('revision_name')->label('Nombre rev')->searchable(),
            TextColumn::make('revision')->label('Rev'),
            TextColumn::make('document_date')->label('Fecha')->date('d/m/Y'),
        ];
    }

    protected static function getExportData(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Nombre rev' => $record->revision_name,
            'Rev' => $record->revision,
            'Fecha' => $record->document_date?->format('d/m/Y'),
        ];
    }

    protected static function getExportHeadings(): array
    {
        return ['Nombre rev', 'Rev', 'Fecha'];
    }
}

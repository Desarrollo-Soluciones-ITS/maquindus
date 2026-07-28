<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class FieldQueriesRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'fieldQueries';

    protected static ?string $title = 'Consultas de campo';

    protected static ?string $modelLabel = 'consulta de campo';

    protected static function getFormComponents(): array
    {
        return [
            DatePicker::make('document_date')->label('Fecha de doc')->native(false),
            TextInput::make('document_name')->label('Nombre de doc')->required()->maxLength(120),
            TextInput::make('document_type')->label('Tipo de documento')->required()->maxLength(80),
            TextInput::make('issuer')->label('Emisor')->maxLength(120),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('document_date')->label('Fecha de doc')->date('d/m/Y'),
            TextColumn::make('document_name')->label('Nombre de doc')->searchable(),
            TextColumn::make('document_type')->label('Tipo de documento')->searchable(),
            TextColumn::make('issuer')->label('Emisor')->searchable(),
        ];
    }

    protected static function getExportData(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Fecha de doc' => $record->document_date?->format('d/m/Y'),
            'Nombre de doc' => $record->document_name,
            'Tipo de documento' => $record->document_type,
            'Emisor' => $record->issuer,
        ];
    }

    protected static function getExportHeadings(): array
    {
        return ['Fecha de doc', 'Nombre de doc', 'Tipo de documento', 'Emisor'];
    }
}
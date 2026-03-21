<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class CatalogsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'catalogs';

    protected static ?string $title = 'Especificación técnica · Catálogos';

    protected static ?string $modelLabel = 'catálogo';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('document_type')->label('Tipo de doc')->required()->maxLength(80),
            TextInput::make('name')->label('Nombre')->required()->maxLength(120),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('document_type')->label('Tipo de doc')->searchable(),
            TextColumn::make('name')->label('Nombre')->searchable(),
        ];
    }
}
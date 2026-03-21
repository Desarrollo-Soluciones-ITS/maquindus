<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class EquipmentSparePartsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'equipmentSpareParts';

    protected static ?string $title = 'Repuestos';

    protected static ?string $modelLabel = 'repuesto';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('part_number')->label('N de parte')->required()->maxLength(80),
            TextInput::make('catalog_number')->label('N de catálogo')->maxLength(80),
            TextInput::make('client_part_number')->label('N parte cliente')->maxLength(80),
            TextInput::make('description')->label('Descripción')->maxLength(255),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('part_number')->label('N de parte')->searchable(),
            TextColumn::make('catalog_number')->label('N de catálogo')->searchable(),
            TextColumn::make('client_part_number')->label('N parte cliente')->searchable(),
            TextColumn::make('description')->label('Descripción')->limit(60),
        ];
    }
}
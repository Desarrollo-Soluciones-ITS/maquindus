<?php

namespace App\Filament\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class CatalogsRelationManager extends EquipmentMetadataRelationManager
{
    protected static string $relationship = 'catalogs';

    protected static ?string $title = 'Especificación técnica · Catálogos, fotos y videos';

    protected static ?string $modelLabel = 'catálogo';

    protected static function getFormComponents(): array
    {
        return [
            Select::make('document_type')
                ->label('Tipo de documento')
                ->options([
                    'fotos' => 'Fotos',
                    'videos' => 'Videos',
                    'pdf' => 'PDF',
                ])
                ->native(false)
                ->required(),
            TextInput::make('name')->label('Nombre')->required()->maxLength(120),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('document_type')->label('Tipo de documento')->badge()->searchable(),
            TextColumn::make('name')->label('Nombre')->searchable(),
        ];
    }
}
<?php

namespace App\Filament\RelationManagers;

use App\Filament\Traits\HasExportToExcel;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StandardsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

    protected static string $relationship = 'standards';

    protected static ?string $title = 'Especificación técnica · Normas';

    protected static ?string $modelLabel = 'norma';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('name')->label('Nombre')->maxLength(120),
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

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['name', 'revision'];
    }

    protected static function getExportLabels(): array
    {
        return ['Nombre', 'Rev'];
    }
}
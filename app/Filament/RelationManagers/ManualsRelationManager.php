<?php

namespace App\Filament\RelationManagers;

use App\Filament\Traits\HasExportToExcel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManualsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

    protected static string $relationship = 'manuals';

    protected static ?string $title = 'Especificación técnica · Manuales';

    protected static ?string $modelLabel = 'manual';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('name')->label('Nombre')->maxLength(120),
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

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['name', 'revision', 'document_date'];
    }

    protected static function getExportLabels(): array
    {
        return ['Nombre', 'Revisión', 'Fecha'];
    }
}
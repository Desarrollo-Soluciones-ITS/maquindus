<?php

namespace App\Filament\RelationManagers;

use App\Filament\Traits\HasExportToExcel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataSheetsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

    protected static string $relationship = 'dataSheets';

    protected static ?string $title = 'Especificación técnica · Hojas de datos';

    protected static ?string $modelLabel = 'hoja de datos';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('sheet_number')->label('N hoja datos')->maxLength(80),
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

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['sheet_number', 'revision', 'document_date'];
    }

    protected static function getExportLabels(): array
    {
        return ['N hoja datos', 'Rev', 'Fecha'];
    }
}

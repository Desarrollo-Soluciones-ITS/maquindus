<?php

namespace App\Filament\RelationManagers;

use App\Filament\Traits\HasExportToExcel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

    protected static string $relationship = 'reports';

    protected static ?string $title = 'Reportes';

    protected static ?string $modelLabel = 'reporte';

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

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['document_date', 'document_name', 'document_type', 'issuer'];
    }

    protected static function getExportLabels(): array
    {
        return ['Fecha de doc', 'Nombre de doc', 'Tipo de documento', 'Emisor'];
    }
}
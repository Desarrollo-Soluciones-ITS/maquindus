<?php

namespace App\Filament\RelationManagers;

use App\Filament\Traits\HasExportToExcel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CatalogsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

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
                ->native(false),
            TextInput::make('name')->label('Nombre')->maxLength(120),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('document_type')->label('Tipo de documento')->badge()->searchable(),
            TextColumn::make('name')->label('Nombre')->searchable(),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['document_type', 'name'];
    }

    protected static function getExportLabels(): array
    {
        return ['Tipo de documento', 'Nombre'];
    }
}
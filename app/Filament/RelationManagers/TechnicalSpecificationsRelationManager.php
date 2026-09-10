<?php

namespace App\Filament\RelationManagers;

use App\Filament\Filters\TextFilter;
use App\Filament\Traits\HasExportToExcel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TechnicalSpecificationsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

    protected static string $relationship = 'technicalSpecifications';

    protected static ?string $title = 'Especificación técnica · Revisiones';

    protected static ?string $modelLabel = 'especificación técnica';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('revision_name')->label('Nombre revisión')->maxLength(120),
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

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                ...TextFilter::forColumns([
                    'revision_name' => 'Nombre revisión',
                    'revision' => 'Rev',
                ], \App\Models\EquipmentTechnicalSpecification::class),
            ])
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['revision_name', 'revision', 'document_date'];
    }

    protected static function getExportLabels(): array
    {
        return ['Nombre rev', 'Rev', 'Fecha'];
    }
}
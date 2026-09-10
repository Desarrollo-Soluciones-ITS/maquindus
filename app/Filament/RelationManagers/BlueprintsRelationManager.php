<?php

namespace App\Filament\RelationManagers;

use App\Filament\Filters\TextFilter;
use App\Filament\Traits\HasExportToExcel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlueprintsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

    protected static string $relationship = 'blueprints';

    protected static ?string $title = 'Especificación técnica · Planos';

    protected static ?string $modelLabel = 'plano';

    protected static function getFormComponents(): array
    {
        return [
            TextInput::make('blueprint_number')->label('N de planos')->maxLength(80),
            TextInput::make('name')->label('Nombre')->maxLength(120),
            TextInput::make('revision')->label('Rev')->maxLength(40),
            DatePicker::make('document_date')->label('Fecha')->native(false),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('blueprint_number')->label('N de planos')->searchable(),
            TextColumn::make('name')->label('Nombre')->searchable(),
            TextColumn::make('revision')->label('Rev'),
            TextColumn::make('document_date')->label('Fecha')->date('d/m/Y'),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                ...TextFilter::forColumns([
                    'blueprint_number' => 'N de planos',
                    'name' => 'Nombre',
                    'revision' => 'Rev',
                ], \App\Models\EquipmentBlueprint::class),
            ])
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['blueprint_number', 'name', 'revision', 'document_date'];
    }

    protected static function getExportLabels(): array
    {
        return ['N de planos', 'Nombre', 'Rev', 'Fecha'];
    }
}
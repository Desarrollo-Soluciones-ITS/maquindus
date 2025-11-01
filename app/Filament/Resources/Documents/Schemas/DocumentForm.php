<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\Category;
use App\Enums\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Manual de Operación de la Bomba P-5')
                    ->required(),
                Select::make('type')
                    ->label('Tipo')
                    ->options(Type::options())
                    ->required(),
                Select::make('category')
                    ->label('Categoría')
                    ->placeholder('Ninguna')
                    ->options(Category::options())
                    ->default(null),
                Select::make('documentable_type')
                    ->label('Asociar a')
                    ->options([
                        \App\Models\Equipment::class => 'Equipo',
                        \App\Models\Part::class => 'Parte',
                        \App\Models\Project::class => 'Proyecto',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('documentable_id', null)),
                Select::make('documentable_id')
                    ->label(fn(Get $get): string => match ($get('documentable_type')) {
                        \App\Models\Equipment::class => 'Equipo',
                        \App\Models\Part::class => 'Parte',
                        \App\Models\Project::class => 'Proyecto',
                        default => 'Seleccionar Entidad',
                    })
                    ->required()
                    ->options(function (Get $get) {
                        $modelClass = $get('documentable_type');
                        if (!$modelClass) return [];

                        if (!in_array($modelClass, [
                            \App\Models\Equipment::class,
                            \App\Models\Part::class,
                            \App\Models\Project::class
                        ])) {
                            return [];
                        }

                        return $modelClass::all()
                            ->mapWithKeys(fn($model) => [$model->id => $model->name ?? $model->id])
                            ->toArray();
                    })
                    ->hidden(fn(Get $get) => is_null($get('documentable_type')))
                    ->searchable(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\Prefix;
use App\Enums\Status;
use App\Filament\Inputs\CodeInput;
use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Ej. Planta de ensayo')
                    ->rule(PreventIllegalCharacters::apply())
                    ->maxLength(80)
                    ->unique()
                    ->required(),
                CodeInput::make(Prefix::Project),
                DatePicker::make('start')
                    ->label('Fecha de inicio')
                    ->placeholder('Selecciona una fecha...')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->nullable()
                    ->before('end'),
                DatePicker::make('end')
                    ->label('Fecha de finalización')
                    ->placeholder('Selecciona una fecha...')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->nullable()
                    ->after('start'),
                Select::make('status')
                    ->label('Estado')
                    ->options(Status::options())
                    ->required(),
                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->hidden(fn($livewire) => $livewire instanceof RelationManager)
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Proyecto piloto para nueva línea')
                    ->columnSpanFull()
                    ->default(null),
            ]);
    }
}

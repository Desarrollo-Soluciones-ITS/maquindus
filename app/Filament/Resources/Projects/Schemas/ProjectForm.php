<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\Prefix;
use App\Enums\Status;
use App\Filament\Inputs\CodeInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Planta de ensayo')
                    ->maxLength(80)
                    ->unique()
                    ->required(),
                CodeInput::make(Prefix::Project),
                DatePicker::make('start')
                    ->label('Fecha de inicio'),
                DatePicker::make('end')
                    ->label('Fecha de finalización'),
                Select::make('status')
                    ->label('Estado')
                    ->options(Status::options())
                    ->required(),
                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Proyecto piloto para nueva línea')
                    ->columnSpanFull()
                    ->default(null),
            ]);
    }
}

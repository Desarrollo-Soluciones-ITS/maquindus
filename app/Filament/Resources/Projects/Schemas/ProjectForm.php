<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\Status;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

use function Illuminate\Log\log;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Planta de ensayo')
                    ->required(),
                TextInput::make('code')
                    ->label('Código')
                    ->placeholder('PRJ-001')
                    ->required(),
                DatePicker::make('start')
                    ->label('Fecha de inicio')
                    ->required(),
                DatePicker::make('end')
                    ->label('Fecha de finalización'),
                Select::make('status')
                    ->label('Estado')
                    ->options(
                        collect(Status::cases())
                            ->mapWithKeys(fn ($case) => [
                                $case->value => $case->value
                            ])
                    )
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

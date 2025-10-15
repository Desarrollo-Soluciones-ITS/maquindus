<?php

namespace App\Filament\Resources\People\Schemas;

use App\Models\Customer;
use App\Models\Supplier;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Mario')
                    ->required(),
                TextInput::make('surname')
                    ->label('Apellido')
                    ->placeholder('Gómez')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->placeholder('contacto@correo.com')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->placeholder('04128029102')
                    ->tel()
                    ->required(),
                Group::make()
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('state_id')
                            ->label('Estado')
                            ->relationship('state', 'name')
                            ->live()
                            ->required(),
                        Select::make('city_id')
                            ->label('Ciudad')
                            ->relationship(
                                name: 'city',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query, Get $get) =>
                                    $query->where('state_id', '=', $get('state_id'))
                            )
                            ->required(),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->placeholder('Calle 48, Avenida FG')
                            ->required(),
                    ]),
                    MorphToSelect::make('personable')
                        ->label('Empresa relacionada')
                        ->types([
                            MorphToSelect\Type::make(Supplier::class)
                                ->label('Proveedor')
                                ->titleAttribute('name'),
                            MorphToSelect\Type::make(Customer::class)
                                ->label('Cliente')
                                ->titleAttribute('name'),
                        ])
                        ->modifyTypeSelectUsing(
                            fn (Select $select) => $select->placeholder('Ninguna')
                        ),
                    TextInput::make('position')
                        ->label('Cargo')
                        ->placeholder('Responsable de ventas'),
            ]);
    }
}

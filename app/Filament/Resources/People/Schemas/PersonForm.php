<?php

namespace App\Filament\Resources\People\Schemas;

use App\Filament\Inputs\PhoneInput;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        $venId = Country::venezuela()->id;

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Ej. Mario')
                    ->maxLength(50)
                    ->required(),
                TextInput::make('surname')
                    ->label('Apellido')
                    ->placeholder('Ej. Gómez')
                    ->maxLength(50)
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->placeholder('Ej. contacto@correo.com')
                    ->email()
                    ->unique()
                    ->maxLength(255)
                    ->required(),
                PhoneInput::make(),
                Select::make('country_id')
                    ->label('País')
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->optionsLimit(15)
                    ->relationship(
                        name: 'country',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->oldest()
                    )
                    ->live()
                    ->required()
                    ->default($venId),
                Select::make('state_id')
                    ->label('Estado')
                    ->relationship('state', 'name')
                    ->live()
                    ->hidden(fn(Get $get) => $get('country_id') !== $venId)
                    ->required(),
                Select::make('city_id')
                    ->label('Ciudad')
                    ->relationship(
                        name: 'city',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query, Get $get) =>
                        $query->where('state_id', '=', $get('state_id'))
                    )
                    ->hidden(fn(Get $get) => $get('country_id') !== $venId)
                    ->required(),
                TextInput::make('address')
                    ->label('Dirección')
                    ->placeholder('Ej. Calle 15, Avenida FG')
                    ->maxLength(255)
                    ->required(),
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
                        fn(Select $select) => $select->placeholder('Ninguna')
                    ),
                TextInput::make('position')
                    ->label('Cargo')
                    ->placeholder('Ej. Responsable de ventas')
                    ->maxLength(255),
            ]);
    }
}

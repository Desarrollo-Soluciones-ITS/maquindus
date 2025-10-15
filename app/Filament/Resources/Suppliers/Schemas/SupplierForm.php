<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rif')
                    ->label('RIF')
                    ->placeholder('J-07621321-0')
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Suministros Industriales C.A.')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->placeholder('info@sumindus.com')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->placeholder('02121822940')
                    ->tel()
                    ->required(),
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
                    ->placeholder('Calle 10, Avenida MC')
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Empresa de suministro de equipamiento.')
                    ->default(null),
            ]);
    }
}

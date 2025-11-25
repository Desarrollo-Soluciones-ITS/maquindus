<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->placeholder('Ej. Miguel Pérez')
                            ->maxLength(80)
                            ->required(),
                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->placeholder('Ej. correo@ejemplo.com')
                            ->email()
                            ->unique()
                            ->maxLength(255)
                            ->required(),
                        Select::make('role_id')
                            ->label('Rol')
                            ->relationship('role', 'name')
                            ->searchable(),
                    ]),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->placeholder('Escribe la contraseña')
                    ->password()
                    ->rule('confirmed')
                    ->required()
                    ->revealable()
                    ->hiddenOn(Operation::Edit),
                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->placeholder('Confirma la contraseña')
                    ->password()
                    ->required()
                    ->revealable()
                    ->hiddenOn(Operation::Edit),
            ]);
    }
}

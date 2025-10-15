<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->rule('confirmed')
                    ->required()
                    ->revealable()
                    ->hiddenOn(Operation::Edit),
                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->password()
                    ->required()
                    ->revealable()
                    ->hiddenOn(Operation::Edit),
            ]);
    }
}

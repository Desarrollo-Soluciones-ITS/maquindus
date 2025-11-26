<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileSettings extends BaseEditProfile
{
    protected static ?string $title = 'Cambiar contraseña';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('password')
                    ->label('Contraseña')
                    ->placeholder('********')
                    ->password()
                    ->revealable()
                    ->maxLength(20)
                    ->required()
                    ->rule(Password::default())
                    ->dehydrateStateUsing(fn($state) => Hash::make($state)),

                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->placeholder('********')
                    ->password()
                    ->revealable()
                    ->maxLength(20)
                    ->required()
                    ->same('password')
                    ->validationMessages([
                        'same' => 'La confirmación debe ser igual a la contraseña'
                    ])
                    ->dehydrated(false),
            ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Contraseña actualizada';
    }

    protected function getCancelFormAction(): Action
    {
        return $this->backAction()->label('Cancelar');
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Actualizar')
            ->submit('save');
    }

    public function afterSave(): void
    {
        $this->data['password'] = '';
        $this->data['password_confirmation'] = '';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [
            'password' => $data['password'] ?? throw new \LogicException('La contraseña es necesaria'),
        ];
    }
}

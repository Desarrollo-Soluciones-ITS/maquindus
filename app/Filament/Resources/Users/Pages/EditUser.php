<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['password_confirmation']);
        
        // Only include password if it was provided
        if (empty($data['password'])) {
            unset($data['password']);
        }
        
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(!currentUserHasPermission('users.delete')),
        ];
    }
}

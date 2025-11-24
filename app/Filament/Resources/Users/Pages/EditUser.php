<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('users.show')),
            ArchiveAction::make()->hidden(!currentUserHasPermission('users.delete')),
        ];
    }
}

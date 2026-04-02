<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(currentUserHasPermission('roles'), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(!currentUserHasPermission('roles')),
        ];
    }
}

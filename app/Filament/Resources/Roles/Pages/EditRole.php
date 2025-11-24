<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ArchiveAction::make(),
        ];
    }
}

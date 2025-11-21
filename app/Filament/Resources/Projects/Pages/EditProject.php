<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Enums\Prefix;
use App\Filament\Resources\Projects\ProjectResource;
use App\Services\Code;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('projects.show')),
            DeleteAction::make()->hidden(!currentUserHasPermission('projects.delete')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Project);
        return $data;
    }
}

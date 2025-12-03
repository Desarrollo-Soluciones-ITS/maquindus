<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Enums\Prefix;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Projects\ProjectResource;
use App\Services\Code;
use App\Filament\Actions\RestoreAction;
use App\Traits\PreventsEditingTrashed;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProject extends EditRecord
{
    use PreventsEditingTrashed;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('projects.show')),
            ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('projects.delete')),
            RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('projects.restore')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Project);
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = collect($data);
        $oldName = $record->name;
        $newName = $data['name'];

        $isDocumentable = method_exists($record, 'documents');

        if ($isDocumentable && $oldName !== $newName) {
            handle_documentable_name_change($record, $oldName, $newName);
        }

        $record->update($data->all());

        return $record;
    }
}

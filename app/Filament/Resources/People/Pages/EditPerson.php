<?php

namespace App\Filament\Resources\People\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\People\PersonResource;
use App\Models\Country;
use App\Filament\Actions\RestoreAction;
use App\Models\Person;
use App\Traits\PreventsEditingTrashed;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPerson extends EditRecord
{
    use PreventsEditingTrashed;

    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('people.show')),
            ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('people.delete')),
            RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('people.restore')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['country_id'] !== Country::venezuela()->id) {
            $data['state_id'] = null;
            $data['city_id'] = null;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = collect($data);
        $oldName = $record->name;
        $newName = $data['name'];
        $oldEmail = $record->email ?? null;
        $newEmail = $data['email'] ?? null;

        $isDocumentable = method_exists($record, 'documents');

        if ($isDocumentable) {
            if ($record instanceof Person && ($oldName !== $newName || $oldEmail !== $newEmail)) {
                $oldFullName = $oldName . ' - ' . $oldEmail;
                $newFullName = $newName . ' - ' . $newEmail;
                handle_documentable_name_change($record, $oldFullName, $newFullName);
            } else if ($oldName !== $newName) {
                handle_documentable_name_change($record, $oldName, $newName);
            }
        }

        $record->update($data->all());

        return $record;
    }
}

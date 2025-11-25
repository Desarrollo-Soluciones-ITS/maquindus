<?php

namespace App\Filament\Resources\People\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\People\PersonResource;
use App\Models\Country;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('people.show')),
            ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('people.delete')),
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
}

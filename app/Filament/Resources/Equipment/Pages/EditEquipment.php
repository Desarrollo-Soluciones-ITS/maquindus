<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Enums\Prefix;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Services\Code;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('equipments.show')),
            ArchiveAction::make()->hidden(fn($record) => $record->trashed() || currentUserHasPermission('equipments.delete')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Equipment);
        return $data;
    }
}

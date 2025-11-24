<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Country;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('suppliers.show')),
            ArchiveAction::make()->hidden(!currentUserHasPermission('suppliers.delete')),
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

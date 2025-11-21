<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Country;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('customers.show')),
            DeleteAction::make()->hidden(!currentUserHasPermission('customers.delete')),
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

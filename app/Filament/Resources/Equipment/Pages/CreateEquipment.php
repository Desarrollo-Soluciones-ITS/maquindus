<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Enums\Prefix;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Services\Code;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Equipment);
        return $data;
    }
}

<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Enums\Prefix;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Services\Code;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Equipment);
        return $data;
    }
}

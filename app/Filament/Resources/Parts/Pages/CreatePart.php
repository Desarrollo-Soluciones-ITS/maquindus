<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Enums\Prefix;
use App\Filament\Resources\Parts\PartResource;
use App\Services\Code;
use Filament\Resources\Pages\CreateRecord;

class CreatePart extends CreateRecord
{
    protected static string $resource = PartResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Part);
        return $data;
    }
}

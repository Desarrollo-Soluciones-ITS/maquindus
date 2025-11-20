<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Enums\Prefix;
use App\Filament\Resources\Projects\ProjectResource;
use App\Services\Code;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Project);
        return $data;
    }
}

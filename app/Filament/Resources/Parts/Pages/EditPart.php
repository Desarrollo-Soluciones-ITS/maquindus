<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Enums\Prefix;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Parts\PartResource;
use App\Services\Code;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('parts.show')),
            ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('parts.delete')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Part);
        return $data;
    }
}

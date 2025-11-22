<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Enums\Prefix;
use App\Filament\Resources\Parts\PartResource;
use App\Services\Code;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('parts.show')),
            DeleteAction::make()->hidden(!currentUserHasPermission('parts.delete'))
                ->label('Archivar')
                ->icon(Heroicon::ArchiveBoxArrowDown),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = Code::full($data['code'], Prefix::Part);
        return $data;
    }
}

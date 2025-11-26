<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Actions\EditAction as FilamentEditAction;
use Illuminate\Database\Eloquent\Model;

class EditAction
{
    public static function make(): Action
    {
        return FilamentEditAction::make()
            ->using(function (Model $record, array $data): Model {
                $data = collect($data);
                $oldName = $record->name;
                $newName = $data['name'];

                $isDocumentable = method_exists($record, 'documents');

                if ($isDocumentable && $oldName !== $newName) {
                    handle_documentable_name_change($record, $oldName, $newName);
                }

                $record->update($data->all());

                return $record;
            });
    }
}

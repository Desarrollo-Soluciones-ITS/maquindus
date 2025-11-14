<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DeleteDocumentAction
{
    public static function make(): Action
    {
        return DeleteAction::make()
            ->using(function (Model $record) {
                $record->files()->each(function ($record) {
                    Storage::delete($record->path);
                    $record->delete();
                });

                $record->delete();
            });
    }
}

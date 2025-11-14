<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DeleteDocumentsBulkAction
{
    public static function make(): Action
    {
        return DeleteBulkAction::make()
            ->using(function (Collection $records) {
                $records->each(function ($record) {
                    $record->files()->each(function ($record) {
                        Storage::delete($record->path);
                        $record->delete();
                    });

                    $record->delete();
                });
            });
    }
}

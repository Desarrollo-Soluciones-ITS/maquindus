<?php

namespace App\Filament\Actions\Documents;

use App\Models\File;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction as FilamentDeleteBulkAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DeleteBulkAction
{
    public static function make(): Action
    {
        return FilamentDeleteBulkAction::make()
            ->using(function (Collection $records) {
                $records->each(function ($record) {
                    if ($record instanceof File) {
                        Storage::delete($record->path);
                        $record->delete();
                        return;
                    }

                    $record->files()->each(function ($file) {
                        Storage::delete($file->path);
                        $file->delete();
                    });

                    $record->delete();
                });
            });
    }
}

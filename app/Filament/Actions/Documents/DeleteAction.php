<?php

namespace App\Filament\Actions\Documents;

use App\Models\File;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction as FilamentDeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DeleteAction
{
    public static function make(): Action
    {
        return FilamentDeleteAction::make()
            ->using(function (Model $record) {
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
    }
}

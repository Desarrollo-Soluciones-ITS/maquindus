<?php

namespace App\Filament\Actions\Documents;

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
                $record->files()->each(function ($record) {
                    Storage::delete($record->path);
                    $record->delete();
                });

                $record->delete();
            });
    }
}

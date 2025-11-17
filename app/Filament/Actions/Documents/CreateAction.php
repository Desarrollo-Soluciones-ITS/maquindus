<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Actions\CreateAction as FilamentCreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateAction
{
    public static function make(): Action
    {
        return FilamentCreateAction::make()
            ->using(
                function (array $data, RelationManager $livewire): Model {
                    $data = collect($data);
                    $path = $data->get('path');
                    $mime = Storage::mimeType($path);

                    $document = $livewire->getOwnerRecord()
                        ->documents()
                        ->create($data->except('path')->all());

                    return $document->files()->create([
                        'path' => $path,
                        'mime' => $mime,
                        'version' => 1,
                    ]);
                }
            );
    }
}

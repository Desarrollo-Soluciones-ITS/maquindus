<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
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
                ),
        ];
    }
}

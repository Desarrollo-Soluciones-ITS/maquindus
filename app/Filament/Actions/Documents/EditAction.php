<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Actions\EditAction as FilamentEditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditAction
{
    public static function make(): Action
    {
        return FilamentEditAction::make()
            ->using(function (Model $record, array $data): Model {
                $data = collect($data);
                $oldName = $record->name;
                $newName = $data['name'];
                $oldCategory = $record->category;
                $newCategory = $data['category'];

                if ($oldName !== $newName || $oldCategory !== $newCategory) {
                    $documentable = $record->documentable;

                    $parent = model_to_spanish($documentable::class, plural: true);

                    $segments = collect([$parent, $documentable->name]);

                    if ($newCategory) {
                        $segments->push($newCategory);
                    }

                    $newFolder = $segments->join('/');

                    $record->files()->each(function ($file) use ($oldName, $newName, $newFolder) {
                        $oldPath = $file->path;
                        $oldFilename = basename($oldPath);

                        $newFilename = self::generateNewFilename($oldFilename, $oldName, $newName);
                        $newPath = $newFolder . '/' . $newFilename;

                        if (Storage::exists($oldPath)) {
                            Storage::move($oldPath, $newPath);
                            $file->update(['path' => $newPath]);
                        }
                    });
                }

                $record->update($data->all());

                return $record;
            });
    }

    private static function generateNewFilename(string $oldFilename, string $oldName, string $newName): string
    {
        $position = strpos($oldFilename, $oldName);

        if ($position !== false) {
            return substr_replace($oldFilename, $newName, $position, strlen($oldName));
        }

        return $oldFilename;
    }
}

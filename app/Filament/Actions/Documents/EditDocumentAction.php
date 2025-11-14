<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditDocumentAction
{
    public static function make(): Action
    {
        return EditAction::make()
            ->color(Color::Amber)
            ->using(function (Model $record, array $data): Model {
                $data = collect($data);
                $oldName = $record->name;
                $newName = $data['name'];
                $oldType = $record->type;
                $newType = $data['type'];

                if ($oldName !== $newName || $oldType !== $newType) {
                    $documentable = $record->documentable;
                    $parent = str($documentable::class)->explode('\\')->pop();
                    $newFolder = collect([$parent, $documentable->name, $newType])->join('/');

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

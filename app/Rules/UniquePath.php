<?php

namespace App\Rules;

use Closure;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UniquePath
{
    public static function apply()
    {
        return function (Get $get, ?Model $record, object $livewire) {
            return function (string $attribute, mixed $value, Closure $fail) use ($get, $record, $livewire) {
                $documentable = null;

                if ($livewire instanceof RelationManager) {
                    $documentable = $livewire->getOwnerRecord();
                } elseif ($record?->documentable) {
                    $documentable = $record->documentable;
                }

                if (!$documentable) {
                    return;
                }

                $category = $get('category');

                $folder = model_to_spanish(model: $documentable::class, plural: true);
                $segments = collect([$folder, $documentable->name]);

                if ($category) {
                    $segments->push($category);
                }

                $computedPath = $segments->join('/');

                if ($record !== null) {
                    $recordName = $record->name ?? null;
                    $recordCategory = $record->category?->value ?? null;

                    if (
                        (string) $value === (string) $recordName
                        && (string) $category === (string) $recordCategory
                    ) {
                        return;
                    }
                }

                $file = $get('path');

                if (!$file || !$file instanceof TemporaryUploadedFile) {
                    $path = "$computedPath/$value";


                    if ($category) {
                        $like = $path . '%';

                        $count = DB::table('files')
                            ->where('path', 'like', $like)
                            ->count();

                        if ($count > 0) {
                            $fail('Ya existe un archivo con ese nombre y carpeta.');
                            return;
                        }
                    }

                    $likeWithValue = $path . '%';
                    $expectedSlashCount = substr_count($computedPath, '/') + 1;

                    $count = DB::table('files')
                        ->where('path', 'like', $likeWithValue)
                        ->whereRaw("(LENGTH(path) - LENGTH(REPLACE(path, '/', ''))) = ?", [$expectedSlashCount])
                        ->count();

                    if ($count > 0) {
                        $fail('Ya existe un archivo con este nombre y carpeta.');
                        return;
                    }

                    $deletedFullPath = 'Superado/' . $computedPath . '/' . $value . '%';
                    $count = DB::table('files')
                        ->where('path', 'like', $deletedFullPath)
                        ->count();

                    if ($count > 0) {
                        $fail('Ya existe un archivo con este nombre en la carpeta "Superado".');
                        return;
                    }
                }

                $fullPath = $computedPath . '/' . $value . '%';

                $count = DB::table('files')
                    ->where('path', 'like', $fullPath)
                    ->count();

                if ($count > 0) {
                    $fail('Ya existe un archivo con este nombre y carpeta.');
                    return;
                }

                $deletedFullPath = 'Superado/' . $computedPath . '/' . $value . '%';
                $count = DB::table('files')
                    ->where('path', 'like', $deletedFullPath)
                    ->count();

                if ($count > 0) {
                    $fail('Ya existe un archivo con este nombre en la carpeta "Superado".');
                    return;
                }
            };
        };
    }
}

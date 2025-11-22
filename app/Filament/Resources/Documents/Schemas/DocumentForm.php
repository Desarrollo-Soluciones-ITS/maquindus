<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\Category;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Models\Equipment;
use App\Models\Part;
use Closure;
use DB;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(80)
                    ->placeholder('Manual de Operación de la Bomba P-5')
                    ->rule(
                        function (Get $get, ?Model $record, object $livewire) {
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
                                            $fail('Este archivo ya se encuentra registrado.');
                                        }

                                        return;
                                    }

                                    $likeWithValue = $path . '%';
                                    $expectedSlashCount = substr_count($computedPath, '/') + 1;

                                    $count = DB::table('files')
                                        ->where('path', 'like', $likeWithValue)
                                        ->whereRaw("(LENGTH(path) - LENGTH(REPLACE(path, '/', ''))) = ?", [$expectedSlashCount])
                                        ->count();

                                    if ($count > 0) {
                                        $fail('Este archivo ya se encuentra registrado.');
                                    }

                                    return;
                                }

                                $extension = $file->getClientOriginalExtension();

                                $initialVersion = 1;
                                $fileName = str($value)->append(" - V{$initialVersion}", '.', $extension)->toString();
                                $fullPath = $computedPath . '/' . $fileName;

                                if (Storage::disk('local')->exists($fullPath)) {
                                    $fail('Este archivo ya se encuentra registrado.');
                                }
                            };
                        }
                    )
                    ->required(),
                Select::make('category')
                    ->label('Categoría')
                    ->options(Category::options())
                    ->placeholder('Ninguna')
                    ->visible(function (RelationManager|ListDocuments $livewire, Model|null $record) {
                        $documentable = null;
                        if ($livewire instanceof RelationManager) {
                            $documentable = $livewire->getOwnerRecord();
                        } else if ($record !== null) {
                            $documentable = $record->documentable;
                        }

                        return $documentable instanceof Equipment
                            || $documentable instanceof Part;
                    }),
                FileUpload::make('path')
                    ->label('Archivo')
                    ->disk('local')
                    ->hiddenOn(Operation::Edit)
                    ->directory(
                        function (Get $get, RelationManager $livewire) {
                            $documentable = $livewire->getOwnerRecord();
                            $folder = model_to_spanish(
                                model: $documentable::class,
                                plural: true
                            );

                            $segments = collect([$folder, $documentable->name]);
                            $category = $get('category');

                            if ($category) {
                                $segments->push($category);
                            }

                            return $segments->join('/');
                        }
                    )
                    ->getUploadedFileNameForStorageUsing(
                        function (TemporaryUploadedFile $file, Get $get) {
                            $extension = $file->getClientOriginalExtension();
                            $initialVersion = 1;

                            return str($get('name'))
                                ->append(" - V{$initialVersion}", '.', $extension);
                        }
                    )
                    ->required(),
            ]);
    }
}

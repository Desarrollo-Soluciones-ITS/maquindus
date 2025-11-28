<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\Category;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Person;
use App\Rules\UniquePath;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Model;
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
                    ->placeholder('Ej. Manual de operación')
                    ->rule(UniquePath::apply())
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

                            if ($documentable instanceof Person) {
                                $name = trim($documentable->name);
                                $email = trim($documentable->email);
                                $segments = collect([$folder, "{$name} - {$email}"]);
                            } else {
                                $segments = collect([$folder, trim($documentable->name)]);
                            }

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

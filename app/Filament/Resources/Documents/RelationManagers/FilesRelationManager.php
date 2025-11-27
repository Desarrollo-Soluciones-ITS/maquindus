<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use App\Filament\Actions\Documents\DownloadAction;
use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Actions\Documents\PreviewAction;
use App\Models\Person;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $recordTitleAttribute = 'version';

    protected static ?string $title = 'Versiones';

    protected static ?string $modelLabel = 'versión';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Archivo de nueva versión')
                    ->disk('local')
                    ->directory(
                        function (RelationManager $livewire) {
                            $document = $livewire->getOwnerRecord();
                            $documentable = $document->documentable;
                            $folder = model_to_spanish(
                                model: $documentable::class,
                                plural: true
                            );

                            if ($documentable instanceof Person) {
                                $segments = collect([$folder, "{$documentable->name} - {$documentable->email}"]);
                            } else {
                                $segments = collect([$folder, $documentable->name]);
                            }

                            $category = $document->category;

                            if ($category) {
                                $segments->push($category->value);
                            }

                            return $segments->join('/');
                        }
                    )
                    ->getUploadedFileNameForStorageUsing(
                        function (TemporaryUploadedFile $file, RelationManager $livewire) {
                            $document = $livewire->getOwnerRecord();
                            $latestVersion = $document->files()->max('version') ?? 0;
                            $nextVersion = $latestVersion + 1;

                            $extension = $file->getClientOriginalExtension();
                            $baseName = str($document->name);

                            return str($baseName)
                                ->append(" - V{$nextVersion}", '.', $extension);
                        }
                    )
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('version')
                            ->label('Versión')
                            ->numeric(),
                        TextEntry::make('mime')
                            ->label('Tipo de archivo')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('Fecha de subida')
                            ->date('d/m/Y - g:i A')
                            ->timezone('America/Caracas'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(is_not_localhost() ? 'download' : 'preview')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('version')
                    ->label('Versión')
                    ->searchable()
                    ->sortable()
                    ->numeric(),
                TextColumn::make('mime')
                    ->label('Tipo de archivo')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Fecha de subida')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Añadir versión')
                    ->modalHeading('Añadir versión')
                    ->modalSubmitActionLabel('Añadir')
                    ->createAnother(false)
                    ->using(function (array $data, RelationManager $livewire): Model {
                        $document = $livewire->getOwnerRecord();
                        $latestVersion = $document->files()->max('version') ?? 0;
                        $path = $data['path'];

                        $mime = check_solidworks(
                            mime: Storage::mimeType($path),
                            path: $path
                        );

                        return $document->files()->create([
                            'path' => $path,
                            'mime' => mime_type($mime),
                            'version' => $latestVersion + 1,
                        ]);
                    })->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('files.create'))
            ])
            ->recordActions([
                ActionGroup::make([
                    ActionGroup::make([
                        PreviewAction::make()->hidden(!currentUserHasPermission('files.show_file')),
                        OpenFolderAction::make()->hidden(!currentUserHasPermission('files.open_in_folder')),
                        DownloadAction::make()->hidden(!currentUserHasPermission('files.download')),
                    ])->dropdown(false),
                    ViewAction::make()
                        ->color(Color::Blue)
                        ->modalHeading('Datos de la versión')
                        ->hidden(!currentUserHasPermission('files.show')),
                ]),
            ]);
    }
}

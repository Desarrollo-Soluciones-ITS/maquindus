<?php

namespace App\Filament\Resources\Documents\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $recordTitleAttribute = 'version';

    protected static ?string $title = 'Versiones';

    protected static ?string $modelLabel = 'archivo';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Nueva Versión')
                    ->disk('local')
                    ->directory(
                        function (RelationManager $livewire) {
                            $document = $livewire->getOwnerRecord();
                            $parent = str($document->documentable::class)->explode('\\')->pop();
                            return collect([$parent, $document->documentable->name, $document->type->value])
                                ->join('/');
                        }
                    )
                    ->getUploadedFileNameForStorageUsing(
                        function (TemporaryUploadedFile $file, RelationManager $livewire) {
                            $document = $livewire->getOwnerRecord();
                            $latestVersion = $document->files()->max('version') ?? 0;
                            $nextVersion = $latestVersion + 1;

                            $extension = $file->getClientOriginalExtension();
                            $baseName = str($document->name);
                            $timestamp = now()->format('Ymd_His');

                            return str("V{$nextVersion}_")
                                ->append($timestamp, '_', $baseName, '.', $extension);
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
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => mime_type($state)),
                        TextEntry::make('created_at')
                            ->label('Subido el')
                            ->date('d/m/Y - g:i A')
                            ->timezone('America/Caracas'),
                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->date('d/m/Y - g:i A')
                            ->timezone('America/Caracas')
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('Versión')
                    ->sortable()
                    ->searchable()
                    ->numeric(),
                TextColumn::make('mime')
                    ->label('Tipo de archivo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => mime_type($state)),
                TextColumn::make('created_at')
                    ->label('Subido el')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Subir Nueva Versión')
                    ->using(function (array $data, RelationManager $livewire): Model {
                        $document = $livewire->getOwnerRecord();
                        $latestVersion = $document->files()->max('version') ?? 0;
                        $path = $data['path'];
                        $mime = Storage::mimeType($path);

                        return $document->files()->create([
                            'path' => $path,
                            'mime' => $mime,
                            'version' => $latestVersion + 1,
                        ]);
                    })
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('download')
                        ->label('Descargar')
                        ->icon(Heroicon::ArrowDown)
                        ->action(function (Model $record) {
                            try {
                                return Storage::download($record->path);
                            } catch (\Throwable $th) {
                                Notification::make()
                                    ->title('No se encontró la versión.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    ViewAction::make(),
                    // EditAction::make()
                    //     ->using(function (Model $record, array $data): Model {
                    //         $oldPath = $record->path;
                    //         $newPath = $data['path'];
                    //         $newMime = $record->mime;

                    //         if ($oldPath !== $newPath) {
                    //             Storage::delete($oldPath);
                    //             $newMime = Storage::mimeType($newPath);
                    //         }

                    //         $record->update([
                    //             'path' => $newPath,
                    //             'mime' => $newMime,
                    //         ]);

                    //         return $record;
                    //     }),
                    DeleteAction::make()
                        ->using(function (Model $record) {
                            Storage::delete($record->path);
                            $record->delete();
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function (Collection $records) {
                            $records->each(function ($record) {
                                Storage::delete($record->path);
                                $record->delete();
                            });
                        })
                ]),
            ]);
    }
}

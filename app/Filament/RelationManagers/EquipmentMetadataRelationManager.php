<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\Documents\OpenFolderAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

abstract class EquipmentMetadataRelationManager extends RelationManager
{
    protected static bool $isLazy = false;

    protected const ATTACHED_DOCUMENT_FIELD = 'attached_document_path';

    abstract protected static function getFormComponents(): array;

    abstract protected static function getMetadataTableColumns(): array;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ...static::getFormComponents(),
            FileUpload::make(static::ATTACHED_DOCUMENT_FIELD)
                ->label('Documento anexo')
                ->disk('local')
                ->hiddenOn(Operation::View)
                ->directory(function (Get $get, RelationManager $livewire) {
                    $equipment = $livewire->getOwnerRecord();
                    $section = Str::headline((string) (static::$modelLabel ?? 'Documento'));
                    $descriptor = static::resolveDescriptorFromGet($get) ?? 'General';

                    return collect(['Equipos', $equipment->name, $section, $descriptor])
                        ->filter(fn($segment) => filled($segment))
                        ->join('/');
                })
                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                    $extension = $file->getClientOriginalExtension();
                    $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeName = (string) str($baseName)->slug('-');
                    $suffix = now()->format('YmdHis');

                    return "{$safeName}-{$suffix}.{$extension}";
                })
                ->downloadable()
                ->openable()
                ->previewable(false)
                ->helperText('Si cargas un nuevo archivo sobre un registro existente, se agrega como nueva versión.')
                ->columnSpanFull(),
            Placeholder::make('current_attached_document')
                ->label('Archivo actual')
                ->hiddenOn(Operation::Create)
                ->hidden(fn(?Model $record) => !$record || !$this->hasAttachedDocument($record))
                ->content(function (?Model $record) {
                    if (!$record) {
                        return 'Sin documento anexo';
                    }

                    $document = $record->documents()->with('current')->first();
                    $file = $document?->current;

                    if (!$file) {
                        return 'Sin documento anexo';
                    }

                    $url = route('files.preview', ['file' => $file]);
                    $name = basename($file->path);

                    return new HtmlString("<a href=\"{$url}\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"text-primary-600 underline\">{$name}</a>");
                })
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->getOwnerRecord()->trashed()) {
                    return $query->withTrashed()->with('documents.current');
                }

                return $query->with('documents.current');
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                ...static::getMetadataTableColumns(),
                IconColumn::make('attached_document')
                    ->label('Anexo')
                    ->state(fn(Model $record): bool => $this->hasAttachedDocument($record))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): Model {
                        $documentPath = $data[static::ATTACHED_DOCUMENT_FIELD] ?? null;
                        unset($data[static::ATTACHED_DOCUMENT_FIELD]);

                        $record = $this->getRelationship()->create($data);
                        $this->syncAttachedDocument($record, $documentPath);

                        return $record;
                    })
                    ->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.edit')),
            ])
            ->recordActions([
                ActionGroup::make([
                    OpenFolderAction::make(),
                    Action::make('openAttachedDocument')
                        ->label('Abrir anexo')
                        ->icon('heroicon-o-eye')
                        ->url(fn(Model $record): ?string => $this->getAttachedDocumentPreviewUrl($record), shouldOpenInNewTab: true)
                        ->hidden(fn(Model $record) => !$this->hasAttachedDocument($record) || !currentUserHasPermission('documents.show_file')),
                    ViewAction::make(),
                    EditAction::make()
                        ->using(function (Model $record, array $data): Model {
                            $documentPath = $data[static::ATTACHED_DOCUMENT_FIELD] ?? null;
                            unset($data[static::ATTACHED_DOCUMENT_FIELD]);

                            $record->update($data);
                            $this->syncAttachedDocument($record, $documentPath);

                            return $record;
                        })
                        ->hidden(fn($record) => $record->trashed() || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.edit')),
                    DeleteAction::make()
                        ->after(fn(Model $record) => $record->documents()->delete())
                        ->hidden(fn($record) => $record->trashed() || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.edit')),
                    RestoreAction::make()
                        ->after(fn(Model $record) => $record->documents()->withTrashed()->restore())
                        ->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('equipments.edit')),
                ]),
            ]);
    }

    protected static function resolveDescriptorFromArray(array $data): ?string
    {
        foreach (['name', 'document_name', 'sheet_number', 'blueprint_number', 'revision_name', 'part_number', 'catalog_number'] as $key) {
            $value = $data[$key] ?? null;

            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    protected static function resolveDescriptorFromGet(Get $get): ?string
    {
        foreach (['name', 'document_name', 'sheet_number', 'blueprint_number', 'revision_name', 'part_number', 'catalog_number'] as $key) {
            $value = $get($key);

            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    protected function resolveAttachedDocumentName(Model $record): string
    {
        $name = trim((string) ($record->name ?? ''));

        if ($name !== '') {
            return $name;
        }

        return (string) str((string) (static::$modelLabel ?? class_basename($record::class)))
            ->headline()
            ->append(' ', (string) $record->getKey());
    }

    protected function syncAttachedDocument(Model $record, ?string $documentPath): void
    {
        $document = $record->documents()->first();
        $documentName = $this->resolveAttachedDocumentName($record);

        if (!$document && !$documentPath) {
            return;
        }

        if (!$document) {
            $document = $record->documents()->create([
                'name' => $documentName,
            ]);
        } elseif ($document->name !== $documentName) {
            $document->update(['name' => $documentName]);
        }

        if (!$documentPath) {
            return;
        }

        $mime = check_solidworks(
            mime: Storage::mimeType($documentPath),
            path: $documentPath,
        );

        $document->files()->create([
            'path' => $documentPath,
            'mime' => mime_type($mime),
            'version' => ($document->files()->max('version') ?? 0) + 1,
        ]);
    }

    protected function hasAttachedDocument(Model $record): bool
    {
        return $record->documents->first()?->current !== null;
    }

    protected function getAttachedDocumentPreviewUrl(Model $record): ?string
    {
        $file = $record->documents->first()?->current;

        if (!$file?->path) {
            return null;
        }

        return route('files.preview', ['file' => $file]);
    }
}
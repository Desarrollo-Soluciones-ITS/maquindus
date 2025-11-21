<?php

namespace App\Filament\RelationManagers;

use App\Enums\Category;
use App\Filament\Actions\Documents\CreateAction;
use App\Filament\Actions\Documents\DeleteAction;
use App\Filament\Actions\Documents\DeleteBulkAction;
use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Actions\Documents\DownloadAction;
use App\Filament\Actions\Documents\EditAction;
use App\Filament\Actions\Documents\PreviewAction;
use App\Filament\Actions\Documents\ViewAction;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use App\Models\Equipment;
use App\Models\Part;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Documentos';

    protected static ?string $modelLabel = 'documento';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Manual de Operación de la Bomba P-5')
                    ->required(),
                Select::make('category')
                    ->label('Categoría')
                    ->options(Category::options())
                    ->placeholder('Ninguna')
                    ->visible(function (RelationManager $livewire) {
                        $documentable = $livewire->getOwnerRecord();

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
                                model: $documentable->type,
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

    public function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return DocumentsTable::configure($table)
            ->headerActions([
                CreateAction::make()->hidden(!currentUserHasPermission('relationships.documents.create')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ActionGroup::make([
                        PreviewAction::make()->hidden(!currentUserHasPermission('relationships.documents.show_file')),
                        OpenFolderAction::make()->hidden(!currentUserHasPermission('relationships.documents.open_in_folder')),
                        DownloadAction::make()->hidden(!currentUserHasPermission('relationships.documents.download')),
                        ViewAction::make()->hidden(!currentUserHasPermission('relationships.documents.show')),
                    ])->dropdown(false),
                    EditAction::make()->hidden(!currentUserHasPermission('relationships.documents.edit')),
                    DeleteAction::make()->hidden(!currentUserHasPermission('relationships.documents.delete')),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->hidden(!currentUserHasPermission('relationships.documents.delete')),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\RelationManagers;

use App\Enums\Type;
use App\Filament\Actions\Documents\CreateAction;
use App\Filament\Actions\Documents\DeleteAction;
use App\Filament\Actions\Documents\DeleteBulkAction;
use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Actions\Documents\DownloadAction;
use App\Filament\Actions\Documents\EditAction;
use App\Filament\Actions\Documents\PreviewAction;
use App\Filament\Actions\Documents\ViewAction;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Tables\Columns\TextColumn;
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
                Select::make('type')
                    ->label('Tipo')
                    ->options(Type::options())
                    ->required(),
                FileUpload::make('path')
                    ->label('Archivo')
                    ->disk('local')
                    ->hiddenOn(Operation::Edit)
                    ->directory(
                        function (Get $get, RelationManager $livewire) {
                            $documentable = $livewire->getOwnerRecord();
                            $parent = str($documentable::class)
                                ->explode('\\')
                                ->pop();
                            $folder = model_name_to_spanish_plural($parent);
                            return collect([$folder, $documentable->name, $get('type')])
                                ->join('/');
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
        return $table
            ->recordTitleAttribute('name')
            ->recordUrl(null)
            ->recordAction(is_not_localhost() ? 'download' : 'preview')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('current.created_at')
                    ->label('Última versión')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ])
            ->filters([
                //
            ])
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

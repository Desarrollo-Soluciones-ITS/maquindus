<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\Documents\CreateAction;
use App\Filament\Actions\Documents\DeleteAction;
use App\Filament\Actions\Documents\DownloadAction;
use App\Filament\Actions\Documents\EditAction;
use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Actions\Documents\PreviewAction;
use App\Filament\Actions\Documents\ViewAction;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Documentos';

    protected static ?string $modelLabel = 'documento';

    public function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
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
                    DeleteAction::make()->hidden(!currentUserHasPermission('relationships.documents.delete'))
                        ->label('Archivar')
                        ->icon(Heroicon::ArchiveBoxArrowDown),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}

<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\Documents\CreateAction;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Documentos';

    protected static ?string $modelLabel = 'documento';

    protected static bool $isLazy = false;

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
            ->modifyQueryUsing(function (Builder $query) {
                $parent = $this->getOwnerRecord();

                if (!$parent->trashed()) {
                    return $query;
                }

                return $query->withTrashed()
                    ->with(['documentable' => fn($query) => $query->withTrashed()]);
            })
            ->headerActions([
                CreateAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('documents.create')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}

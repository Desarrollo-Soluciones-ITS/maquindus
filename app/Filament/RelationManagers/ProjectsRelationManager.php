<?php

namespace App\Filament\RelationManagers;

use App\Enums\Prefix;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Schemas\ProjectInfolist;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Proyectos';

    protected static ?string $modelLabel = 'proyecto';

    public function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return ProjectInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ProjectsTable::configure($table)
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(code_to_full(Prefix::Project))
                    ->hidden(!currentUserHasPermission('projects.create')),
                AttachAction::make()->hidden(is_view_customer() || !currentUserHasPermission('projects.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('projects.show')),
                    EditAction::make()
                        ->mutateDataUsing(code_to_full(Prefix::Project))
                        ->hidden(!currentUserHasPermission('projects.edit')),
                    DetachAction::make()
                        ->hidden(is_view_customer() || !currentUserHasPermission('projects.unsync')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('projects.delete')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ])
            ]);
    }
}

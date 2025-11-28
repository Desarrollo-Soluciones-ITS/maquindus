<?php

namespace App\Filament\RelationManagers;

use App\Enums\Prefix;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Actions\EditAction;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Schemas\ProjectInfolist;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use App\Filament\Actions\RestoreAction;
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
                    ->mutateDataUsing(function ($data) {
                        $data = (code_to_full(Prefix::Project))($data);

                        // If this relation manager is used from a Customer record,
                        // set the customer_id automatically so the form doesn't need it.
                        if (method_exists($this, 'getOwnerRecord')) {
                            $owner = $this->getOwnerRecord();
                            if ($owner && $owner::class === \App\Models\Customer::class) {
                                $data['customer_id'] = $owner->id;
                            }
                        }

                        return $data;
                    })
                    ->hidden(!currentUserHasPermission('projects.create')),
                AttachAction::make()->hidden(is_view_customer() || !currentUserHasPermission('projects.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('projects.show')),
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('projects.edit'))
                        ->mutateDataUsing(code_to_full(Prefix::Project)),
                    DetachAction::make()
                        ->hidden(is_view_customer() || !currentUserHasPermission('projects.unsync')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('projects.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('projects.restore')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ])
            ]);
    }
}

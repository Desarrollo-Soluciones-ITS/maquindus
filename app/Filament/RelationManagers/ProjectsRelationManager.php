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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
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
                        // Log incoming payload to help debug date validation issues
                        try {
                            Log::info('ProjectsRelationManager create payload (before):', (array) $data);
                        } catch (\Throwable $e) {
                            // ignore logging failures
                        }

                        $data = (code_to_full(Prefix::Project))($data);

                        // Normalize date formats: convert d/m/Y (display) to Y-m-d (storage)
                        if (!empty($data['start']) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data['start'])) {
                            try {
                                $data['start'] = Carbon::createFromFormat('d/m/Y', $data['start'])->format('Y-m-d');
                            } catch (\Throwable $e) {
                                // leave as-is if parsing fails
                            }
                        }
                        if (!empty($data['end']) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data['end'])) {
                            try {
                                $data['end'] = Carbon::createFromFormat('d/m/Y', $data['end'])->format('Y-m-d');
                            } catch (\Throwable $e) {
                                // leave as-is if parsing fails
                            }
                        }

                        // If this relation manager is used from a Customer record,
                        // set the customer_id automatically so the form doesn't need it.
                        if (method_exists($this, 'getOwnerRecord')) {
                            $owner = $this->getOwnerRecord();
                            if ($owner && $owner::class === \App\Models\Customer::class) {
                                $data['customer_id'] = $owner->id;
                            }
                        }

                        try {
                            Log::info('ProjectsRelationManager create payload (after):', (array) $data);
                        } catch (\Throwable $e) {
                        }

                        return $data;
                    })
                    ->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('projects.create')),
                AttachAction::make()->hidden(is_view_customer() || !currentUserHasPermission('projects.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('projects.show')),
                    EditAction::make()->hidden(fn($record) => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('projects.edit'))
                        ->mutateDataUsing(code_to_full(Prefix::Project)),
                    DetachAction::make()
                        ->hidden(is_view_customer() || !currentUserHasPermission('projects.unsync')),
                    ArchiveAction::make()->hidden(fn($record) => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('projects.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$this->getOwnerRecord()->trashed() || !currentUserHasPermission('projects.restore')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ])
            ]);
    }
}

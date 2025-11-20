<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Schemas\ProjectInfolist;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
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
            ->headerActions([
                CreateAction::make(),
                AttachAction::make()
                    ->hidden($this->isCustomerPage()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DetachAction::make()
                        ->hidden($this->isCustomerPage()),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->hidden($this->isCustomerPage()),
                    DeleteBulkAction::make(),
                ])
            ]);
    }

    private function isCustomerPage()
    {
        return function (ProjectsRelationManager $livewire) {
            return $livewire->getPageClass() === ViewCustomer::class;
        };
    }
}

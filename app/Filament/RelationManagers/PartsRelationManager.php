<?php

namespace App\Filament\RelationManagers;

use App\Enums\Prefix;
use App\Filament\Resources\Parts\Schemas\PartForm;
use App\Filament\Resources\Parts\Schemas\PartInfolist;
use App\Filament\Resources\Parts\Tables\PartsTable;
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

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Repuestos';

    protected static ?string $modelLabel = 'repuesto';

    public function form(Schema $schema): Schema
    {
        return PartForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return PartInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return PartsTable::configure($table)
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(code_to_full(Prefix::Part))
                    ->hidden(!currentUserHasPermission('parts.create')),
                AttachAction::make()->hidden(!currentUserHasPermission('parts.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('parts.show')),
                    EditAction::make()
                        ->mutateDataUsing(code_to_full(Prefix::Part))
                        ->hidden(!currentUserHasPermission('parts.edit')),
                    DetachAction::make()->hidden(!currentUserHasPermission('parts.unsync')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ])
            ]);
    }
}

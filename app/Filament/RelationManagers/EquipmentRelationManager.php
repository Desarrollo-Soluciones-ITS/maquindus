<?php

namespace App\Filament\RelationManagers;

use App\Enums\Prefix;
use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
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

class EquipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'equipment';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Equipos';

    protected static ?string $modelLabel = 'equipo';

    public function form(Schema $schema): Schema
    {
        return EquipmentForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return EquipmentInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return EquipmentTable::configure($table)
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(code_to_full(Prefix::Equipment))
                    ->hidden(!currentUserHasPermission('relationships.equipments.create')),
                AttachAction::make()->hidden(!currentUserHasPermission('relationships.equipments.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('relationships.equipments.show')),
                    EditAction::make()
                        ->mutateDataUsing(code_to_full(Prefix::Equipment))
                        ->hidden(!currentUserHasPermission('relationships.equipments.edit')),
                    DetachAction::make()->hidden(!currentUserHasPermission('relationships.equipments.unsync')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ])
            ]);
    }
}

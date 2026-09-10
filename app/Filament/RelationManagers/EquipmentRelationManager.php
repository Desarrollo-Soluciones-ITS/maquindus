<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\EditAction;
use App\Filament\Filters\TextFilter;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\ViewAction;
use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
            ->filters([
                ...TextFilter::forColumns([
                    'name' => 'Nombre equipo',
                    'model' => 'Modelo',
                    'serial' => 'Serial',
                    'about' => 'Descripción',
                ], \App\Models\Equipment::class),
            ])
            ->headerActions([
                CreateAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.create')),
                AttachAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('equipments.show')),
                    EditAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.edit')),
                    DetachAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.unsync')),
                ])
            ]);
    }
}

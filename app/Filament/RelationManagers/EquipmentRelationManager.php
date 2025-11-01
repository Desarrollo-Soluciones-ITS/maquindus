<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
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
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DetachAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    DetachBulkAction::make(),
                ])
            ]);
    }
}

<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\Parts\Schemas\PartForm;
use App\Filament\Resources\Parts\Schemas\PartInfolist;
use App\Filament\Resources\Parts\Tables\PartsTable;
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
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make()
                    ->preloadRecordSelect(),
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

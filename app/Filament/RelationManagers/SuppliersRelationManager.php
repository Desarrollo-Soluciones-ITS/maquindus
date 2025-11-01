<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierInfolist;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
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

class SuppliersRelationManager extends RelationManager
{
    protected static string $relationship = 'suppliers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Proveedores';

    protected static ?string $modelLabel = 'proveedor';

    public function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return SupplierInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return SuppliersTable::configure($table)
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
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

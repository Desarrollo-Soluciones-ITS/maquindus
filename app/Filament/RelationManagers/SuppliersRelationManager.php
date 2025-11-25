<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierInfolist;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
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
                CreateAction::make()->hidden(!currentUserHasPermission('suppliers.create')),
                AttachAction::make()->hidden(!currentUserHasPermission('suppliers.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('suppliers.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('suppliers.edit')),
                    DetachAction::make()->hidden(!currentUserHasPermission('suppliers.unsync')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('suppliers.delete')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}

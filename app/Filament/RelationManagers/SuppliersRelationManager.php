<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Actions\EditAction;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierInfolist;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
use Filament\Actions\Action;
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
use Illuminate\Support\Str;

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
                CreateAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('suppliers.create')),
                AttachAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('suppliers.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('suppliers.show')),
                    EditAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('suppliers.edit')),
                    DetachAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('suppliers.unsync')),
                    ArchiveAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('suppliers.delete')),
                    RestoreAction::make()->hidden(fn() => !$this->getOwnerRecord()->trashed() || !currentUserHasPermission('suppliers.restore')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
                Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $ownerRecord = $livewire->getOwnerRecord();
                        $ownerName = Str::slug($ownerRecord->name ?? 'registro');
                        $fileName = "{$ownerName}-proveedores.xlsx";
                        $suppliers = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($suppliers) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $suppliers;
                            public function __construct($suppliers) { $this->suppliers = $suppliers; }
                            public function collection() { return $this->suppliers->map(function($supplier) {
                                return [
                                    'RIF' => $supplier->rif,
                                    'Nombre' => $supplier->name,
                                    'Correo' => $supplier->email,
                                    'Teléfono' => $supplier->phone,
                                ];
                            }); }
                            public function headings(): array { return ['RIF', 'Nombre', 'Correo', 'Teléfono']; }
                        }, $fileName);
                    }),
            ]);
    }
}

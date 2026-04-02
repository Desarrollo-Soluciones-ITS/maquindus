<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Models\Equipment;
use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SupplierPurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'supplierPurchaseOrders';

    protected static ?string $recordTitleAttribute = 'order_no';

    protected static ?string $title = 'Órdenes de compra proveedor';

    protected static ?string $modelLabel = 'orden de compra proveedor';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components(
            PurchaseOrderForm::getComponents(
                hideSupplier: $this->ownerIsSupplier(),
                defaultSupplierId: $this->ownerIsSupplier() ? $this->getOwnerRecord()->getKey() : null,
            )
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_no')
                    ->label('Código de orden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        if ($this->ownerIsSupplier()) {
                            $data['supplier_id'] = $this->getOwnerRecord()->getKey();
                        }

                        return $data;
                    })
                    ->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('purchase_orders.create')),
                AttachAction::make()->hidden(fn() => !$this->ownerIsEquipment() || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('purchase_orders.edit')),
            ])
            ->recordActions([
                DetachAction::make()->hidden(fn() => !$this->ownerIsEquipment() || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('purchase_orders.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
                Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $ownerRecord = $livewire->getOwnerRecord();
                        $ownerName = Str::slug($ownerRecord->name ?? 'registro');
                        $fileName = "{$ownerName}-ordenes-compra-proveedor.xlsx";
                        $orders = $query->get();

                        return \Maatwebsite\Excel\Facades\Excel::download(new class($orders) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $orders;

                            public function __construct($orders) { $this->orders = $orders; }

                            public function collection() {
                                return $this->orders->map(fn($order) => [
                                    'Proveedor' => $order->supplier?->name,
                                    'Código de orden' => $order->order_no,
                                    'Descripción' => $order->description,
                                    'Creado el' => $order->created_at,
                                ]);
                            }

                            public function headings(): array {
                                return ['Proveedor', 'Código de orden', 'Descripción', 'Creado el'];
                            }
                        }, $fileName);
                    }),
            ]);
    }

    protected function ownerIsSupplier(): bool
    {
        return $this->getOwnerRecord() instanceof Supplier;
    }

    protected function ownerIsEquipment(): bool
    {
        return $this->getOwnerRecord() instanceof Equipment;
    }
}
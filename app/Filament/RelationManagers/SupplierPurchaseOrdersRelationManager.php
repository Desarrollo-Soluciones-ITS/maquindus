<?php

namespace App\Filament\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\TextInput;
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
        return $schema->components([
            TextInput::make('order_no')
                ->label('Código de orden')
                ->minLength(3)
                ->maxLength(80)
                ->unique(ignoreRecord: true)
                ->required(),
            TextInput::make('description')
                ->label('Descripción')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
                CreateAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.edit')),
                AttachAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.edit')),
            ])
            ->recordActions([
                DetachAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('equipments.edit')),
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
                                    'Código de orden' => $order->order_no,
                                    'Descripción' => $order->description,
                                    'Creado el' => $order->created_at,
                                ]);
                            }

                            public function headings(): array {
                                return ['Código de orden', 'Descripción', 'Creado el'];
                            }
                        }, $fileName);
                    }),
            ]);
    }
}
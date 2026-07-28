<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Filters\ArchivedFilter;
use App\Filament\Filters\DateFilter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EquipmentSparePartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    protected static ?string $title = 'Repuestos';

    protected static ?string $modelLabel = 'repuesto';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('part_number')
                    ->label('N° de parte')
                    ->maxLength(80)
                    ->required(),
                TextInput::make('catalog_number')
                    ->label('N° de catálogo')
                    ->maxLength(80),
                TextInput::make('customer_part_number')
                    ->label('N° de parte del cliente')
                    ->maxLength(80),
                TextInput::make('about')
                    ->label('Descripción')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('part_number')
                    ->label('N° de parte')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('catalog_number')
                    ->label('N° de catálogo')
                    ->searchable(),
                TextColumn::make('customer_part_number')
                    ->label('N° de parte del cliente')
                    ->searchable(),
                TextColumn::make('about')
                    ->label('Descripción')
                    ->limit(60),
            ])
            ->filters([
                DateFilter::make(),
                ArchivedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): Model {
                        $record = $this->getRelationship()->create($data);

                        // Sincronizar con la tabla antigua equipment_spare_parts
                        try {
                            DB::table('equipment_spare_parts')->insert([
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'equipment_id' => $this->getOwnerRecord()->id,
                                'part_number' => $data['part_number'] ?? null,
                                'catalog_number' => $data['catalog_number'] ?? null,
                                'client_part_number' => $data['customer_part_number'] ?? null,
                                'description' => $data['about'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } catch (\Throwable) {
                            // Si falla, no importa, el dato principal ya se guardó
                        }

                        return $record;
                    })
                    ->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('parts.create')),
            ])
            ->recordActions([
                ActionGroup::make([
                    OpenFolderAction::make(),
                    ViewAction::make()
                        ->hidden(!currentUserHasPermission('parts.show')),
                    EditAction::make()
                        ->hidden(fn($record) => $record->trashed() || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('parts.edit')),
                    DeleteAction::make()
                        ->hidden(fn($record) => $record->trashed() || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('parts.delete')),
                    RestoreAction::make()
                        ->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('parts.restore')),
                ]),
            ]);
    }
}

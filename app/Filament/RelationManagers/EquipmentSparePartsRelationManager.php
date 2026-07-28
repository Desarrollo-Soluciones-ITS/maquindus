<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Filters\ArchivedFilter;
use App\Filament\Filters\DateFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use App\Models\Part;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
            ->defaultSort('parts.created_at', 'desc')
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
                        $part = Part::create($data);
                        $this->getOwnerRecord()->parts()->attach($part->id);
                        return $part;
                    })
                    ->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('parts.create')),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $ownerRecord = $livewire->getOwnerRecord();
                        $ownerName = \Illuminate\Support\Str::slug($ownerRecord->name ?? 'registro');
                        $fileName = "{$ownerName}-repuestos.xlsx";
                        $records = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($records) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $records;
                            public function __construct($records) { $this->records = $records; }
                            public function collection() {
                                return $this->records->map(fn($r) => [
                                    'N° de parte' => $r->part_number,
                                    'N° de catálogo' => $r->catalog_number,
                                    'N° parte cliente' => $r->customer_part_number,
                                    'Descripción' => $r->about,
                                ]);
                            }
                            public function headings(): array {
                                return ['N° de parte', 'N° de catálogo', 'N° parte cliente', 'Descripción'];
                            }
                        }, $fileName);
                    }),
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

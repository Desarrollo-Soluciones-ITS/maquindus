<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Actions\EditAction;
use App\Filament\Filters\TextFilter;
use App\Filament\Resources\People\Schemas\PersonForm;
use App\Filament\Resources\People\Schemas\PersonInfolist;
use App\Filament\Resources\People\Tables\PeopleTable;
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

class PeopleRelationManager extends RelationManager
{
    protected static string $relationship = 'people';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Contactos';

    protected static ?string $modelLabel = 'contacto';

    public function form(Schema $schema): Schema
    {
        return PersonForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return PersonInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return PeopleTable::configure($table)
            ->filters([
                ...TextFilter::forColumns([
                    'name' => 'Nombre',
                    'email' => 'Correo',
                    'phone' => 'Teléfono',
                    'position' => 'Cargo',
                    'personable.name' => 'Empresa',
                ], \App\Models\Person::class),
            ])
            ->headerActions([
                CreateAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.create')),
                AttachAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('people.show')),
                    EditAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.edit')),
                    DetachAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.unsync')),
                    ArchiveAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.delete')),
                    RestoreAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || !$this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.restore')),
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
                        $ownerName = (string) ($ownerRecord->name ?? 'registro');
                        $fileName = Str::slug($ownerName) . '-contactos.xlsx';
                        $title = $ownerName . ' — Contactos';

                        $rows = $query->get()->map(function ($person) {
                            return [
                                $person->name,
                                $person->surname,
                                $person->email,
                                $person->phone,
                                $person->position,
                            ];
                        })->all();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\RelationManagerExcelExport(
                                $title,
                                ['Nombre', 'Apellido', 'Correo', 'Teléfono', 'Cargo'],
                                $rows,
                            ),
                            $fileName,
                        );
                    }),
            ]);
    }
}

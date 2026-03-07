<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Actions\EditAction;
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
            ->filters([])
            ->headerActions([
                CreateAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.create')),
                AttachAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('people.show')),
                    EditAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.edit')),
                    DetachAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.unsync')),
                    ArchiveAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.delete')),
                    RestoreAction::make()->hidden(fn() => !$this->getOwnerRecord()->trashed() || !currentUserHasPermission('people.restore')),
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
                        $fileName = "{$ownerName}-contactos.xlsx";
                        $people = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($people) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $people;
                            public function __construct($people) { $this->people = $people; }
                            public function collection() { return $this->people->map(function($person) {
                                return [
                                    'Nombre' => $person->name,
                                    'Apellido' => $person->surname,
                                    'Correo' => $person->email,
                                    'Teléfono' => $person->phone,
                                    'Cargo' => $person->position,
                                ];
                            }); }
                            public function headings(): array { return ['Nombre', 'Apellido', 'Correo', 'Teléfono', 'Cargo']; }
                        }, $fileName);
                    }),
            ]);
    }
}

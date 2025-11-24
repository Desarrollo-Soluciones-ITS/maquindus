<?php

namespace App\Filament\RelationManagers;

use App\Filament\Resources\People\Schemas\PersonForm;
use App\Filament\Resources\People\Schemas\PersonInfolist;
use App\Filament\Resources\People\Tables\PeopleTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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
                //
            ])
            ->headerActions([
                CreateAction::make()->hidden(!currentUserHasPermission('relationships.people.create')),
                AttachAction::make()->hidden(!currentUserHasPermission('relationships.people.sync')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('relationships.people.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('relationships.people.edit')),
                    DetachAction::make()->hidden(!currentUserHasPermission('relationships.people.unsync')),
                    DeleteAction::make()->hidden(!currentUserHasPermission('relationships.people.delete'))
                        ->label('Archivar')
                        ->icon(Heroicon::ArchiveBoxArrowDown),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}

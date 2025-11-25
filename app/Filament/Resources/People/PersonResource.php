<?php

namespace App\Filament\Resources\People;

use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\People\Pages\CreatePerson;
use App\Filament\Resources\People\Pages\EditPerson;
use App\Filament\Resources\People\Pages\ListPeople;
use App\Filament\Resources\People\Pages\ViewPerson;
use App\Filament\Resources\People\Schemas\PersonForm;
use App\Filament\Resources\People\Schemas\PersonInfolist;
use App\Filament\Resources\People\Tables\PeopleTable;
use App\Models\Person;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Identification;

    protected static ?string $recordTitleAttribute = 'fullname';

    protected static ?string $modelLabel = 'contacto';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return PersonForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PersonInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PeopleTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            'documents' => DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeople::route('/'),
            'create' => CreatePerson::route('/create'),
            'view' => ViewPerson::route('/{record}'),
            'edit' => EditPerson::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->fullname;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'surname'];
    }

    public static function canAccess(): bool
    {
        return currentUserHasPermission('people.view');
    }
    public static function canCreate(): bool
    {
        return currentUserHasPermission('people.create');
    }
    public static function canUpdate(): bool
    {
        return currentUserHasPermission('people.edit');
    }
    public static function canView(Model $record): bool
    {
        return currentUserHasPermission('people.show');
    }
    public static function canDelete(Model $record): bool
    {
        return currentUserHasPermission('people.delete');
    }
}

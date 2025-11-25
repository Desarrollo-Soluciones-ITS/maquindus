<?php

namespace App\Filament\Resources\Customers;

use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\RelationManagers\ProjectsRelationManager;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'cliente';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            'documents' => DocumentsRelationManager::class,
            'projects' => ProjectsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return currentUserHasPermission('customers.view');
    }
    public static function canCreate(): bool
    {
        return currentUserHasPermission('customers.create');
    }
    public static function canUpdate(): bool
    {
        return currentUserHasPermission('customers.edit');
    }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return currentUserHasPermission('customers.show');
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return currentUserHasPermission('customers.delete');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

<?php

namespace App\Filament\Resources\Equipment;

use App\Filament\RelationManagers\BlueprintsRelationManager;
use App\Filament\RelationManagers\CatalogsRelationManager;
use App\Filament\RelationManagers\DataSheetsRelationManager;
use App\Filament\RelationManagers\FieldQueriesRelationManager;
use App\Filament\RelationManagers\PartsRelationManager;
use App\Filament\RelationManagers\ReportsRelationManager;
use App\Filament\RelationManagers\StandardsRelationManager;
use App\Filament\RelationManagers\SupplierPurchaseOrdersRelationManager;
use App\Filament\RelationManagers\TechnicalSpecificationsRelationManager;
use App\Filament\Resources\Equipment\Pages\CreateEquipment;
use App\Filament\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Equipment\Pages\ListEquipment;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\Pages\EquipmentGallery;
use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
use App\Models\Equipment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'equipo';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EquipmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EquipmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipmentTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Especificación técnica', [
                DataSheetsRelationManager::class,
                BlueprintsRelationManager::class,
                CatalogsRelationManager::class,
                TechnicalSpecificationsRelationManager::class,
                StandardsRelationManager::class,
            ]),
            'supplierPurchaseOrders' => SupplierPurchaseOrdersRelationManager::class,
            'parts' => PartsRelationManager::class,
            'fieldQueries' => FieldQueriesRelationManager::class,
            'reports' => ReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipment::route('/'),
            'create' => CreateEquipment::route('/create'),
            'view' => ViewEquipment::route('/{record}'),
            'edit' => EditEquipment::route('/{record}/edit'),
            'gallery' => EquipmentGallery::route('/{record}/gallery'),
        ];
    }

    public static function canAccess(): bool
    {
        return currentUserHasPermission('equipments.view');
    }
    public static function canCreate(): bool
    {
        return currentUserHasPermission('equipments.create');
    }
    public static function canUpdate(): bool
    {
        return currentUserHasPermission('equipments.edit');
    }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return currentUserHasPermission('equipments.show');
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return currentUserHasPermission('equipments.delete');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

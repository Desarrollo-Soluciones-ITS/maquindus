<?php

namespace App\Filament\Resources\Equipment;

use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Equipment\Pages\CreateEquipment;
use App\Filament\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Equipment\Pages\ListEquipment;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\RelationManagers\PartsRelationManager;
use App\Filament\RelationManagers\ProjectsRelationManager;
use App\Filament\RelationManagers\SuppliersRelationManager;
use App\Filament\Resources\Equipment\Pages\EquipmentGallery;
use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
use App\Models\Equipment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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
            'documents' => DocumentsRelationManager::class,
            'parts' => PartsRelationManager::class,
            'projects' => ProjectsRelationManager::class,
            'suppliers' => SuppliersRelationManager::class,
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
}

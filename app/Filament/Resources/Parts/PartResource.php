<?php

namespace App\Filament\Resources\Parts;

use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\RelationManagers\EquipmentRelationManager;
// use App\Filament\RelationManagers\ImagesRelationManager;
use App\Filament\RelationManagers\ProjectsRelationManager;
use App\Filament\RelationManagers\SuppliersRelationManager;
use App\Filament\Resources\Parts\Pages\CreatePart;
use App\Filament\Resources\Parts\Pages\EditPart;
use App\Filament\Resources\Parts\Pages\ListParts;
use App\Filament\Resources\Parts\Pages\PartGallery;
use App\Filament\Resources\Parts\Pages\ViewPart;
use App\Filament\Resources\Parts\Schemas\PartForm;
use App\Filament\Resources\Parts\Schemas\PartInfolist;
use App\Filament\Resources\Parts\Tables\PartsTable;
use App\Models\Part;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PartResource extends Resource
{
    protected static ?string $model = Part::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::WrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'repuesto';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PartForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PartInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartsTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            'documents' => DocumentsRelationManager::class,
            'equipment' => EquipmentRelationManager::class,
            'projects' => ProjectsRelationManager::class,
            'suppliers' => SuppliersRelationManager::class,
            // 'images' => ImagesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParts::route('/'),
            'create' => CreatePart::route('/create'),
            'view' => ViewPart::route('/{record}'),
            'edit' => EditPart::route('/{record}/edit'),
            'gallery' => PartGallery::route('/{record}/gallery'),
        ];
    }

    public static function canAccess(): bool
    {
        return currentUserHasPermission('parts.view');
    }
    public static function canCreate(): bool
    {
        return currentUserHasPermission('parts.create');
    }
    public static function canUpdate(): bool
    {
        return currentUserHasPermission('parts.edit');
    }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return currentUserHasPermission('parts.show');
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return currentUserHasPermission('parts.delete');
    }
}

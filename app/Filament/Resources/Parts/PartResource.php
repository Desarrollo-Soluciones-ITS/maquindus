<?php

namespace App\Filament\Resources\Parts;

use App\Filament\RelationManagers\EquipmentRelationManager;
use App\Filament\RelationManagers\ImagesRelationManager;
use App\Filament\RelationManagers\ProjectsRelationManager;
use App\Filament\RelationManagers\SuppliersRelationManager;
use App\Filament\Resources\Parts\Pages\CreatePart;
use App\Filament\Resources\Parts\Pages\EditPart;
use App\Filament\Resources\Parts\Pages\ListParts;
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

    protected static ?string $modelLabel = 'parte';

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
        return PartsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'equipment' => EquipmentRelationManager::class,
            'projects' => ProjectsRelationManager::class,
            'suppliers' => SuppliersRelationManager::class,
            'images' => ImagesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParts::route('/'),
            'create' => CreatePart::route('/create'),
            'view' => ViewPart::route('/{record}'),
            'edit' => EditPart::route('/{record}/edit'),
        ];
    }
}

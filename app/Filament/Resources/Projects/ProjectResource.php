<?php

namespace App\Filament\Resources\Projects;

use App\Filament\RelationManagers\ActivitiesRelationManager;
use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\RelationManagers\EquipmentRelationManager;
// use App\Filament\RelationManagers\ImagesRelationManager;
use App\Filament\RelationManagers\PartsRelationManager;
use App\Filament\RelationManagers\PeopleRelationManager;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ProjectGallery;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Schemas\ProjectInfolist;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::HomeModern;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'proyecto';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            'equipment' => EquipmentRelationManager::class,
            'parts' => PartsRelationManager::class,
            'people' => PeopleRelationManager::class,
            'activities' => ActivitiesRelationManager::class,
            'documents' => DocumentsRelationManager::class,
            // 'images' => ImagesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
            'gallery' => ProjectGallery::route('/{record}/gallery'),
        ];
    }
}

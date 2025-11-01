<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\Category;
use App\Enums\Type;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Project;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Type::Blueprint => 'primary',
                        Type::Manual => 'warning',
                        Type::Report => 'success',
                        Type::Specs => 'gray',
                    }),
                TextEntry::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Category::Set => 'gray',
                        Category::Detail => 'primary',
                        Category::ToBuild => 'warning',
                        Category::AsBuilt => 'success',
                        Category::Operation => 'info',
                        Category::Maintenance => 'danger',
                    })
                    ->placeholder('N/A'),
                TextEntry::make('documentable.name')
                    ->label('Pertenece a')
                    ->formatStateUsing(function (Model $record, $state) {
                        $fullClass = $record->documentable_type;

                        $spanishName = match ($fullClass) {
                            Part::class      => 'Parte',
                            Equipment::class => 'Equipo',
                            Project::class   => 'Proyecto',
                            default          => 'Relacionado',
                        };

                        return "($spanishName) $state";
                    }),
                TextEntry::make('current.created_at')
                    ->label('Última versión')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ]);
    }
}

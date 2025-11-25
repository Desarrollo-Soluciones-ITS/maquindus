<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\Category;
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
                TextEntry::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn($state) => Category::colors($state)),
                TextEntry::make('documentable.name')
                    ->label('Pertenece a')
                    ->formatStateUsing(function (Model $record, $state) {
                        $model = $record->documentable_type;
                        $spanish = model_to_spanish($model) ?? 'Relacionado';
                        return "($spanish) $state";
                    }),
                TextEntry::make('current.created_at')
                    ->label('Última versión')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ]);
    }
}

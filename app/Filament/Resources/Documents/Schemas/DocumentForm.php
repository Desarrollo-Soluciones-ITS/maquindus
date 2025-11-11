<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\Category;
use App\Enums\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Manual de Operación de la Bomba P-5')
                    ->required(),
                Select::make('type')
                    ->label('Tipo')
                    ->options(Type::options())
                    ->required(),
                Select::make('category')
                    ->label('Categoría')
                    ->placeholder('Ninguna')
                    ->options(Category::options())
                    ->default(null),
            ]);
    }
}

<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\Project;
use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_no')
                    ->label('N° de Orden')
                    ->placeholder('Ej. OC-001')
                    ->minLength(3)
                    ->maxLength(8)
                    ->alphaNum()
                    ->unique()
                    ->required(),
                TextInput::make('description')
                    ->label('Descripción')
                    ->placeholder('Descripción de la orden')
                    ->maxLength(255),
            ]);
    }
}

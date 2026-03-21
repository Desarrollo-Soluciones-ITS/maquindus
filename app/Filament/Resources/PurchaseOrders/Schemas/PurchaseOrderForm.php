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
                    ->label('Código de orden')
                    ->placeholder('Ej. OC-CLIENTE-001')
                    ->minLength(3)
                    ->maxLength(80)
                    ->unique()
                    ->required(),
                TextInput::make('description')
                    ->label('Descripción')
                    ->placeholder('Descripción de la orden')
                    ->maxLength(255),
            ]);
    }
}

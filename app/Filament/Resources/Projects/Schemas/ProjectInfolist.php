<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\Status;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('code')
                    ->label('Código'),
                TextEntry::make('customer.name')
                    ->label('Cliente'),
                TextEntry::make('start')
                    ->label('Fecha de inicio')
                    ->date(),
                TextEntry::make('end')
                    ->label('Fecha de finalización')
                    ->date(),
                TextEntry::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        Status::Planning => 'primary',
                        Status::Ongoing => 'warning',
                        Status::Finished => 'success',
                    }),
                TextEntry::make('about')
                    ->label('Descripción')
                    ->default('N/A'),
            ]);
    }
}

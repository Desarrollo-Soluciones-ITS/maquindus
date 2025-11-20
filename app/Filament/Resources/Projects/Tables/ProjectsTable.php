<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\Status;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        Status::Planning => 'primary',
                        Status::Ongoing => 'warning',
                        Status::Finished => 'success',
                    }),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->color(Color::Blue)
                    ->url(fn($record) =>
                        ViewCustomer::getUrl(['record' => $record->customer_id])
                    )
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

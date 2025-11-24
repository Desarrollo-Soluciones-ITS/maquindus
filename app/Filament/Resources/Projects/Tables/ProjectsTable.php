<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\Status;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\DateFilter;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Status::Planning => 'primary',
                        Status::Ongoing => 'warning',
                        Status::Finished => 'success',
                    }),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->color(Color::Blue)
                    ->hidden(is_view_customer())
                    ->url(
                        fn($record) =>
                        ViewCustomer::getUrl(['record' => $record->customer_id])
                    )
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->sortable(is_not_relation_manager())
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
            ])
            ->filters([
                DateFilter::make(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Status::options()),
                SelectFilter::make('customer')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('projects.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('projects.edit')),
                    ArchiveAction::make()->hidden(!currentUserHasPermission('projects.delete')),
                ]),
            ])
                ->toolbarActions([
                    BulkActionGroup::make([

                    ]),
                ]);
    }
}

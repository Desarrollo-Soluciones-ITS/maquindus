<?php

namespace App\Filament\Resources\People\Tables;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use App\Filament\Actions\EditAction;
use App\Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PeopleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('personable.name')
                    ->label('Empresa')
                    ->searchable()
                    ->color(Color::Blue)
                    ->url(function (Model $record) {
                        if (!$record->personable)
                            return null;
                        $class = $record->personable::class;

                        $page = match ($class) {
                            Supplier::class => ViewSupplier::class,
                            Customer::class => ViewCustomer::class,
                        };

                        return $page::getUrl([
                            'record' => $record->personable->id
                        ]);
                    }),
            ])
            ->filters([
                SelectFilter::make('personable_id')
                    ->label('Empresa')
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Customer::query()
                            ->where('name', 'like', "%{$search}%")
                            ->limit(20)
                            ->unionAll(
                                Supplier::query()
                                    ->select('name', 'id')
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(20)
                            )
                            ->pluck('name', 'id')
                            ->all()
                    ),
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('people.show')),
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('people.edit')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('people.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('people.restore')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}

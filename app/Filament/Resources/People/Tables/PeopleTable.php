<?php

namespace App\Filament\Resources\People\Tables;

use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Filters\ArchivedFilter;
use App\Filament\Filters\TextFilter;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Models\Person;
use App\Models\Supplier;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
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
                            default => null,
                        };

                        if (!$page) {
                            return null;
                        }

                        return $page::getUrl([
                            'record' => $record->personable->id
                        ]);
                    }),
            ])
            ->filters([
                ...TextFilter::forColumns([
                    'name' => 'Nombre',
                    'email' => 'Correo',
                    'phone' => 'Teléfono',
                    'position' => 'Cargo',
                ], Person::class),
                SelectFilter::make('personable_id')
                    ->label('Empresa')
                    ->searchable()
                    ->multiple()
                    ->options(function (): array {
                        return Person::query()
                            ->whereNotNull('personable_id')
                            ->with('personable')
                            ->get()
                            ->mapWithKeys(fn (Person $person): array => [
                                (string) $person->personable_id => (string) ($person->personable?->name ?? $person->personable_id),
                            ])
                            ->sort()
                            ->all();
                    }),
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    OpenFolderAction::make(),
                    ViewAction::make()->hidden(!currentUserHasPermission('people.show')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}

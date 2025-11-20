<?php

namespace App\Filament\Resources\People\Tables;

use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PeopleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fullname')
                    ->label('Nombre')
                    ->searchable(['name', 'surname']),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Cargo')
                    ->placeholder('N/A')
                    ->searchable(),
                TextColumn::make('personable.name')
                    ->label('Empresa')
                    ->placeholder('N/A')
                    ->searchable()
                    ->color(Color::Blue)
                    ->url(function (Model $record) {
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
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

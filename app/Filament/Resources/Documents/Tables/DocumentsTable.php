<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\Category;
use App\Enums\Type;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Type::Blueprint => 'primary',
                        Type::Manual => 'warning',
                        Type::Report => 'success',
                        Type::Specs => 'gray',
                    }),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Category::Set => 'gray',
                        Category::Detail => 'primary',
                        Category::ToBuild => 'warning',
                        Category::AsBuilt => 'success',
                        Category::Operation => 'info',
                        Category::Maintenance => 'danger',
                    })
                    ->placeholder('N/A'),
                TextColumn::make('documentable.name')
                    ->label('Pertenece a')
                    ->formatStateUsing(function (Model $record, $state) {
                        $fullClass = $record->documentable_type;

                        $spanishName = match ($fullClass) {
                            Part::class      => 'Parte',
                            Equipment::class => 'Equipo',
                            Project::class   => 'Proyecto',
                            default          => 'Relacionado',
                        };

                        return "($spanishName) $state";
                    }),
                TextColumn::make('current.created_at')
                    ->label('Última versión')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('download')
                        ->label('Descargar')
                        ->icon(Heroicon::ArrowDown)
                        ->action(function (Model $record) {
                            try {
                                return Storage::download($record->current->path);
                            } catch (\Throwable $th) {
                                Notification::make()
                                    ->title('No se encontró el documento.')
                                    ->danger()
                                    ->send();
                            }
                        }),
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

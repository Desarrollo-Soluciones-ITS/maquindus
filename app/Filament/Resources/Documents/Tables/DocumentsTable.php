<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\Type;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
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
                TextColumn::make('documentable.name')
                    ->label('Pertenece a')
                    ->formatStateUsing(function (Model $record, $state) {
                        $fullClass = $record->documentable_type;
                        // TODO -> centralizar junto con función helper model_name_to_spanish_name()
                        $spanishName = match ($fullClass) {
                            Part::class      => 'Repuesto',
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
                    DeleteAction::make()->using(function (Model $record) {
                        $record->files()->each(function ($record) {
                            Storage::delete($record->path);
                            $record->delete();
                        });

                        $record->delete();
                    }),
                ])
            ]);
    }
}

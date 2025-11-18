<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\Type;
use App\Filament\Actions\Documents\DeleteAction;
use App\Filament\Actions\Documents\DownloadAction;
use App\Filament\Actions\Documents\EditAction;
use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Actions\Documents\PreviewAction;
use App\Filament\Actions\Documents\ViewAction;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Project;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(is_not_localhost() ? 'download' : 'preview')
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
                    ActionGroup::make([
                        PreviewAction::make(),
                        OpenFolderAction::make(),
                        DownloadAction::make(),
                        ViewAction::make(),
                    ])->dropdown(false),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ]);
    }
}

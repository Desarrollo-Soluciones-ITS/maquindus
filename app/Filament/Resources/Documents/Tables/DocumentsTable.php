<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\Category;
use App\Filament\Actions\Documents\DeleteAction;
use App\Filament\Actions\Documents\DownloadAction;
use App\Filament\Actions\Documents\EditAction;
use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Actions\Documents\PreviewAction;
use App\Filament\Actions\Documents\ViewAction;
use App\Filament\RelationManagers\DocumentsRelationManager;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->recordUrl(null)
            ->recordAction(is_not_localhost() ? 'download' : 'preview')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('current.mime')
                    ->label('Tipo de archivo')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn($state) => mime_type($state)),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->searchable()
                    ->placeholder('N/A')
                    ->color(fn($state): string => Category::colors($state)),
                TextColumn::make('documentable.name')
                    ->label('Pertenece a')
                    ->hiddenOn(DocumentsRelationManager::class)
                    ->formatStateUsing(function (Model $record, $state) {
                        $model = $record->documentable_type;
                        $spanish = model_to_spanish($model) ?? 'Relacionado';
                        return "($spanish) $state";
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

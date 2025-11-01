<?php

namespace App\Filament\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Imágenes';

    protected static ?string $modelLabel = 'imagen';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Archivo de Imagen')
                    ->directory('images')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles')
                    ->inlineLabel()
                    ->schema([
                        ImageEntry::make('path')
                            ->label('Imagen'),
                        TextEntry::make('mime')
                            ->label('Tipo de archivo')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('Subido el')
                            ->date('d/m/Y - g:i A')
                            ->timezone('America/Caracas'),
                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->date('d/m/Y - g:i A')
                            ->timezone('America/Caracas')
                    ])
                    ->columnSpanFull(),

                // Version 2
                // ImageEntry::make('path')
                //     ->inlineLabel()
                //     ->label('Imagen')
                //     ->columnSpanFull(),
                // TextEntry::make('mime')
                //     ->inlineLabel()
                //     ->label('Tipo de archivo')
                //     ->badge()
                //     ->columnSpanFull(),
                // TextEntry::make('created_at')
                //     ->inlineLabel()
                //     ->label('Subido el')
                //     ->date('d/m/Y - g:i A')
                //     ->timezone('America/Caracas')
                //     ->columnSpanFull(),
                // TextEntry::make('updated_at')
                //     ->inlineLabel()
                //     ->label('Última actualización')
                //     ->date('d/m/Y - g:i A')
                //     ->timezone('America/Caracas')
                //     ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('path')
                    ->label('Imagen'),
                TextColumn::make('mime')
                    ->label('Tipo de archivo')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Subido el')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Subir Imagen')
                    ->using(
                        function (array $data, RelationManager $livewire): Model {
                            $data = collect($data);
                            $path = $data->get('path');
                            $mime = Storage::mimeType($path);

                            return $livewire->getOwnerRecord()
                                ->images()
                                ->create([
                                    'path' => $path,
                                    'mime' => $mime,
                                ]);
                        }
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('download')
                        ->label('Descargar')
                        ->icon(Heroicon::ArrowDown)
                        ->action(function (Model $record) {
                            try {
                                return Storage::download($record->path);
                            } catch (\Throwable $th) {
                                Notification::make()
                                    ->title('No se encontró la imagen.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    ViewAction::make(),
                    EditAction::make()
                        ->using(function (Model $record, array $data): Model {
                            $oldPath = $record->path;
                            $newPath = $data['path'];
                            $newMime = $record->mime;

                            if ($oldPath !== $newPath) {
                                Storage::delete($oldPath);
                                $newMime = Storage::mimeType($newPath);
                            }

                            $record->update([
                                'path' => $newPath,
                                'mime' => $newMime,
                            ]);

                            return $record;
                        }),
                    DeleteAction::make()
                        ->using(function (Model $record) {
                            Storage::delete($record->path);
                            $record->delete();
                        }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function (Collection $records) {
                            $records->each(function ($record) {
                                Storage::delete($record->path);
                                $record->delete();
                            });
                        })
                ]),
            ]);
    }
}

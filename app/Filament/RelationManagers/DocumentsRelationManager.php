<?php

namespace App\Filament\RelationManagers;

use App\Enums\Category;
use App\Enums\Type;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Documentos';

    protected static ?string $modelLabel = 'documento';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Select::make('type')
                    ->label('Tipo')
                    ->options(Type::options())
                    ->required(),
                FileUpload::make('path')
                    ->label('Archivo')
                    ->disk('local')
                    ->hiddenOn(Operation::Edit)
                    ->directory(
                        function (Get $get, RelationManager $livewire) {
                            $documentable = $livewire->getOwnerRecord();
                            $parent = str($documentable::class)
                                ->explode('\\')
                                ->pop();
                            return collect([$parent, $documentable->name, $get('type')])
                                ->join('/');
                        }
                    )
                    ->getUploadedFileNameForStorageUsing(
                        function (TemporaryUploadedFile $file, Get $get ) {
                            $extension = $file->getClientOriginalExtension();
                            return str($get('name'))->append('.', $extension);
                        }
                    )
                    ->required(),
                Select::make('category')
                    ->label('Categoría')
                    ->placeholder('Ninguna')
                    ->options(Category::options())
                    ->default(null),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextEntry::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->placeholder('N/A'),
                TextEntry::make('current.created_at')
                    ->label('Última versión')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre'),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->placeholder('N/A'),
                TextColumn::make('current.created_at')
                    ->label('Última versión')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(
                        function (array $data, RelationManager $livewire): Model {
                            $data = collect($data);
                            $path = $data->get('path');
                            
                            $mime = Storage::mimeType($path);
                            
                            $document = $livewire->getOwnerRecord()
                                ->documents()
                                ->create($data->except('path')->all());

                            $document->files()->create([
                                'path' => $path,
                                'mime' => $mime,
                                'version' => 1,
                            ]);
                            
                            return $document;
                        }
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('download')
                        ->label('Descargar')
                        ->icon(Heroicon::ArrowDown)
                        ->action(function ($record) {
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
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

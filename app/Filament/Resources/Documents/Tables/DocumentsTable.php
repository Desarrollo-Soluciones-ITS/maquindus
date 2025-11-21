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
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Parts\Pages\ViewPart;
use App\Filament\Resources\People\Pages\ViewPerson;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Person;
use App\Models\Project;
use App\Models\Supplier;
use Filament\Actions\ActionGroup;
use Filament\Support\Colors\Color;
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
                    })
                    ->color(Color::Blue)
                    ->url(function (Model $record) {
                        $class = $record->documentable::class;

                        $page = match ($class) {
                            Part::class => ViewPart::class,
                            Person::class => ViewPerson::class,
                            Project::class => ViewProject::class,
                            Supplier::class => ViewSupplier::class,
                            Customer::class => ViewCustomer::class,
                            Equipment::class => ViewEquipment::class,
                        };

                        return $page::getUrl([
                            'record' => $record->documentable->id
                        ]);
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
                        PreviewAction::make()->hidden(!currentUserHasPermission('documents.show_file')),
                        OpenFolderAction::make()->hidden(!currentUserHasPermission('documents.open_in_folder')),
                        DownloadAction::make()->hidden(!currentUserHasPermission('documents.download')),
                        ViewAction::make()->hidden(!currentUserHasPermission('documents.show')),
                    ])->dropdown(false),
                    EditAction::make()->hidden(!currentUserHasPermission('documents.edit')),
                    DeleteAction::make()->hidden(!currentUserHasPermission('documents.delete')),
                ])
            ]);
    }
}

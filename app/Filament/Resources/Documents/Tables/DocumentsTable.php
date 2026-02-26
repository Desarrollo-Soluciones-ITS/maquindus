<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Enums\Category;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Actions\Documents\DownloadAction;
use App\Filament\Actions\Documents\EditAction;
use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Actions\Documents\PreviewAction;
use App\Filament\Actions\Documents\ViewAction;
use App\Filament\Filters\ArchivedFilter;
use App\Filament\Filters\DateFilter;
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
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use App\Filament\Actions\RestoreAction;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->recordUrl(null)
            ->recordAction('preview')
            ->defaultSort('current_created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('current.mime')
                    ->label('Tipo de archivo')
                    ->badge(),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn($state): string => Category::colors($state)),
                TextColumn::make('documentable.name')
                    ->label('Pertenece a')
                    ->searchable()
                    ->hiddenOn(DocumentsRelationManager::class)
                    ->formatStateUsing(function (Model $record, $state) {
                        $model = $record->documentable_type;
                        $spanish = model_to_spanish($model) ?? 'Relacionado';
                        return "($spanish) $state";
                    })
                    ->color(Color::Blue)
                    ->url(function (Model $record) {
                        $class = $record->documentable ? $record->documentable::class : null;

                        if (empty($class)) {
                            return null;
                        }

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
                TextColumn::make('current_created_at')
                    ->label('Última versión')
                    ->sortable(
                        true,
                        fn(Builder $query, string $direction) =>
                        $query->withAggregate('current', 'created_at')
                            ->orderBy('current_created_at', $direction)
                    )
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
                TextColumn::make('review_date')
                    ->label('Fecha de revisión')
                    ->sortable()
                    ->date('d/m/Y')
                    ->timezone('America/Caracas')
                    ->hidden(function (RelationManager|ListDocuments $livewire, Model|null $record) {
                        $documentable = null;
                        if ($livewire instanceof RelationManager) {
                            $documentable = $livewire->getOwnerRecord();
                        } else if ($record !== null) {
                            $documentable = $record->documentable;
                        }

                        return !$documentable instanceof Equipment
                            && !$documentable instanceof Part
                            && $documentable != null;
                    })
            ])
            ->filters([
                DateFilter::make('current.created_at')
                    ->query(function (Builder $query, ?Carbon $startDate, ?Carbon $endDate) {
                        $when = $startDate && $endDate;
                        return $query->when(
                            $when,
                            fn(Builder $middle) =>
                            $middle->whereHas(
                                'current',
                                fn(Builder $inner) =>
                                $inner->whereBetween(
                                    'files.created_at',
                                    [$startDate, $endDate]
                                )
                            )
                        );
                    }),
                SelectFilter::make('current.mime')
                    ->label('Tipo de archivo')
                    ->query(
                        fn(Builder $query, array $data) =>
                        !$data['value'] ? $query
                        : $query->whereHas(
                            'current',
                            fn(Builder $inner) =>
                            $inner->where('mime', '=', $data['value'])
                        )
                    )
                    ->options([
                        'PDF' => 'PDF',
                        'Imagen' => 'Imagen',
                        'Word' => 'Word',
                        'Excel' => 'Excel',
                        'PowerPoint' => 'PowerPoint',
                        'AutoCAD' => 'AutoCAD',
                        'SolidWorks' => 'SolidWorks',
                    ]),
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        ...Category::options(),
                        'N/A' => 'Sin categoría',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'];
                        if ($value === 'N/A') {
                            return $query->whereNull('category');
                        } else if ($value) {
                            return $query->where('category', $value);
                        }
                        return $query;
                    }),
                ArchivedFilter::make()
                    ->hidden(function (DocumentsRelationManager|ListDocuments $livewire) {
                        return $livewire instanceof DocumentsRelationManager && $livewire->getOwnerRecord()->trashed();
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ActionGroup::make([
                        PreviewAction::make()
                            ->hidden(fn($record) => empty($record->documentable) || !currentUserHasPermission('documents.show_file'))
                            ->before(function (Action $action, Model $record) {
                                if ($record->isRecordLocked()) {
                                    $message = $record->getLockStatusMessage();

                                    Notification::make()
                                        ->title('Acceso Denegado')
                                        ->body(Str::markdown($message))
                                        ->danger()
                                        ->send();

                                    $action->halt();
                                }
                            }),
                        OpenFolderAction::make()
                            ->hidden(fn($record) => empty($record->documentable) || !currentUserHasPermission('documents.open_in_folder'))
                            ->before(function (Action $action, Model $record) {
                                if ($record->isRecordLocked()) {
                                    $message = $record->getLockStatusMessage();

                                    Notification::make()
                                        ->title('Acceso Denegado')
                                        ->body(Str::markdown($message))
                                        ->danger()
                                        ->send();

                                    $action->halt();
                                }
                            }),
                        DownloadAction::make()
                            ->hidden(fn($record) => empty($record->documentable) || !currentUserHasPermission('documents.download'))
                            ->before(function (Action $action, Model $record) {
                                if ($record->isRecordLocked()) {
                                    $message = $record->getLockStatusMessage();

                                    Notification::make()
                                        ->title('Acceso Denegado')
                                        ->body(Str::markdown($message))
                                        ->danger()
                                        ->send();

                                    $action->halt();
                                }
                            }),
                        ViewAction::make()->hidden(!currentUserHasPermission('documents.show')),
                    ])->dropdown(false),
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('documents.edit')),
                    ArchiveAction::make()
                        ->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('documents.delete'))
                        ->before(function (Action $action, Model $record) {
                            if (empty($record)) {
                                Notification::make()
                                    ->title('Registro archivado')
                                    ->body("Este registro se encuentra ya archivado.")
                                    ->warning()
                                    ->send();

                                $action->halt();
                            }

                            if ($record->isRecordLocked()) {
                                $message = $record->getLockStatusMessage();

                                Notification::make()
                                    ->title('Acceso Denegado')
                                    ->body(Str::markdown($message))
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        }),
                    RestoreAction::make()->hidden(fn($record) => $record->documentable && $record->documentable->trashed() || !$record->trashed() || !currentUserHasPermission('documents.restore')),
                ])
            ]);
    }
}

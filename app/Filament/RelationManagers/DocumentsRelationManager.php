<?php

namespace App\Filament\RelationManagers;

use App\Filament\Actions\Documents\CreateAction;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Documentos';

    protected static ?string $modelLabel = 'documento';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return DocumentsTable::configure($table)
            ->modifyQueryUsing(function (Builder $query) {
                $parent = $this->getOwnerRecord();

                if (!$parent->trashed()) {
                    return $query;
                }

                return $query->withTrashed()
                    ->with(['documentable' => fn($query) => $query->withTrashed()]);
            })
            ->headerActions([
                CreateAction::make()->hidden(fn() => $this->getOwnerRecord()->trashed() || !currentUserHasPermission('documents.create')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
                Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $ownerRecord = $livewire->getOwnerRecord();
                        $ownerName = Str::slug($ownerRecord->name ?? 'registro');
                        $fileName = "{$ownerName}-documentos.xlsx";
                        $documents = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($documents) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $documents;
                            public function __construct($documents) { $this->documents = $documents; }
                            public function collection() { return $this->documents->map(function($doc) {
                                return [
                                    'Nombre' => $doc->name,
                                    'Tipo de archivo' => optional($doc->current)->mime,
                                    'Categoría' => $doc->category?->value,
                                    'Pertenece a' => optional($doc->documentable)->name,
                                    'Última versión' => $doc->current_created_at,
                                    'Fecha de revisión' => $doc->review_date,
                                ];
                            }); }
                            public function headings(): array { return ['Nombre', 'Tipo de archivo', 'Categoría', 'Pertenece a', 'Última versión', 'Fecha de revisión']; }
                        }, $fileName);
                    }),
            ]);
    }
}

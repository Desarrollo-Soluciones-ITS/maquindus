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
                CreateAction::make()->hidden(fn() => !relation_manager_owner_is_equipment($this) || $this->getOwnerRecord()->trashed() || !currentUserHasPermission('documents.create')),
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
                        $ownerName = (string) ($ownerRecord->name ?? 'registro');
                        $fileName = Str::slug($ownerName) . '-documentos.xlsx';
                        $title = $ownerName . ' — Documentos';

                        $rows = $query->get()->map(function ($doc) {
                            return [
                                $doc->name,
                                optional($doc->current)->mime,
                                $doc->category?->value,
                                optional($doc->documentable)->name,
                                $doc->current_created_at
                                    ? \Carbon\Carbon::parse($doc->current_created_at)->format('d/m/Y')
                                    : null,
                                $doc->review_date
                                    ? \Carbon\Carbon::parse($doc->review_date)->format('d/m/Y')
                                    : null,
                            ];
                        })->all();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\RelationManagerExcelExport(
                                $title,
                                ['Nombre', 'Tipo de archivo', 'Categoría', 'Pertenece a', 'Última versión', 'Fecha de revisión'],
                                $rows,
                            ),
                            $fileName,
                        );
                    }),
            ]);
    }
}

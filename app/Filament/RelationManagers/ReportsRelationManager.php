<?php

namespace App\Filament\RelationManagers;

use App\Filament\Filters\DateFilter;
use App\Filament\Filters\TextFilter;
use App\Filament\Traits\HasExportToExcel;
use App\Models\EquipmentReport;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReportsRelationManager extends EquipmentMetadataRelationManager
{
    use HasExportToExcel;

    protected static string $relationship = 'reports';

    protected static ?string $title = 'RFQ';

    protected static ?string $modelLabel = 'RFQ';

    /**
     * Los registros nuevos se almacenan en la carpeta RFQ.
     * El histórico permanece en la carpeta Reportes (no se mueve nada).
     */
    protected static ?string $sectionFolder = 'RFQ';

    protected static function getFormComponents(): array
    {
        return [
            DatePicker::make('document_date')->label('Fecha de Emisión')->native(false),
            TextInput::make('document_name')->label('Nombre del Documento')->maxLength(120),
            TextInput::make('revision')->label('N° de Revisión')->maxLength(80),
            TextInput::make('rqm_number')->label('N° de RQM')->maxLength(80),
        ];
    }

    protected static function getMetadataTableColumns(): array
    {
        return [
            TextColumn::make('document_date')->label('Fecha de Emisión')->date('d/m/Y')->sortable(),
            TextColumn::make('document_name')->label('Nombre del Documento')->searchable(),
            TextColumn::make('revision')->label('N° de Revisión')->searchable()->placeholder('—'),
            TextColumn::make('rqm_number')->label('N° de RQM')->searchable()->placeholder('—'),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                ...TextFilter::forColumns([
                    'document_name' => 'Nombre del Documento',
                    'revision' => 'N° de Revisión',
                    'rqm_number' => 'N° de RQM',
                ], EquipmentReport::class),
                DateFilter::make('document_date'),
            ])
            ->toolbarActions([static::getExportAction()]);
    }

    protected static function getExportColumns(): array
    {
        return ['document_date', 'document_name', 'revision', 'rqm_number'];
    }

    protected static function getExportLabels(): array
    {
        return ['Fecha de Emisión', 'Nombre del Documento', 'N° de Revisión', 'N° de RQM'];
    }

    /**
     * Descarga del documento anexo. Complementa las acciones existentes
     * (ver, ver en carpeta, abrir anexo) sin modificar su comportamiento.
     *
     * @return array<int, Action>
     */
    protected function getExtraRecordActions(): array
    {
        return [
            Action::make('downloadAttachedDocument')
                ->label('Descargar')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (Model $record) {
                    $file = $this->getAttachedDocumentFile($record);

                    if (! $file?->path) {
                        Notification::make()
                            ->title('No se encontró el documento.')
                            ->danger()
                            ->send();

                        return null;
                    }

                    return Storage::download($file->path);
                })
                ->hidden(fn(Model $record): bool => !$this->hasAttachedDocument($record) || !currentUserHasPermission('documents.download')),
        ];
    }
}
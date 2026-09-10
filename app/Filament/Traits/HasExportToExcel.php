<?php

namespace App\Filament\Traits;

use App\Exports\RelationManagerExcelExport;
use Filament\Actions\Action;
use Illuminate\Support\Str;

/**
 * Trait para agregar botón de exportación a Excel en subtabs de equipos.
 *
 * USO: En la subclase, implementar:
 *   protected static function getExportColumns(): array
 *   protected static function getExportLabels(): array
 */
trait HasExportToExcel
{
    public static function getExportAction(): Action
    {
        return Action::make('export')
            ->label('Exportar')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function ($livewire) {
                $query = $livewire->getFilteredTableQuery();
                $records = $query->get();

                $owner = $livewire->getOwnerRecord();
                $ownerName = (string) ($owner->name ?? ($owner->part_number ?? 'registro'));
                $sectionName = (string) (static::$title ?? 'registro');

                $fileName = Str::slug($ownerName) . '-' . Str::slug($sectionName) . '.xlsx';
                $title = "{$ownerName} — {$sectionName}";

                $columns = static::getExportColumns();
                $labels = static::getExportLabels();

                $rows = $records->map(function ($record) use ($columns) {
                    return collect($columns)
                        ->map(function ($col) use ($record) {
                            $value = data_get($record, $col);
                            if ($value instanceof \Carbon\Carbon) {
                                return $value->format('d/m/Y');
                            }
                            return $value;
                        })
                        ->all();
                })->all();

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new RelationManagerExcelExport($title, $labels, $rows),
                    $fileName,
                );
            });
    }
}
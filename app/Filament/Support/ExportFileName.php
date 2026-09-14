<?php

namespace App\Filament\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Throwable;

/**
 * Construye el nombre del archivo de exportación a Excel con el formato:
 *
 *     {nombre de la tabla}_{fecha actual}[_{filtros aplicados}].xlsx
 *
 * Los filtros aplicados se obtienen de las etiquetas de indicador que
 * Filament genera para cada filtro activo (getIndicators()->getLabel()),
 * por lo que quedan identificados tal como el usuario los ve en pantalla.
 */
class ExportFileName
{
    public static function make(string $tableName, ?object $livewire = null, string $extension = 'xlsx'): string
    {
        $segments = [
            Str::slug($tableName) ?: 'exportacion',
            now()->format('d-m-Y'),
        ];

        if ($filters = static::filterSummary($livewire)) {
            $segments[] = Str::slug($filters);
        }

        return implode('_', $segments) . '.' . $extension;
    }

    /**
     * Resumen legible de los filtros activos, o null si no hay ninguno.
     */
    public static function filterSummary(?object $livewire = null): ?string
    {
        $labels = static::activeFilterLabels($livewire);

        if (empty($labels)) {
            return null;
        }

        return implode(' - ', $labels);
    }

    /**
     * Etiquetas legibles de los filtros activos.
     *
     * @return array<int, string>
     */
    public static function activeFilterLabels(?object $livewire = null): array
    {
        if (! $livewire || ! method_exists($livewire, 'getTable')) {
            return [];
        }

        $labels = [];

        try {
            foreach ($livewire->getTable()->getFilters() as $filter) {
                foreach ($filter->getIndicators() as $indicator) {
                    $label = $indicator->getLabel();

                    if ($label instanceof Htmlable) {
                        $label = $label->toHtml();
                    }

                    $label = trim(strip_tags((string) $label));

                    if ($label !== '') {
                        $labels[] = $label;
                    }
                }
            }
        } catch (Throwable) {
            return [];
        }

        return $labels;
    }
}

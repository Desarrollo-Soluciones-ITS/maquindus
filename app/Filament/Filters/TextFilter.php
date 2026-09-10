<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;

/**
 * Filtro de selección (dropdown) cuyas opciones provienen de los valores
 * ya existentes en el sistema para cada columna (sin escritura manual).
 *
 * Para columnas con relación, usar notación con punto (ej. 'supplier.name').
 */
class TextFilter
{
    /**
     * @param array<string,string> $columns ['columna' => 'Etiqueta']
     * @param class-string $model
     * @return array<SelectFilter>
     */
    public static function forColumns(array $columns, string $model): array
    {
        return collect($columns)
            ->map(fn (string $label, string $column) => static::make($column, $label, $model))
            ->values()
            ->all();
    }

    /**
     * @param class-string $model
     */
    public static function make(string $column, string $label, string $model): SelectFilter
    {
        if (str_contains($column, '.')) {
            [$relation, $relatedColumn] = explode('.', $column, 2);

            return SelectFilter::make($column)
                ->label($label)
                ->relationship($relation, $relatedColumn)
                ->searchable()
                ->preload()
                ->multiple();
        }

        return SelectFilter::make($column)
            ->label($label)
            ->searchable()
            ->multiple()
            ->options(
                fn (): array => $model::query()
                    ->select($column)
                    ->distinct()
                    ->whereNotNull($column)
                    ->orderBy($column)
                    ->pluck($column, $column)
                    ->all()
            );
    }
}
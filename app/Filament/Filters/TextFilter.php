<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;

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

            if (static::relationIsMorphTo($model, $relation)) {
                throw new InvalidArgumentException(
                    "El filtro '{$column}' usa una relación polimórfica (MorphTo) sobre {$model}: "
                    . 'Filament genera SQL inválido en ese caso. '
                    . 'Declara un SelectFilter explícito sobre la clave foránea (por ejemplo con ->options()).'
                );
            }

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

    /**
     * Las relaciones polimórficas no pueden alimentar un SelectFilter de Filament:
     * MorphTo::getRelated() devuelve el modelo padre, por lo que el SQL termina
     * consultando una columna inexistente en la tabla propia (ej. activity_log.name).
     */
    private static function relationIsMorphTo(string $model, string $relation): bool
    {
        if (! class_exists($model) || ! method_exists($model, $relation)) {
            return false;
        }

        try {
            return Relation::noConstraints(
                fn () => (new $model)->{$relation}()
            ) instanceof MorphTo;
        } catch (\Throwable) {
            return false;
        }
    }
}
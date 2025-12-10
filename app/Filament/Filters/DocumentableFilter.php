<?php

namespace App\Filament\Filters;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class DocumentableFilter
{
    public static function make()
    {
        $options = documentables()->mapWithKeys(function (string $model) {
            return [$model => model_to_spanish($model, plural: true)];
        });

        return Filter::make('documentable')
            ->columnSpanFull()
            ->columns(2)
            ->schema([
                Select::make('documentable_type')
                    ->label('Pertenece a')
                    ->placeholder('Todos')
                    ->options($options)
                    ->live(),
                Select::make('documentable_id')
                    ->searchable()
                    ->hidden(fn(Get $get) => ! $get('documentable_type'))
                    ->label(function (Get $get) {
                        $type = $get('documentable_type');
                        return model_to_spanish($type) . ' relacionado';
                    })
                    ->getSearchResultsUsing(function (string $search, Get $get) {
                        $type = $get('documentable_type');
                        return $type::query()
                            ->where('name', 'like', "%{$search}%")
                            ->limit(10)
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->getOptionLabelUsing(function ($value, Get $get) {
                        $type = $get('documentable_type');

                        return $type::query()
                            ->find($value, ['name']);
                    }),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['documentable_type'],
                        fn(Builder $q, $type) => $q->where('documentable_type', $type)
                    )
                    ->when(
                        $data['documentable_id'],
                        fn(Builder $q, $id) => $q->where('documentable_id', $id)
                    );
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                $hasType = $data['documentable_type'] ?? null;
                $hasId = $data['documentable_id'] ?? null;
                $type = $data['documentable_type'];

                if ($hasType) {
                    $model = model_to_spanish($type, plural: true);

                    $indicators[] = Indicator::make("Pertenece a: {$model}")
                        ->removeField('documentable_type');
                }

                if ($hasType && $hasId) {
                    $id = $data['documentable_id'];

                    $record = $type::find($id, ['name']);

                    if (!$record) return $indicators;

                    $model = model_to_spanish($type);
                    $name = $record->name;
                    $label = "{$model} relacionado: {$name}";

                    $indicators[] = Indicator::make($label)
                        ->removeField('documentable_id');
                }

                return $indicators;
            });
    }
}

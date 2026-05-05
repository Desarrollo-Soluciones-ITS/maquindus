<?php

namespace App\Filament\Inputs;

use App\Enums\Prefix;
use App\Services\Code;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

class CodeInput
{
    public static function make(Prefix $prefix)
    {
        return TextInput::make('code')
            ->label('Código')
            ->prefix("$prefix->value-")
            ->suffix('-' . now()->format('Y-m-d'))
            ->placeholder('ABC123')
            ->minLength(3)
            ->maxLength(10)
            ->alphaNum()
            ->mask(RawJs::make(<<<'JS'
                $input.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10)
            JS))
            ->rule(fn(string $model, $record) => function (string $_, mixed $value, Closure $fail) use ($model, $record) {
                $exists = $model::query()
                    ->where('code', 'like', "%-{$value}-%")
                    ->when($record, fn($query) =>
                        $query->where('id', '<>', $record->id))
                    ->exists();

                if ($exists) {
                    $fail(trans('validation.unique'));
                }
            })
            ->live()
            ->formatStateUsing(fn($state) => Code::short($state))
            ->required();
    }
}

<?php

namespace App\Filament\Inputs;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

class PhoneInput {
    public static function make() {
        return TextInput::make('phone')
            ->label('Teléfono')
            ->placeholder('Ej. 0412-1234567')
            ->mask(RawJs::make(<<<'JS'
                $input.startsWith('0')
                    ? '99999999999'
                    : '+999999999999999999'
            JS))
            ->maxLength(19)
            ->tel()
            ->unique()
            ->required();
    }
}

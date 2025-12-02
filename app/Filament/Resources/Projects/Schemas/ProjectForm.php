<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\Prefix;
use App\Enums\Status;
use App\Filament\Inputs\CodeInput;
use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Carbon\Carbon;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Planta de ensayo')
                    ->rule(PreventIllegalCharacters::apply())
                    ->maxLength(80)
                    ->unique()
                    ->required(),
                CodeInput::make(Prefix::Project),
                DatePicker::make('start')
                    ->label('Fecha de inicio')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->rules([
                        'nullable',
                        function ($attribute, $value, $fail) {
                            if (empty($value)) {
                                return;
                            }

                            $end = request()->input('end');

                            try {
                                $s = Carbon::createFromFormat('d/m/Y', $value);
                            } catch (\Throwable $e) {
                                $fail('Formato de fecha de inicio inválido.');
                                return;
                            }

                            if (!empty($end)) {
                                try {
                                    $eDate = Carbon::createFromFormat('d/m/Y', $end);
                                } catch (\Throwable $e) {
                                    // If end is present but invalid, let end's validator report it
                                    return;
                                }

                                if ($s->gt($eDate)) {
                                    $fail('El campo fecha de inicio debe ser una fecha anterior o igual a end.');
                                }
                            }
                        }
                    ]),

                DatePicker::make('end')
                    ->label('Fecha de finalización')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->rules([
                        'nullable',
                        function ($attribute, $value, $fail) {
                            if (empty($value)) {
                                return;
                            }

                            $start = request()->input('start');

                            try {
                                $e = Carbon::createFromFormat('d/m/Y', $value);
                            } catch (\Throwable $e) {
                                $fail('Formato de fecha de finalización inválido.');
                                return;
                            }

                            if (!empty($start)) {
                                try {
                                    $sDate = Carbon::createFromFormat('d/m/Y', $start);
                                } catch (\Throwable $e) {
                                    // If start is present but invalid, let start's validator report it
                                    return;
                                }

                                if ($e->lt($sDate)) {
                                    $fail('El campo fecha de finalización debe ser una fecha posterior o igual a start.');
                                }
                            }
                        }
                    ]),
                Select::make('status')
                    ->label('Estado')
                    ->options(Status::options())
                    ->required(),
                Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->hidden(fn($livewire) => $livewire instanceof \Filament\Resources\RelationManagers\RelationManager)
                    ->required(fn($livewire) => !($livewire instanceof \Filament\Resources\RelationManagers\RelationManager)),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Proyecto piloto para nueva línea')
                    ->columnSpanFull()
                    ->default(null),
            ]);
    }
}

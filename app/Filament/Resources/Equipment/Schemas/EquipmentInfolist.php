<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('code')
                    ->label('Código'),
                TextEntry::make('about')
                    ->label('Descripción'),
                KeyValueEntry::make('details')
                    ->label('Características')
                    ->keyLabel('Nombre')
                    ->columnSpanFull(),
                Section::make('Estructura de Carpetas')
                    ->columnSpanFull()
                    ->schema([
                        Html::make(
                            '<div style="font-size:0.95rem; line-height:1.5;">'
                            . '<p><strong>Ruta base:</strong> <code>Equipos/{NombreEquipo}</code></p>'
                            . '<ul>'
                            . '<li>Consultas de Campo</li>'
                            . '<li>Especificaciones Tecnicas<ul>'
                            . '<li>Catálogos</li>'
                            . '<li>Hoja De Datos</li>'
                            . '<li>Manuales</li>'
                            . '<li>Normas</li>'
                            . '<li>Planos</li>'
                            . '<li>Revisiones</li>'
                            . '</ul></li>'
                            . '<li>General</li>'
                            . '<li>Reportes</li>'
                            . '<li>Repuestos</li>'
                            . '</ul>'
                            . '</div>'
                        ),
                    ]),
            ]);
    }
}

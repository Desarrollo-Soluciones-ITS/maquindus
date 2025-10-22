<?php

namespace App\Enums;

enum Type: string
{
    use HasOptions;

    case Blueprint = 'Planos';
    case Manual = 'Manuales';
    case Report = 'Reportes';
    case Specs = 'Especificaciones Técnicas';
}

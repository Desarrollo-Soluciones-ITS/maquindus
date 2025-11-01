<?php

namespace App\Enums;

enum Category: string
{
    use HasOptions;

    case Set = 'Plano de conjunto';
    case Detail = 'Plano de detalle';
    case ToBuild = 'Plano para construir';
    case AsBuilt = 'Plano como construido';
    case Operation = 'Manual de operación';
    case Maintenance = 'Manual de mantenimiento';
}

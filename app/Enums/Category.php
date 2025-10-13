<?php

namespace App\Enums;

enum Category: string
{
    case SET = 'Plano de conjunto';
    case DETAIL = 'Plano de detalle';
    case TO_BUILD = 'Plano para construir';
    case AS_BUILT = 'Plano como construido';
    case OPERATION = 'Manual de operación';
    case MAINTENANCE = 'Manual de mantenimiento';
}

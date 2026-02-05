<?php

namespace App\Enums;

enum Status: string
{
    use HasOptions;

    case Posible = 'Posible';
    case Planning = 'Planificación';
    case awarded = 'Adjudicado';
    case Ongoing = 'En curso';
    case Finished = 'Finalizado';
}

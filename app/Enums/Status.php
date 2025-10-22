<?php

namespace App\Enums;

enum Status: string
{
    use HasOptions;

    case Planning = 'Planificación';
    case Ongoing = 'En curso';
    case Finished = 'Finalizado';
}

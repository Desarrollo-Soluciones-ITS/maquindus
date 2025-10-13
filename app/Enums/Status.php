<?php

namespace App\Enums;

enum Status: string
{
    case PLANNING = 'Planificación';
    case ONGOING = 'En curso';
    case FINISHED = 'Finalizado';
}

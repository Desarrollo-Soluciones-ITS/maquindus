<?php

namespace App\Enums;

enum Status: string
{
    case Planning = 'Planificación';
    case Ongoing = 'En curso';
    case Finished = 'Finalizado';
}

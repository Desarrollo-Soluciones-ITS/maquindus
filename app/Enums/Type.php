<?php

namespace App\Enums;

enum Type: string
{
    case Blueprint = 'Planos';
    case Manual = 'Manuales';
    case Report = 'Reportes';
    case Specs = 'Especificaciones Técnicas';
}

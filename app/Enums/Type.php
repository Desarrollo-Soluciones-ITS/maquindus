<?php

namespace App\Enums;

enum Type: string
{
    case BLUEPRINT = 'Plano';
    case MANUAL = 'Manual';
    case TECHNICAL = 'Hoja Técnica';
}

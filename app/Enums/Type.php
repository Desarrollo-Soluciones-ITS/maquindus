<?php

namespace App\Enums;

enum Type: string
{
    case Blueprint = 'Plano';
    case Manual = 'Manual';
    case Technical = 'Hoja Técnica';
}

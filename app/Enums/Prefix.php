<?php

namespace App\Enums;

enum Prefix: string
{
    use HasOptions;

    case Equipment = 'EQ';
    case Part = 'RE';
    case Project = 'PR';
}

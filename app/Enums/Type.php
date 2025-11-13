<?php

namespace App\Enums;

enum Type: string
{
    use HasOptions;

    case Blueprint = 'Planos';
    case Manual = 'Manuales';
    case Report = 'Reportes';
    case Specs = 'Fichas Técnicas';

    public function color(): string
    {
        return match ($this) {
            self::Blueprint => 'primary',
            self::Manual => 'warning',
            self::Report => 'success',
            self::Specs => 'gray',
        };
    }
}

<?php

namespace App\Enums;

enum Category: string
{
    use HasOptions;

    case Blueprint = 'Planos';
    case Manual = 'Manuales';
    case Report = 'Reportes';
    case Specs = 'Fichas Técnicas';
    case Offer = 'Oferta';

    public function color(): string
    {
        return static::colors($this);
    }

    public static function colors(Category $category): string
    {
        return match ($category) {
            self::Blueprint => 'primary',
            self::Manual => 'warning',
            self::Report => 'success',
            self::Specs => 'gray',
        };
    }
}

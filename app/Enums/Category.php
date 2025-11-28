<?php

namespace App\Enums;

enum Category: string
{
    use HasOptions;

    case Blueprint = 'Planos';
    case Manual = 'Manuales';
    case Report = 'Reportes';
    case Specs = 'Especificaciones Tecnicas';
    case Offer = 'Ofertas';
    case Photo = 'Fotos';

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
            self::Specs => 'success',
            self::Offer => 'warning',
            self::Photo => 'gray',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class Country extends Model
{
    use HasFactory, HasUuids;

    public const VENEZUELA_CACHE_KEY = 'venezuela';

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * País por defecto del sistema (Venezuela).
     *
     * Solo cachea resultados válidos. Si la fila no existe lanza una excepción
     * descriptiva en lugar del TypeError que producía el tipo de retorno estricto
     * (`Country::venezuela(): Country`) al devolver null.
     */
    public static function venezuela(): Country
    {
        $cached = Cache::get(static::VENEZUELA_CACHE_KEY);

        if ($cached instanceof Country) {
            return $cached;
        }

        $venezuela = static::query()
            ->where('name', 'Venezuela')
            ->first();

        if (! $venezuela) {
            throw new RuntimeException(
                "No existe el país 'Venezuela' en la tabla countries. Es requerido por el sistema "
                . '(formularios de Contactos y Proveedores). Ejecuta CountrySeeder o crea el registro.'
            );
        }

        Cache::put(static::VENEZUELA_CACHE_KEY, $venezuela);

        return $venezuela;
    }

    /**
     * Variante tolerante para valores por defecto de formularios:
     * devuelve null cuando el país no existe, sin romper la navegación.
     */
    public static function venezuelaOrNull(): ?Country
    {
        try {
            return static::venezuela();
        } catch (RuntimeException) {
            return null;
        }
    }
}

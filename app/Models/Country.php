<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Country extends Model
{
    use HasFactory, HasUuids;

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public static function venezuela(): Country
    {
        if (Cache::has('venezuela')) {
            return Cache::get('venezuela');
        }

        $venezuela = static::query()
            ->where('name', 'Venezuela')
            ->first();

        Cache::put('venezuela', $venezuela);

        return $venezuela;
    }
}

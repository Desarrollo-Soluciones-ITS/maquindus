<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SeededPermission extends Model
{
    use HasUuids;

    protected $table = 'seeded_permissions';

    public $timestamps = false;

    protected $fillable = [
        'seeder_class',
        'executed_at',
        'executed_by',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    /**
     * Check if a seeder has already been executed
     */
    public static function hasRun(string $seederClass): bool
    {
        return self::where('seeder_class', $seederClass)->exists();
    }

    /**
     * Mark a seeder as executed
     */
    public static function markAsRun(string $seederClass): void
    {
        self::updateOrCreate(
            ['seeder_class' => $seederClass],
            [
                'executed_at' => now(),
                'executed_by' => auth()->id() ?? 'system',
            ]
        );
    }
}

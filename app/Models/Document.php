<?php

namespace App\Models;

use App\Enums\Category;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, HasUuids, LogsActivity, HasActivityLog;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => Category::class,
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function current(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->files()->latest()->first();
        });
    }
}

<?php

namespace App\Models;

use App\Enums\Category;
use App\Traits\HasActivityLog;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, HasUuids, LogsActivity, HasActivityLog, SoftDeletes, Searchable;

    protected static function booted()
    {
        static::created(function ($model) {
            $model->updateSearchIndex();
        });

        static::updated(function ($model) {
            $model->updateSearchIndex();
        });

        static::deleted(function ($model) {
            $model->removeFromSearchIndex();
        });
    }

    protected $with = ['current'];

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

    public function current(): HasOne
    {
        return $this->hasOne(File::class)->latestOfMany();
    }
}

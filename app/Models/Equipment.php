<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Equipment extends Model
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

    /**
     * Additional attributes to ignore in the activity log.
     */
    protected $activityIgnoredAttributes = [
        'details',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}

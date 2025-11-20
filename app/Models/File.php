<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class File extends Model
{
    use HasFactory, HasUuids, LogsActivity, HasActivityLog;

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}

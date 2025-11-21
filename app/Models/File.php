<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;

class File extends Model
{
    use HasFactory, HasUuids, LogsActivity, HasActivityLog;

    protected static function booted(): void
    {
        static::creating(function (File $file) {
            if (empty($file->file_size) && !empty($file->path)) {
                $file->file_size = self::getFileSizeFromPath($file->path);
            }
        });

        static::updating(function (File $file) {
            if ($file->isDirty('path') && !empty($file->path)) {
                $file->file_size = self::getFileSizeFromPath($file->path);
            }
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        return self::formatBytes($this->file_size);
    }

    public static function formatBytes(float $bytes): string
    {
        if (!$bytes) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    private static function getFileSizeFromPath(string $path): ?int
    {
        try {
            if (Storage::exists($path)) {
                return Storage::size($path);
            }
        } catch (\Exception $e) {
            Log::warning("Could not get file size for path: {$path}", [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}

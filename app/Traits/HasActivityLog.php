<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;

trait HasActivityLog
{
    /**
     * Configuration for activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $spanishPlural = model_to_spanish($this::class, plural: true);

        return LogOptions::defaults()
            ->useLogName($spanishPlural)
            ->logAll()
            ->logExcept(['id', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) use ($spanishPlural) {
                $translatedEvent = translate_activity_verb($eventName);
                $singular = Str::singular($spanishPlural);
                return "Un {$singular} fue {$translatedEvent} en el sistema.";
            })
            ->logOnlyDirty();
    }
}

<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;

trait HasActivityLog
{
    /**
     * Configuration for activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $spanishPlural = model_to_spanish($this::class, plural: true);
        $spanishSingular = model_to_spanish($this::class);

        return LogOptions::defaults()
            ->useLogName($spanishPlural)
            ->logAll()
            ->logExcept(['id', 'created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) use ($spanishSingular) {
                $translatedEvent = translate_activity_verb($eventName);
                return "Un {$spanishSingular} fue {$translatedEvent} en el sistema.";
            })
            ->logOnlyDirty();
    }
}

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

        $fieldsToIgnore = ['id', 'created_at', 'updated_at'];

        if (property_exists($this, 'activityIgnoredAttributes')) {
            $fieldsToIgnore = array_merge($fieldsToIgnore, $this->activityIgnoredAttributes);
            $fieldsToIgnore = array_unique($fieldsToIgnore);
        }

        return LogOptions::defaults()
            ->useLogName($spanishPlural)
            ->logAll()
            ->logExcept($fieldsToIgnore)
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) use ($spanishSingular) {
                $translatedEvent = translate_activity_verb($eventName);
                return "Un {$spanishSingular} fue {$translatedEvent} en el sistema.";
            })
            ->logOnlyDirty();
    }
}

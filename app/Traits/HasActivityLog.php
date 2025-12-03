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
        $spanishSingular = strtolower(model_to_spanish($this::class));
        $fieldsToIgnore = ['id', 'created_at', 'updated_at'];

        if (property_exists($this, 'activityIgnoredAttributes')) {
            $fieldsToIgnore = array_merge($fieldsToIgnore, $this->activityIgnoredAttributes);
            $fieldsToIgnore = array_unique($fieldsToIgnore);
        }

        $article = $this::class === \App\Models\Activity::class
            ? 'Una'
            : 'Un';

        return LogOptions::defaults()
            ->useLogName($spanishPlural)
            ->logAll()
            ->logExcept($fieldsToIgnore)
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) use ($article, $spanishSingular) {
                $translatedEvent = Str::replaceLast('o', 'a', translate_activity_verb($eventName));
                return "{$article} {$spanishSingular} fue {$translatedEvent} en el sistema.";
            })
            ->logOnlyDirty();
    }
}

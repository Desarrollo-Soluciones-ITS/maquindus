<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;

trait HasActivityLog
{
    public static function bootHasActivityLog()
    {
        static::updating(function ($model) {
            if ($model->isDirty('code')) {
                $spanishPlural = model_to_spanish($model::class, plural: true);
                $fieldsToIgnore = ['id', 'created_at', 'updated_at'];

                if (property_exists($model, 'activityIgnoredAttributes')) {
                    $fieldsToIgnore = array_merge($fieldsToIgnore, $model->activityIgnoredAttributes);
                    $fieldsToIgnore = array_unique($fieldsToIgnore);
                }

                $oldCode = $model->getOriginal('code') ?? '(No definido)';
                $newCode = $model->code;

                $message = "El código {$oldCode} fue actualizado a {$newCode}.";

                activity()
                    ->useLog($spanishPlural)
                    ->performedOn($model)
                    ->causedBy(auth()->user())
                    ->setEvent('code_updated')
                    ->withProperties([
                        'old_code' => $oldCode,
                        'new_code' => $newCode,
                        'changed_at' => now(),
                        'model_type' => $model->getMorphClass(),
                        'model_id' => $model->id,
                    ])
                    ->log($message);
            }
        });
    }

    /**
     * Get the Filament page URL for viewing this model.
     */
    public function getActivityLogUrl(): ?string
    {
        $notShowableModels = ['File', 'Document', 'Permission', 'Role', 'User'];
        $class = class_basename($this);

        if (in_array($class, $notShowableModels)) {
            return null;
        }

        $page = match ($class) {
            'Part' => \App\Filament\Resources\Parts\Pages\ViewPart::class,
            'Person' => \App\Filament\Resources\People\Pages\ViewPerson::class,
            'Project' => \App\Filament\Resources\Projects\Pages\ViewProject::class,
            'Supplier' => \App\Filament\Resources\Suppliers\Pages\ViewSupplier::class,
            'Customer' => \App\Filament\Resources\Customers\Pages\ViewCustomer::class,
            'Equipment' => \App\Filament\Resources\Equipment\Pages\ViewEquipment::class,
            'Activity' => \App\Filament\Resources\ActivityLogs\Pages\ViewActivityLog::class,
            default => null,
        };

        return $page::getUrl(['record' => $this->id]) ?? null;
    }

    /**
     * Get the formatted description for activity logs.
     */
    public function getActivityLogDescription(string $event, string $description, array $properties = []): string
    {
        $translatedEvent = translate_activity_verb($event);
        $spanishSingular = strtolower(model_to_spanish(static::class));

        $recordName = $properties['attributes']['name'] ??
            $properties['attributes']['title'] ??
            $this->name ??
            $this->title ??
            '';

        $article = static::class === \App\Models\Activity::class ? 'Una' : 'Un';

        $baseDescription = "{$article} {$spanishSingular} fue {$translatedEvent} en el sistema.";

        if ($event === 'code_updated') {
            return $description;
        }

        if ($recordName && !Str::contains($baseDescription, $recordName)) {
            $formattedEvent = Str::replaceLast('o', 'a', $translatedEvent);

            $formattedDescription = Str::replace(
                ['Un', 'Una', $formattedEvent, $spanishSingular],
                ['El', 'La', $translatedEvent, "{$spanishSingular} <b>{$recordName}</b>"],
                $baseDescription
            );

            return $formattedDescription;
        }

        return $baseDescription;
    }

    /**
     * Check if this model should show in activity log links.
     */
    public function shouldShowInActivityLog(): bool
    {
        $notShowableModels = ['File', 'Document', 'Permission', 'Role', 'User'];
        $class = class_basename($this);

        return !in_array($class, $notShowableModels);
    }

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

        $recordName = $this->name ?? $this->title;

        return LogOptions::defaults()
            ->useLogName($spanishPlural)
            ->logAll()
            ->logExcept($fieldsToIgnore)
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) use ($article, $spanishSingular, $recordName) {
                $translatedEvent = translate_activity_verb($eventName);
                return "{$article} {$spanishSingular} fue {$translatedEvent} en el sistema.";
            })
            ->logOnlyDirty();
    }
}
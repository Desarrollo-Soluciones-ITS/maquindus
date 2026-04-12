<?php

namespace App\Traits;

use App\Models\SeededPermission;

trait TrackableSeeder
{
    /**
     * Check if this seeder has already been executed
     */
    protected function hasAlreadyRun(): bool
    {
        $className = class_basename($this);
        return SeededPermission::hasRun($className);
    }

    /**
     * Mark this seeder as executed
     */
    protected function markAsRun(): void
    {
        $className = class_basename($this);
        SeededPermission::markAsRun($className);
    }

    /**
     * Run the seeder if it hasn't been executed yet
     * Override this method in your seeder to implement custom logic
     */
    public function runIfNeeded(): void
    {
        if ($this->hasAlreadyRun()) {
            if (property_exists($this, 'command') && $this->command) {
                $this->command->info("✅ {$this->getName()} ya fue ejecutado. Omitiendo...");
            }
            return;
        }

        $this->run();
    }

    /**
     * Get the seeder name
     */
    protected function getName(): string
    {
        return class_basename($this);
    }
}

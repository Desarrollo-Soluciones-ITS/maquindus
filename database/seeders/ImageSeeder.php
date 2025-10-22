<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Project;
use App\Models\Part;
use Illuminate\Support\Str;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targets = [
            Equipment::class,
            Project::class,
            Part::class,
        ];

        foreach ($targets as $target) {
            $modelName = class_basename($target);
            $items = $target::inRandomOrder()->limit(5)->get();

            if ($items->isEmpty()) {
                continue;
            }

            foreach ($items as $item) {
                for ($i = 1; $i <= 2; $i++) {
                    $path = "images/" . Str::kebab($modelName) . "/{$item->id}/image-{$i}.jpg";

                    $item->images()->create([
                        'path' => $path,
                        'mime' => 'image/jpeg',
                    ]);
                }
            }
        }
    }
}

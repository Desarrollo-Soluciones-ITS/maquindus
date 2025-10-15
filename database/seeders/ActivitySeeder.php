<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Person;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::first();

        $activities = [
            ['comment' => 'Instalación de equipo principal', 'project_id' => $project->id],
            ['comment' => 'Pruebas de carga y calibración', 'project_id' => $project->id],
        ];

        foreach ($activities as $a) {
            $activity = Activity::create($a);

            // Asociar personas si existen
            $person = Person::first();
            if ($person) {
                $activity->people()->syncWithoutDetaching([$person->id]);
            }
        }
    }
}

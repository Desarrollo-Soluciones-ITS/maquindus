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
            ['title' => 'Instalación de equipo principal', 'comment' => 'Se inició la instalación del equipo principal del proyecto en el área designada.', 'project_id' => $project->id],
            ['title' => 'Pruebas de carga y calibración', 'comment' => 'Se realizaron las diferentes pruebas de carga y calibración con los procedimientos necesarios.', 'project_id' => $project->id],
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

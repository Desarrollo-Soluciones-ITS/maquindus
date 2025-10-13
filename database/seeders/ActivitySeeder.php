<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;
use App\Models\Project;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::first();

        $activities = [
            ['id' => (string) Str::uuid(), 'comment' => 'Instalación de equipo principal', 'project_id' => $project->id],
            ['id' => (string) Str::uuid(), 'comment' => 'Pruebas de carga y calibración', 'project_id' => $project->id],
        ];

        foreach ($activities as $a) {
            Activity::create($a);
        }
    }
}

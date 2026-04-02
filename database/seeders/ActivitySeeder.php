<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Person;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            ['title' => 'Instalación de equipo principal', 'comment' => 'Se inició la instalación del equipo principal en el área designada.'],
            ['title' => 'Pruebas de carga y calibración', 'comment' => 'Se realizaron las pruebas de carga y calibración con los procedimientos necesarios.'],
            ['title' => 'Inspección de repuestos críticos', 'comment' => 'Se verificó el inventario de repuestos estratégicos asociados a los equipos operativos.'],
        ];

        $peopleIds = Person::query()->limit(3)->pluck('id')->all();

        foreach ($activities as $a) {
            $activity = Activity::updateOrCreate(
                ['title' => $a['title']],
                $a,
            );

            if ($peopleIds !== []) {
                $activity->people()->syncWithoutDetaching($peopleIds);
            }
        }
    }
}

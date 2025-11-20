<?php

namespace Database\Seeders;

use App\Enums\Prefix;
use Illuminate\Database\Seeder;
use App\Enums\Status;
use App\Models\Project;
use App\Models\Customer;
use App\Models\Person;
use App\Models\Equipment;
use App\Models\Part;
use App\Services\Code;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();

        $projects = [
            ['name' => 'Planta de ensayo', 'code' => Code::full('PTE', Prefix::Project), 'about' => 'Proyecto piloto para nueva línea', 'start' => '2025-01-01', 'end' => '2025-06-30', 'status' => Status::Finished->value, 'customer_id' => $customer->id],
            ['name' => 'Mantenimiento anual', 'code' => Code::full('MAN', Prefix::Project), 'about' => 'Mantenimiento mayor de equipos críticos', 'start' => '2025-07-01', 'status' => Status::Ongoing->value, 'customer_id' => $customer->id],
        ];

        foreach ($projects as $pr) {
            $project = Project::create($pr);

            // Asociar una persona si existe
            $person = Person::first();
            if ($person) {
                $project->people()->syncWithoutDetaching([$person->id]);
            }

            // Asociar todos los equipments existentes al project (equipment_project pivot)
            $equipmentIds = Equipment::pluck('id')->toArray();
            if (!empty($equipmentIds)) {
                $project->equipment()->syncWithoutDetaching($equipmentIds);
            }

            // Asociar todas las parts existentes al project (part_project pivot)
            $partIds = Part::pluck('id')->toArray();
            if (!empty($partIds)) {
                $project->parts()->syncWithoutDetaching($partIds);
            }
        }
    }
}

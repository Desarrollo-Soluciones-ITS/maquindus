<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\State;
use App\Models\Activity;
use App\Models\Project;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();
        $supplier = Supplier::first();
        $state = State::with('cities')->first();
        $city = $state->cities[0];

        $people = [
            ['name' => 'Carlos', 'surname' => 'Pérez', 'email' => 'carlos.perez@vega.com', 'phone' => '04141234567', 'address' => 'Oficina 12', 'position' => 'Gerente de Planta', 'personable_type' => 'App\\Models\\Customer', 'personable_id' => $customer->id, 'state_id' => $state->id, 'city_id' => $city->id],
            ['name' => 'María', 'surname' => 'Gómez', 'email' => 'maria.gomez@suministros.com', 'phone' => '04149876543', 'address' => 'Sucursal', 'position' => 'Vendedora', 'personable_type' => 'App\\Models\\Supplier', 'personable_id' => $supplier->id, 'state_id' => $state->id, 'city_id' => $city->id],
        ];

        foreach ($people as $p) {
            $person = Person::create($p);

            // Relacionar activity_person (si existe alguna activity)
            $activity = Activity::first();
            if ($activity) {
                $activity->people()->syncWithoutDetaching([$person->id]);
            }

            // Relacionar person_project (si existe algún project)
            $project = Project::first();
            if ($project) {
                $project->people()->syncWithoutDetaching([$person->id]);
            }
        }
    }
}

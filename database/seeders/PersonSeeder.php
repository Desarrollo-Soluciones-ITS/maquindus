<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\Supplier;
use App\Models\State;
use App\Models\Activity;
use App\Models\Country;
use Illuminate\Support\Str;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $venId = Country::venezuela()->id;
        $state = State::with('cities')->first();
        $city = $state->cities[0];

        $people = Supplier::query()->get()->values()->map(function (Supplier $supplier, int $index) use ($venId, $state, $city) {
            return [
                'name' => ['María Gómez', 'Jorge Méndez', 'Ana Villarroel'][$index] ?? "Contacto {$index}",
                'email' => [
                    'maria.gomez@suministros.com',
                    'jorge.mendez@motorescentro.com',
                    'ana.villarroel@fluidosintegrales.com',
                ][$index] ?? "contacto{$index}@maquindus.local",
                'phone' => ['04149876543', '04141230001', '04141230002'][$index] ?? '04140000000',
                'address' => 'Oficina comercial principal',
                'position' => ['Vendedora', 'Coordinador técnico', 'Ejecutiva comercial'][$index] ?? 'Contacto',
                'personable_type' => Supplier::class,
                'personable_id' => $supplier->id,
                'country_id' => $venId,
                'state_id' => $state->id,
                'city_id' => $city->id,
            ];
        });

        foreach ($people as $p) {
            $person = Person::updateOrCreate(
                ['email' => $p['email']],
                $p,
            );

            // Relacionar activity_person (si existe alguna activity)
            $activity = Activity::first();
            if ($activity) {
                $activity->people()->syncWithoutDetaching([$person->id]);
            }
        }
    }
}

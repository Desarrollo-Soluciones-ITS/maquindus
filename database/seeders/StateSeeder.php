<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['name' => 'Distrito Capital'],
            ['name' => 'Amazonas'],
            ['name' => 'Anzoátegui'],
            ['name' => 'Apure'],
            ['name' => 'Aragua'],
            ['name' => 'Barinas'],
            ['name' => 'Bolívar'],
            ['name' => 'Carabobo'],
            ['name' => 'Cojedes'],
            ['name' => 'Delta Amacuro'],
            ['name' => 'Falcón'],
            ['name' => 'Guárico'],
            ['name' => 'La Guaira (Vargas)'],
            ['name' => 'Lara'],
            ['name' => 'Mérida'],
            ['name' => 'Miranda'],
            ['name' => 'Monagas'],
            ['name' => 'Nueva Esparta'],
            ['name' => 'Portuguesa'],
            ['name' => 'Sucre'],
            ['name' => 'Táchira'],
            ['name' => 'Trujillo'],
            ['name' => 'Yaracuy'],
            ['name' => 'Zulia'],
        ];

        foreach ($states as $state) {
            State::create($state);
        }
    }
}

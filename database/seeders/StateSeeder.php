<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['name' => 'Caracas'],
            ['name' => 'Miranda'],
            ['name' => 'Aragua'],
        ];

    State::insert($states);
    }
}

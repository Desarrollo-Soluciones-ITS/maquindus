<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use Str;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['id' => Str::uuid(), 'name' => 'Caracas'],
            ['id' => Str::uuid(), 'name' => 'Miranda'],
            ['id' => Str::uuid(), 'name' => 'Aragua'],
        ];

        State::insert($states);
    }
}

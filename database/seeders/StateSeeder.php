<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\State;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['id' => (string) Str::uuid(), 'name' => 'Caracas'],
            ['id' => (string) Str::uuid(), 'name' => 'Miranda'],
            ['id' => (string) Str::uuid(), 'name' => 'Aragua'],
        ];

    State::insert($states);
    }
}

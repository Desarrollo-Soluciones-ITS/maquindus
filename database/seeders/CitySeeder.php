<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\City;
use Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $states = State::all();

        $cities = [];
        foreach ($states as $state) {
            if ($state->name === 'Caracas') {
                $cities[] = ['id' => Str::uuid(), 'name' => 'Libertador', 'state_id' => $state->id];
                $cities[] = ['id' => Str::uuid(), 'name' => 'Chacao', 'state_id' => $state->id];
            } elseif ($state->name === 'Miranda') {
                $cities[] = ['id' => Str::uuid(), 'name' => 'Charallave', 'state_id' => $state->id];
                $cities[] = ['id' => Str::uuid(), 'name' => 'Petare', 'state_id' => $state->id];
            } else {
                $cities[] = ['id' => Str::uuid(), 'name' => 'Maracay', 'state_id' => $state->id];
            }
        }

        City::insert($cities);
    }
}

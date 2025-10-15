<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\City;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $states = State::all();

        $cities = [];
        foreach ($states as $state) {
            if ($state->name === 'Caracas') {
                $cities[] = ['name' => 'Libertador', 'state_id' => $state->id];
                $cities[] = ['name' => 'Chacao', 'state_id' => $state->id];
            } elseif ($state->name === 'Miranda') {
                $cities[] = ['name' => 'Charallave', 'state_id' => $state->id];
                $cities[] = ['name' => 'Petare', 'state_id' => $state->id];
            } else {
                $cities[] = ['name' => 'Maracay', 'state_id' => $state->id];
            }
        }

        City::insert($cities);
    }
}

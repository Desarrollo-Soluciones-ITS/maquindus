<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Enums\Status;
use App\Models\Project;
use App\Models\Customer;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();

        $projects = [
            ['id' => (string) Str::uuid(), 'name' => 'Planta de ensayo', 'code' => 'PRJ-001', 'about' => 'Proyecto piloto para nueva línea', 'start' => '2025-01-01', 'end' => '2025-06-30', 'status' => Status::PLANNING->value, 'customer_id' => $customer->id],
            ['id' => (string) Str::uuid(), 'name' => 'Mantenimiento anual', 'code' => 'PRJ-002', 'about' => 'Mantenimiento mayor de equipos críticos', 'start' => '2025-07-01', 'end' => '2025-07-15', 'status' => Status::ONGOING->value, 'customer_id' => $customer->id],
        ];

        foreach ($projects as $pr) {
            Project::create($pr);
        }
    }
}

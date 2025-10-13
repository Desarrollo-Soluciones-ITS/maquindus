<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Part;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            ['id' => (string) Str::uuid(), 'name' => 'Filtro principal', 'code' => 'PT-FLT-01', 'about' => 'Filtro de aceite', 'details' => json_encode(['material' => 'acero', 'diameter' => '50mm']), 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'name' => 'Bomba hidráulica', 'code' => 'PT-BMP-02', 'about' => 'Bomba de transferencia', 'details' => json_encode(['flow' => '120L/min', 'model' => 'BMP-120']), 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($parts as $p) {
            Part::create($p);
        }
    }
}

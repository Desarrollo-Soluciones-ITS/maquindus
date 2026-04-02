<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Part;
use App\Models\Equipment;
use App\Models\Supplier;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
<<<<<<< HEAD
            ['name' => 'Filtro principal de aceite', 'code' => Code::full('FTP', Prefix::Part), 'about' => 'Filtro metálico para sistemas de lubricación industrial.', 'details' => ['Material' => 'Acero inoxidable', 'Diámetro' => '50 mm', 'Aplicación' => 'Compresores'], 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bomba hidráulica de transferencia', 'code' => Code::full('BHD', Prefix::Part), 'about' => 'Bomba de transferencia para sistemas hidráulicos de potencia.', 'details' => ['Capacidad' => '120 L/min', 'Potencia' => '20 kW', 'Presión' => '250 bar'], 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tarjeta de control AVR', 'code' => Code::full('AVR', Prefix::Part), 'about' => 'Tarjeta electrónica para regulación automática de voltaje.', 'details' => ['Voltaje' => '24 VDC', 'Compatibilidad' => 'Generadores Perkins', 'Protección' => 'IP20'], 'created_at' => now(), 'updated_at' => now()],
=======
            [
                'name' => 'Filtro principal',
                'about' => 'Filtro de aceite',
                'details' => ['Material' => 'Acero', 'Diámetro' => '50mm'],
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Bomba hidráulica',
                'about' => 'Bomba de transferencia',
                'details' => ['Capacidad' => '120L/min', 'Potencia' => '200kw'],
                'created_at' => now(),
                'updated_at' => now()
            ],
>>>>>>> cae5521526c377eefb4655174b31f89dfc7f269a
        ];

        foreach ($parts as $p) {
            $part = Part::updateOrCreate(
                ['code' => $p['code']],
                $p,
            );

            $equipmentIds = Equipment::query()->limit(2)->pluck('id')->all();
            if ($equipmentIds !== []) {
                $part->equipment()->syncWithoutDetaching($equipmentIds);
            }

            $supplierIds = Supplier::query()->limit(2)->pluck('id')->all();
            if ($supplierIds !== []) {
                $part->suppliers()->syncWithoutDetaching($supplierIds);
            }
        }
    }
}
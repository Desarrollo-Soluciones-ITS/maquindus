<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\State;
use App\Models\City;
use App\Models\Country;
use App\Models\Equipment;
use App\Models\Part;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $venezuela = Country::venezuela();

        $aragua = State::where('name', 'Aragua')->first();
        $maracay = City::where('name', 'Maracay')->first();
        $carabobo = State::where('name', 'Carabobo')->first();
        $valencia = City::where('name', 'Valencia')->first();
        $miranda = State::where('name', 'Miranda')->first();
        $guarenas = City::where('name', 'Guarenas')->first();

        $suppliers = [
            [
                'rif' => 'J-10101010-2',
                'name' => 'Suministros Técnicos SA',
                'email' => 'ventas@suministros.com',
                'phone' => '02441234567',
                'about' => 'Proveedor de repuestos y equipos',
                'address' => 'Parque Industrial',
                'country_id' => $venezuela->id,
                'state_id' => $aragua->id,
                'city_id' => $maracay->id,
                'contact_name' => 'Luis Romero',
                'contact_position' => 'Gerente comercial',
                'contact_phone' => '04125550101',
            ],
            [
                'rif' => 'J-20202020-3',
                'name' => 'Motores Industriales del Centro',
                'email' => 'contacto@motorescentro.com',
                'phone' => '02411234567',
                'about' => 'Proveedor de motores, tableros y componentes electromecánicos.',
                'address' => 'Zona Industrial Castillito',
                'country_id' => $venezuela->id,
                'state_id' => $carabobo->id,
                'city_id' => $valencia->id,
                'contact_name' => 'Andrea Salazar',
                'contact_position' => 'Ejecutiva de cuentas',
                'contact_phone' => '04125550102',
            ],
            [
                'rif' => 'J-30303030-4',
                'name' => 'Servicios y Fluidos Integrales CA',
                'email' => 'ventas@fluidosintegrales.com',
                'phone' => '02121234567',
                'about' => 'Especialistas en bombas, sellos y fluidos para operación industrial.',
                'address' => 'Parque Comercial Guarenas',
                'country_id' => $venezuela->id,
                'state_id' => $miranda->id,
                'city_id' => $guarenas->id,
                'contact_name' => 'Mariana Torres',
                'contact_position' => 'Coordinadora de ventas',
                'contact_phone' => '04125550103',
            ],
        ];

        foreach ($suppliers as $s) {
            $supplier = Supplier::updateOrCreate(
                ['rif' => $s['rif']],
                $s,
            );

            $equipmentIds = Equipment::query()->limit(2)->pluck('id')->all();
            if ($equipmentIds !== []) {
                $supplier->equipment()->syncWithoutDetaching($equipmentIds);
            }

            $partIds = Part::query()->limit(2)->pluck('id')->all();
            if ($partIds !== []) {
                $supplier->parts()->syncWithoutDetaching($partIds);
            }
        }
    }
}

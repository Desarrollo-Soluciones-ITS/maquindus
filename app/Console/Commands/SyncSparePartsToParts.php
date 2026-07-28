<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\EquipmentSparePart;
use App\Models\Part;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSparePartsToParts extends Command
{
    protected $signature = 'parts:sync-from-spare';
    protected $description = 'Migra los datos de equipment_spare_parts a la tabla parts + equipment_part';

    public function handle(): int
    {
        $this->info('=== Sincronizando equipment_spare_parts → parts ===');
        $this->warn('Este comando NO elimina datos de equipment_spare_parts.');
        $this->warn('Los datos originales se conservan por seguridad.');
        $this->newLine();

        $spareParts = EquipmentSparePart::with('equipment')->get();

        if ($spareParts->isEmpty()) {
            $this->info('No hay registros en equipment_spare_parts para migrar.');
            return Command::SUCCESS;
        }

        $this->info("Total a procesar: {$spareParts->count()} repuestos.");
        $bar = $this->output->createProgressBar($spareParts->count());
        $bar->start();

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($spareParts as $spare) {
            try {
                DB::beginTransaction();

                // Buscar si ya existe un Part con este mismo part_number
                $part = null;
                if (filled($spare->part_number)) {
                    $part = Part::where('part_number', $spare->part_number)->first();
                }

                if (!$part) {
                    // Crear nuevo Part
                    $name = filled($spare->part_number)
                        ? $spare->part_number
                        : 'Repuesto-' . \Illuminate\Support\Str::uuid()->toString();

                    $part = Part::create([
                        'name' => $name,
                        'part_number' => $spare->part_number,
                        'catalog_number' => $spare->catalog_number,
                        'customer_part_number' => $spare->client_part_number,
                        'about' => $spare->description,
                    ]);
                    $created++;
                }

                // Vincular al equipo si tiene equipo y no está ya vinculado
                if ($spare->equipment_id && $spare->equipment) {
                    $alreadyLinked = $spare->equipment->parts()
                        ->where('part_id', $part->id)
                        ->exists();

                    if (!$alreadyLinked) {
                        $spare->equipment->parts()->attach($part->id);
                        $this->line("  Vinculado Part {$part->part_number} → Equipment {$spare->equipment->name}");
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $errors[] = "Error con spare part ID {$spare->id}: {$e->getMessage()}";
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("=== Resumen ===");
        $this->info("Creados: {$created}");
        $this->info("Saltados/Errores: {$skipped}");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('Errores encontrados:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        $this->newLine();
        $this->info('Los datos en equipment_spare_parts se conservan intactos.');
        $this->warn('Para verificar, revisa /parts y las pestañas de repuestos en los equipos.');
        $this->newLine();
        $this->info('¡Sincronización completada!');

        return Command::SUCCESS;
    }
}
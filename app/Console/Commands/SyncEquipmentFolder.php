<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncEquipmentFolder extends Command
{
    protected $signature = 'equipment:sync-folder';

    protected $description = 'Create the base folder structure for each equipment under storage/app/private/Equipos';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $base = 'Equipos';

        if (!$disk->exists($base)) {
            $disk->makeDirectory($base);
        }

        $equipmentFolders = ['Consultas de Campo', 'Especificaciones Tecnicas', 'General', 'Reportes', 'Repuestos'];
        $technicalSubfolders = ['Catálogos', 'Hoja De Datos', 'Manuales', 'Normas', 'Planos', 'Revisiones'];

        foreach (Equipment::all() as $equipment) {
            $equipmentRoot = $base . '/' . $equipment->name;

            foreach ($equipmentFolders as $folder) {
                $path = $equipmentRoot . '/' . $folder;
                if ($folder === 'Especificaciones Tecnicas') {
                    foreach ($technicalSubfolders as $subfolder) {
                        $disk->makeDirectory($path . '/' . $subfolder);
                    }
                } else {
                    $disk->makeDirectory($path);
                }
            }

            $this->line("Sincronizado: {$equipmentRoot}");
        }

        $this->info('Estructura de carpetas de Equipos sincronizada correctamente.');

        return self::SUCCESS;
    }
}

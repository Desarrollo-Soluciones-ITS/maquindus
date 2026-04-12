<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SeededPermission;
use Illuminate\Database\Seeder;

class FileManagerPermissionsSeeder extends Seeder
{
    /**
     * Nombre único para identificar este seeder
     */
    private const SEEDER_IDENTIFIER = 'FileManagerPermissionsSeeder';

    /**
     * Permisos del módulo filemanager
     */
    private array $filemanagerPermissions = [
        ['name' => 'Ver gestor de archivos', 'slug' => 'filemanager.view'],
        ['name' => 'Descargar archivos', 'slug' => 'filemanager.download'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si el seeder ya fue ejecutado
        if (SeededPermission::hasRun(self::SEEDER_IDENTIFIER)) {
            $this->command->info('✅ FileManagerPermissionsSeeder ya fue ejecutado. Omitiendo...');
            return;
        }

        $this->command->info('🚀 Ejecutando FileManagerPermissionsSeeder...');

        // Crear permisos de filemanager
        $createdPermissions = [];
        foreach ($this->filemanagerPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['slug' => $permissionData['slug']],
                ['name' => $permissionData['name']]
            );
            $createdPermissions[] = $permission;

            $this->command->info("  ✓ Permiso creado: {$permissionData['name']} ({$permissionData['slug']})");
        }

        // Asignar permisos al rol de administrador
        $adminRole = Role::first();

        if ($adminRole) {
            // Obtener todos los IDs de permisos existentes
            $allPermissionIds = Permission::pluck('id')->toArray();

            // Sincronizar todos los permisos al admin (incluyendo los nuevos)
            $adminRole->permissions()->sync($allPermissionIds);

            $this->command->info("  ✓ Permisos asignados al rol: {$adminRole->name}");
        } else {
            $this->command->warn('  ⚠ No se encontró el rol de administrador. Los permisos fueron creados pero no asignados.');
        }

        // Marcar el seeder como ejecutado
        SeededPermission::markAsRun(self::SEEDER_IDENTIFIER);

        $this->command->info('✅ FileManagerPermissionsSeeder completado exitosamente!');
    }
}

<?php

use App\Models\Equipment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('las paginas de listado del panel renderizan sin errores', function () {
    loginAsAdministrator();

    // Los formularios de personas/proveedores dependen de Country::venezuela().
    $this->seed(Database\Seeders\CountrySeeder::class);

    $routes = [
        'filament.dashboard.resources.activity-logs.index',
        'filament.dashboard.resources.documents.index',
        'filament.dashboard.resources.equipment.index',
        'filament.dashboard.resources.parts.index',
        'filament.dashboard.resources.people.index',
        'filament.dashboard.resources.purchase-orders.index',
        'filament.dashboard.resources.roles.index',
        'filament.dashboard.resources.suppliers.index',
        'filament.dashboard.resources.users.index',
    ];

    foreach ($routes as $route) {
        get(route($route))->assertOk();
    }

    // Filtro "Causado por" del listado de bitácora (clave foránea, no relación MorphTo).
    $causerId = (string) auth()->id();

    get(route('filament.dashboard.resources.activity-logs.index', [
        'tableFilters' => [
            'causer_id' => ['value' => [$causerId]],
        ],
    ]))->assertOk();

    // Filtro "Empresa" del listado de contactos.
    get(route('filament.dashboard.resources.people.index', [
        'tableFilters' => [
            'personable_id' => ['value' => []],
        ],
    ]))->assertOk();
});

test('las sub-tabs de equipo y proveedor renderizan sin errores', function () {
    loginAsAdministrator();

    $equipment = Equipment::forceCreate(['name' => 'Equipo smoke']);

    foreach ([null, 'equipmentSpareParts', 'fieldQueries', 'reports'] as $relation) {
        $parameters = ['record' => $equipment];

        if ($relation) {
            $parameters['relation'] = $relation;
        }

        get(route('filament.dashboard.resources.equipment.view', $parameters))->assertOk();
    }

    $supplier = Supplier::forceCreate(['name' => 'Proveedor smoke']);

    foreach ([null, 'documents', 'equipment', 'parts'] as $relation) {
        $parameters = ['record' => $supplier];

        if ($relation) {
            $parameters['relation'] = $relation;
        }

        get(route('filament.dashboard.resources.suppliers.view', $parameters))->assertOk();
    }
});

function loginAsAdministrator(): User
{
    $role = Role::create(['name' => 'Administrador']);

    $permissions = collect(Permission::buildDefinitions())
        ->map(fn (array $definition): Permission => Permission::create($definition));

    $role->permissions()->attach($permissions->pluck('id'));

    $user = User::factory()->create(['role_id' => $role->id]);

    actingAs($user);

    return $user;
}

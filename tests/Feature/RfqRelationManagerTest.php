<?php

use App\Filament\RelationManagers\FieldQueriesRelationManager;
use App\Filament\RelationManagers\ReportsRelationManager;
use App\Models\Equipment;
use App\Models\EquipmentReport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('el tab RFQ conserva la relacion reports y apunta a la carpeta RFQ', function () {
    expect(ReportsRelationManager::getRelationshipName())->toBe('reports')
        ->and(staticProperty(ReportsRelationManager::class, 'title'))->toBe('RFQ')
        ->and(staticProperty(ReportsRelationManager::class, 'modelLabel'))->toBe('RFQ')
        ->and(staticProperty(ReportsRelationManager::class, 'sectionFolder'))->toBe('RFQ');
});

test('los demas tabs de metadatos conservan la carpeta por defecto', function () {
    expect(staticProperty(FieldQueriesRelationManager::class, 'sectionFolder'))->toBeNull();
});

test('RFQ persiste fecha de emision, nombre, revision y numero de RQM', function () {
    $equipment = Equipment::forceCreate(['name' => 'Equipo RFQ']);

    $report = EquipmentReport::create([
        'equipment_id' => $equipment->id,
        'document_date' => '2026-01-15',
        'document_name' => 'RFQ de prueba',
        'revision' => 'R1',
        'rqm_number' => 'RQM-0001',
    ]);

    $fresh = $report->fresh();

    expect($fresh->revision)->toBe('R1')
        ->and($fresh->rqm_number)->toBe('RQM-0001')
        ->and($fresh->name)->toBe('RFQ de prueba')
        ->and($fresh->document_date->format('Y-m-d'))->toBe('2026-01-15');

    $this->assertDatabaseHas('equipment_reports', [
        'id' => $report->id,
        'revision' => 'R1',
        'rqm_number' => 'RQM-0001',
    ]);
});

function staticProperty(string $class, string $property): mixed
{
    $reflection = new ReflectionProperty($class, $property);
    $reflection->setAccessible(true);

    return $reflection->getValue();
}

test('el detalle de equipo renderiza el tab RFQ con sus columnas y acciones', function () {
    $permissions = collect([
        ['name' => 'Ver equipos', 'slug' => 'equipments.view'],
        ['name' => 'Ver detalle de equipo', 'slug' => 'equipments.show'],
        ['name' => 'Ver archivo de documento', 'slug' => 'documents.show_file'],
        ['name' => 'Descargar documento', 'slug' => 'documents.download'],
    ])->map(fn (array $data) => Permission::create($data));

    $role = Role::create(['name' => 'Equipos RFQ']);
    $role->permissions()->attach($permissions->pluck('id'));

    $user = User::factory()->create(['role_id' => $role->id]);
    $equipment = Equipment::forceCreate(['name' => 'Equipo RFQ render']);

    EquipmentReport::create([
        'equipment_id' => $equipment->id,
        'document_date' => '2026-01-15',
        'document_name' => 'RFQ render',
        'revision' => 'R3',
        'rqm_number' => 'RQM-0009',
    ]);

    actingAs($user);

    get(route('filament.dashboard.resources.equipment.view', [
        'record' => $equipment,
        'relation' => 'reports',
    ]))
        ->assertOk()
        ->assertSeeText('RFQ')
        ->assertSeeText('Fecha de Emisión')
        ->assertSeeText('Nombre del Documento')
        ->assertSeeText('N° de Revisión')
        ->assertSeeText('N° de RQM')
        ->assertSeeText('RQM-0009')
        ->assertSeeText('R3');
});

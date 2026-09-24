<?php

use App\Models\Country;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('devuelve el pais Venezuela cuando existe', function () {
    $country = Country::create(['name' => 'Venezuela']);

    expect(Country::venezuela()->id)->toBe($country->id);
});

test('lanza RuntimeException descriptiva en lugar de TypeError cuando Venezuela no existe', function () {
    expect(fn () => Country::venezuela())
        ->toThrow(RuntimeException::class, "No existe el país 'Venezuela' en la tabla countries");
});

test('no cachea valores nulos y se recupera al crear el pais despues', function () {
    try {
        Country::venezuela();
    } catch (RuntimeException) {
        // esperado: el país no existe todavía
    }

    expect(Cache::get(Country::VENEZUELA_CACHE_KEY))->toBeNull();

    $country = Country::create(['name' => 'Venezuela']);

    expect(Country::venezuela()->id)->toBe($country->id);
});

test('ignora valores cacheados que no son un Country', function () {
    Cache::put(Country::VENEZUELA_CACHE_KEY, null);
    Cache::put(Country::VENEZUELA_CACHE_KEY.'_basura', 'texto');

    expect(fn () => Country::venezuela())->toThrow(RuntimeException::class);

    $country = Country::create(['name' => 'Venezuela']);

    expect(Country::venezuela()->id)->toBe($country->id);
});

test('prioriza el valor en cache cuando contiene un Country', function () {
    Country::create(['name' => 'Venezuela']);
    $otro = Country::create(['name' => 'Otro país']);

    Cache::put(Country::VENEZUELA_CACHE_KEY, $otro);

    expect(Country::venezuela()->id)->toBe($otro->id);
});

test('venezuelaOrNull devuelve null sin lanzar excepcion', function () {
    expect(Country::venezuelaOrNull())->toBeNull();

    $country = Country::create(['name' => 'Venezuela']);

    expect(Country::venezuelaOrNull()?->id)->toBe($country->id);
});

test('contactos y proveedores renderizan aunque no exista el pais Venezuela', function () {
    loginAsPanelAdministrator();

    expect(Country::query()->where('name', 'Venezuela')->exists())->toBeFalse();

    get(route('filament.dashboard.resources.people.index'))->assertOk();
    get(route('filament.dashboard.resources.suppliers.index'))->assertOk();

    $person = App\Models\Person::forceCreate(['name' => 'Contacto sin país']);
    $supplier = App\Models\Supplier::forceCreate(['name' => 'Proveedor sin país']);

    get(route('filament.dashboard.resources.people.view', ['record' => $person]))->assertOk();
    get(route('filament.dashboard.resources.suppliers.view', ['record' => $supplier]))->assertOk();
});

function loginAsPanelAdministrator(): User
{
    $role = Role::create(['name' => 'Administrador']);

    $permissions = collect(Permission::buildDefinitions())
        ->map(fn (array $definition): Permission => Permission::create($definition));

    $role->permissions()->attach($permissions->pluck('id'));

    $user = User::factory()->create(['role_id' => $role->id]);

    actingAs($user);

    return $user;
}

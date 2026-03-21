<?php

use App\Models\Document;
use App\Models\File;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('guest cannot access file preview route', function () {
    $file = createPreviewableFile();

    get(route('files.preview', ['file' => $file]))
        ->assertRedirect('/login');
});

test('authenticated user without preview permission cannot access file preview route', function () {
    $file = createPreviewableFile();
    $user = createUserWithRole(Role::create(['name' => 'Sin permiso']));

    actingAs($user);

    get(route('files.preview', ['file' => $file]))
        ->assertForbidden();
});

test('authenticated user with preview permission can access file preview route', function () {
    $file = createPreviewableFile();
    $permission = Permission::create([
        'name' => 'Ver archivo de documento',
        'slug' => 'documents.show_file',
    ]);
    $role = Role::create(['name' => 'Documentos']);
    $role->permissions()->attach($permission);

    $user = createUserWithRole($role);

    actingAs($user);

    get(route('files.preview', ['file' => $file]))
        ->assertOk()
        ->assertHeader('content-type', 'text/plain; charset=UTF-8');
});

function createPreviewableFile(): File
{
    Storage::put('tests/preview.txt', 'contenido de prueba');

    $document = Document::create([
        'name' => 'Documento de prueba',
    ]);

    return File::create([
        'path' => 'tests/preview.txt',
        'mime' => 'Texto',
        'version' => 1,
        'document_id' => $document->id,
    ]);
}

function createUserWithRole(Role $role): User
{
    return User::factory()->create([
        'role_id' => $role->id,
    ]);
}
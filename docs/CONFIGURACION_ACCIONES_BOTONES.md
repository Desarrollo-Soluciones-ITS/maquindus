ña# Configuración de Acciones: "Ver en Carpeta" y "Abrir Archivo"

> **Fecha:** Julio 2026
> **Versión:** Commit `d20014a` (rama `version-servidor-local`)
> **Sistema:** Maquindus - Gestor de Archivos

---

## 📋 Índice

1. [Descripción General](#1-desripción-general)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Requisitos Previos](#3-requisitos-previos)
4. [Configuración del Archivo `.env`](#4-configuración-del-archivo-env)
5. [Configuración del Disco Local (`config/filesystems.php`)](#5-configuración-del-disco-local-configfilesystemsphp)
6. [Servidor PHP Auxiliar](#6-servidor-php-auxiliar)
7. [Archivos Clave del Sistema](#7-archivos-clave-del-sistema)
8. [Flujo de Funcionamiento](#8-flujo-de-funcionamiento)
9. [Instalación y Puesta en Marcha](#9-instalación-y-puesta-en-marcha)
10. [Solución de Problemas](#10-solución-de-problemas)
11. [Mantenimiento](#11-mantenimiento)

---

## 1. Descripción General

El sistema Maquindus tiene dos acciones principales para interactuar con los archivos almacenados:

| Acción | Botón | Función |
|--------|-------|---------|
| **Ver en carpeta** | `📂` | Abre el Explorador de Windows en la ubicación del archivo seleccionándolo |
| **Abrir archivo** | `👁️` | Abre el archivo en una nueva pestaña del navegador para vista previa |

Ambas acciones dependen de:
- Una correcta configuración del disco local de Laravel (`Storage`)
- Un servidor PHP auxiliar independiente para ejecutar comandos del sistema (Explorer)
- Los archivos físicos existentes en el servidor

---

## 2. Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    NAVEGADOR WEB                            │
│  (Usuario haciendo clic en botón "Ver en carpeta")          │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                LARAVEL (Backend PHP/Filament)                │
│                                                              │
│  1. OpenFolderAction::make()                                 │
│     └─ record_folder_url($record)                           │
│        └─ exec_url($filepath, 'folder')                     │
│           ├─ path($filepath, base:true) ───► Ruta absoluta  │
│           └─ http://127.0.0.1:8970/folder.php?path=...      │
│                                                              │
│  2. Http::timeout(5)->get($url)                              │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│           SERVIDOR PHP AUXILIAR (Puerto 8970)                │
│           (start-server.bat)                                 │
│                                                              │
│  scripts/folder.php                                          │
│    └─ Recibe ?path= (ruta absoluta)                          │
│    └─ Ejecuta: explorer /select,"ruta"                       │
│                                                              │
│  scripts/preview.php                                         │
│    └─ Recibe ?path= (ruta absoluta)                          │
│    └─ Ejecuta: cmd /c start "" "ruta"                        │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              EXPLORADOR DE WINDOWS                           │
│  (Se abre con el archivo seleccionado)                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Requisitos Previos

- **Sistema Operativo:** Windows (Server 2012 R2 o superior)
- **PHP:** 8.1, 8.2 o 8.3
- **Laravel:** 11+
- **Servidor Web:** IIS con PHP habilitado (o Laragon en desarrollo)
- **Storage:** Disco local con los archivos físicos almacenados

### Archivos físicos

Los archivos deben existir en el disco local del servidor en la ruta:
```
C:\inetpub\wwwroot\gestor-archivos\storage\app\private\...
```

Ejemplo de estructura:
```
C:\inetpub\wwwroot\gestor-archivos\storage\app\private\
├── Equipos\
│   ├── Alimentador AF 60 in\
│   │   └── Especificaciones Tecnicas\
│   │       └── Hoja De Datos\
│   │           └── INFORME.xlsx
│   └── Posicionador de Vagones\
│       └── Repuestos\
│           └── Catalogo.pdf
├── Contactos\
└── Proveedores\
```

---

## 4. Configuración del Archivo `.env`

Las siguientes variables son **ESENCIALES** para el funcionamiento:

```env
# ============================================
# CONFIGURACIÓN DE ARCHIVOS Y STORAGE
# ============================================

# Ruta raíz del storage (usada como fallback)
STORAGE_ROOT=C:/inetpub/wwwroot/gestor-archivos/storage

# URL del servidor PHP auxiliar para abrir carpetas/archivos
SHELL_API_URL=http://127.0.0.1:8970

# Ruta LOCAL física donde están los archivos
STORAGE_LOCAL_PATH=C:/inetpub/wwwroot/gestor-archivos/storage/app/private

# Ruta UNC (red) para acceso desde otros PCs en la LAN
STORAGE_NETWORK_PATH=\\\\192.168.0.4\\private

# Recurso compartido para el protocolo gestor:// (debe coincidir con STORAGE_NETWORK_PATH)
NETWORK_SHARE_ROOT=\\\\192.168.0.4\\private
```

> **⚠️ IMPORTANTE:** Las rutas usan `/` (slash normal), no `\` (backslash).

---

## 5. Configuración del Disco Local (`config/filesystems.php`)

El disco `local` de Laravel debe apuntar a la carpeta donde están los archivos físicos:

```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => env('STORAGE_LOCAL_PATH', env('STORAGE_ROOT', storage_path('app/private'))),
        'serve' => true,
        'throw' => false,
        'report' => false,
    ],
    // ... otros discos
],
```

> **Nota:** La variable `STORAGE_LOCAL_PATH` tiene prioridad sobre `STORAGE_ROOT`. Esto permite que el disco `local` apunte a `storage/app/private` en lugar de solo `storage/`.

---

## 6. Servidor PHP Auxiliar

### 6.1 ¿Qué es?

Es un servidor PHP independiente que corre en `http://127.0.0.1:8970` y se encarga de ejecutar comandos del sistema operativo Windows para abrir el Explorador de Archivos.

### 6.2 Scripts incluidos

| Archivo | Ruta | Función |
|---------|------|---------|
| `folder.php` | `scripts/folder.php` | Recibe una ruta y ejecuta `explorer /select,"ruta"` |
| `preview.php` | `scripts/preview.php` | Recibe una ruta y ejecuta `start "" "ruta"` |
| `start-server.bat` | `scripts/start-server.bat` | Inicia el servidor PHP auxiliar |

### 6.3 `scripts/folder.php`

```php
<?php
$input = $_GET['path'] ?? null;
$input = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $input);

// Si es ruta absoluta, úsala directamente
if (!preg_match('/^(?:[A-Za-z]:\\\\|\\\\\\\\)/', $input)) {
    $root = getenv('SHELL_SHARE_ROOT') ?: 'C:\\inetpub\\wwwroot\\gestor-archivos\\storage\\app\\private';
    $input = $root . DIRECTORY_SEPARATOR . ltrim($input, DIRECTORY_SEPARATOR);
}

$arg = escapeshellarg($input);
exec('cmd /c explorer /select,' . $arg, $out, $code);
?>
<script>window.close();</script>  <!-- Cierra la pestaña automáticamente -->
```

### 6.4 `scripts/start-server.bat`

```bat
@echo off
set SCRIPTS_DIR=C:\inetpub\wwwroot\gestor-archivos\scripts
set SHELL_SHARE_ROOT=C:\inetpub\wwwroot\gestor-archivos\storage\app\private

REM Busca PHP en rutas comunes
if exist "C:\laragon\bin\php\php8.3.0\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.3.0\php.exe
if exist "C:\laragon\bin\php\php8.2\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.2\php.exe
if exist "C:\php\php.exe" set PHP_PATH=C:\php\php.exe

start /B "" "%PHP_PATH%" -S 127.0.0.1:8970 -t "%SCRIPTS_DIR%"
```

---

## 7. Archivos Clave del Sistema

### 7.1 `app/Filament/Actions/Documents/OpenFolderAction.php`

Acción "Ver en carpeta":

```php
class OpenFolderAction
{
    public static function make(): Action
    {
        return Action::make('folder')
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(fn(Model $record) => blank(record_folder_url($record)))
            ->action(function (Model $record) {
                $url = record_folder_url($record);
                if ($url) {
                    Http::timeout(5)->get($url);
                }
            });
    }
}
```

### 7.2 `app/Filament/Actions/Documents/PreviewAction.php`

Acción "Abrir archivo":

```php
class PreviewAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label('Abrir archivo')
            ->icon(Heroicon::OutlinedEye)
            ->url(function ($record): ?string {
                $file = $record->current ?? $record;
                if (! $file instanceof File || blank($file->path)) return null;
                return route('files.preview', ['file' => $file]);
            }, shouldOpenInNewTab: true);
    }
}
```

### 7.3 `app/helpers.php` - Funciones clave

```php
// Convierte un path relativo a ruta absoluta y genera URL para el servidor auxiliar
function exec_url(string $filepath, string $endpoint): ?string
{
    $base = env('SHELL_API_URL', 'http://127.0.0.1:8970');
    try {
        $replaced = path($filepath, base: true); // Ruta absoluta
    } catch (\Throwable) {
        return null; // Si el archivo no existe, retorna null
    }
    $path = urlencode($replaced);
    return "$base/$endpoint.php?path=$path";
}

// Obtiene la URL para "Ver en carpeta" según el tipo de registro
function record_folder_url(Model $record): ?string
{
    if ($record instanceof File) {
        return filled($record->path) ? exec_url($record->path, 'folder') : null;
    }
    if ($record instanceof Document) {
        return filled($record->current?->path) ? exec_url($record->current->path, 'folder') : null;
    }
    // ...
}

// Convierte path relativo a absoluto verificando existencia en Storage
function path(string $path, $asFolder = false, $base = true): string
{
    $segments = str($path)->explode('/');
    if ($asFolder) $segments->pop();
    $folder = $segments->join('\\');
    
    // VALIDA que el archivo/carpeta exista en Storage (DISCO LOCAL)
    if ($asFolder && Storage::directoryMissing($folder))
        throw new Error('path() helper error: directory is missing');
    if (!$asFolder && Storage::fileMissing($folder))
        throw new Error('path() helper error: file is missing');
    
    return str($base ? Storage::path($folder) : $folder)
        ->replace('/', DIRECTORY_SEPARATOR)
        ->replace('\\', DIRECTORY_SEPARATOR);
}
```

### 7.4 `app/Http/Controllers/PreviewFileController.php`

Controlador que sirve el archivo para "Abrir archivo":

```php
class PreviewFileController extends Controller
{
    public function __invoke(File $file): BinaryFileResponse
    {
        abort_unless(Storage::exists($file->path), 404);
        
        $storagePath = Storage::path($file->path);
        $mimeType = Storage::mimeType($file->path) ?: 'application/octet-stream';
        
        return response()->file($storagePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($file->path) . '"',
        ]);
    }
}
```

### 7.5 `routes/web.php`

```php
Route::middleware(['permission.any:documents.show_file,files.show_file'])
    ->get('/files/{file}/preview', PreviewFileController::class)
    ->name('files.preview');
```

---

## 8. Flujo de Funcionamiento

### 8.1 "Ver en carpeta"

```
Usuario hace clic en "📂 Ver en carpeta"
        │
        ▼
OpenFolderAction::action($record)
        │
        ▼
record_folder_url($record)
        │
        ├── ¿El record es File? → exec_url($record->path, 'folder')
        ├── ¿El record es Document? → exec_url($record->current->path, 'folder')
        └── ¿Tiene documents()? → exec_url($document->current->path, 'folder')
                │
                ▼
        exec_url($filepath, 'folder')
                │
                ├── path($filepath, base: true) → Ruta absoluta
                │       │
                │       ├── ¿Storage::exists()? → Retorna ruta
                │       └── ¿NO existe? → Lanza excepción → exec_url retorna null
                │
                └── http://127.0.0.1:8970/folder.php?path=RUTA_ABSOLUTA
                        │
                        ▼
                Http::timeout(5)->get($url)  → Llamada HTTP al servidor auxiliar
                        │
                        ▼
                folder.php recibe la ruta
                        │
                        ▼
                exec('cmd /c explorer /select,"RUTA"')
                        │
                        ▼
                ¡Se abre el Explorador de Windows con el archivo seleccionado!
```

### 8.2 "Abrir archivo"

```
Usuario hace clic en "👁️ Abrir archivo"
        │
        ▼
PreviewAction::url($record)  →  route('files.preview', ['file' => $file])
        │
        ▼
PreviewFileController::__invoke(File $file)
        │
        ├── Storage::exists($file->path) → Verifica que exista
        ├── Storage::path($file->path) → Ruta absoluta
        └── response()->file($storagePath, ...) → Servir el archivo
                │
                ▼
        ¡Se abre una nueva pestaña con el archivo!
```

---

## 9. Instalación y Puesta en Marcha

### 9.1 Configuración inicial

**Paso 1:** Clonar el repositorio y configurar `.env`:

```bash
cd C:\inetpub\wwwroot
git clone https://github.com/Desarrollo-Soluciones-ITS/maquindus.git gestor-archivos
cd gestor-archivos
copy .env.example .env
```

**Paso 2:** Editar `.env` con las variables de configuración (ver [Sección 4](#4-configuración-del-archivo-env)).

**Paso 3:** Verificar `config/filesystems.php` que use `STORAGE_LOCAL_PATH`:

```php
'root' => env('STORAGE_LOCAL_PATH', env('STORAGE_ROOT', storage_path('app/private'))),
```

**Paso 4:** Limpiar caché de configuración:

```cmd
php artisan config:clear
php artisan cache:clear
```

### 9.2 Iniciar servidor PHP auxiliar

**Opción A - Manual (una vez):**

```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
scripts\start-server.bat
```

**Opción B - Automático al iniciar Windows:**

1. Crear acceso directo a `scripts\start-server.bat`
2. Pegarlo en `shell:startup` (Ejecutar > `shell:startup`)

**Opción C - Tarea Programada:**

Usar el archivo `scripts/TAKS.xml` como plantilla para crear una tarea en el Programador de Tareas de Windows que ejecute `start-server.bat` al iniciar sesión.

### 9.3 Verificar funcionamiento

**Prueba 1 - Servidor auxiliar:**

Abrir en el navegador del servidor:
```
http://127.0.0.1:8970/folder.php?path=.
```
Debería abrirse el Explorador de Windows en la carpeta raíz del storage.

**Prueba 2 - Diagnóstico completo:**

```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
php tmp_debug.php
```

Salida esperada:
```
SHELL_API_URL: http://127.0.0.1:8970
STORAGE_LOCAL_PATH: C:/inetpub/wwwroot/gestor-archivos/storage/app/private
Servidor PHP: HTTP 200 ✅
Archivo existe en Storage: SI ✅
exec_url: http://127.0.0.1:8970/folder.php?path=... ✅
```

---

## 10. Solución de Problemas

### 10.1 El botón "Ver en carpeta" no se muestra

**Causa:** `record_folder_url()` retorna `null`

**Diagnóstico:**
```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
php artisan config:clear
php artisan cache:clear
php tmp_debug.php
```

**Posibles causas:**
- Archivo físico no existe en `storage/app/private/`
- Variables de entorno no están definidas o no se cargan
- Caché de configuración no limpiada después de cambios en `.env`

### 10.2 El botón "Ver en carpeta" aparece pero no pasa nada

**Causa:** El servidor PHP auxiliar no está corriendo

**Solución:**
```cmd
tasklist | findstr php
```
Si no aparece `php.exe`, iniciar el servidor:
```cmd
scripts\start-server.bat
```

### 10.3 El botón "Abrir archivo" no funciona (pestaña en blanco)

**Causa:** El archivo no se encuentra en Storage

**Diagnóstico:**
```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
php artisan tinker
>>> \Illuminate\Support\Facades\Storage::exists('Equipos/.../archivo.pdf');
// Debe retornar true
```

**Posibles causas:**
- El path en la BD no coincide con la ubicación física
- El disco local está mal configurado en `config/filesystems.php`

### 10.4 Error "path() helper error: file is missing"

**Causa:** La función `path()` verifica que el archivo exista en Storage y no lo encuentra.

**Solución:** Verificar que el archivo físico esté en `storage/app/private/` y que el path en la BD coincida exactamente.

### 10.5 Las variables de entorno no se cargan

**Solución:**
```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
php artisan config:clear
```

Si el problema persiste, verificar permisos del directorio `bootstrap/cache/`:
```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
rmdir bootstrap\cache\.gitignore
echo * > bootstrap\cache\.gitignore
echo !.gitignore >> bootstrap\cache\.gitignore
php artisan config:clear
```

---

## 11. Mantenimiento

### 11.1 Después de cada `git pull`

Siempre ejecutar:
```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
php artisan config:clear
php artisan cache:clear
```

### 11.2 Verificar servidor auxiliar periódicamente

```cmd
tasklist | findstr php
```
Debe mostrar al menos un proceso `php.exe`. Si no está, ejecutar `start-server.bat`.

### 11.3 Verificar storage

Asegurarse de que los archivos subidos a través del sistema se almacenen correctamente en:
```
C:\inetpub\wwwroot\gestor-archivos\storage\app\private\...
```

### 11.4 Recomendaciones para producción

1. **Configurar el servidor auxiliar como servicio de Windows** para que inicie automáticamente
2. **Monitorear el puerto 8970** con un chequeo periódico
3. **Mantener los logs de Laravel** visibles para detectar errores de `path()`
4. **No almacenar en caché la configuración** en entornos donde el `.env` pueda cambiar

---

## Anexo: Commit de referencia

La configuración actual funciona correctamente a partir del commit:

```
d20014a fix: corregir root del disco local para que use STORAGE_LOCAL_PATH
```

Este commit corrige la línea en `config/filesystems.php`:
```diff
- 'root' => env('STORAGE_ROOT', storage_path('app/private')),
+ 'root' => env('STORAGE_LOCAL_PATH', env('STORAGE_ROOT', storage_path('app/private'))),
```

---

*Documento generado para la configuración de acciones "Ver en carpeta" y "Abrir archivo" en Maquindus.*
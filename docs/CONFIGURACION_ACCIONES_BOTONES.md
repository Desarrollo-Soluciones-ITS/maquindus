# Configuración de Acciones: "Ver en Carpeta" y "Abrir Archivo"

> **Fecha:** Julio 2026
> **Versión:** Commit `8d3755b` (rama `version-servidor-local`)
> **Sistema:** Maquindus - Gestor de Archivos

---

## 📋 Índice

1. [Descripción General](#1-desripción-general)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Requisitos Previos](#3-requisitos-previos)
4. [Configuración del Archivo `.env`](#4-configuración-del-archivo-env)
5. [Configuración del Disco Local (`config/filesystems.php`)](#5-configuración-del-disco-local-configfilesystemsphp)
6. [Servidor PHP Auxiliar](#6-servidor-php-auxiliar)
7. [Protocolo Personalizado `gestor://` (para LAN)](#7-protocolo-personalizado-gestor-para-lan)
8. [Archivos Clave del Sistema](#8-archivos-clave-del-sistema)
9. [Flujo de Funcionamiento](#9-flujo-de-funcionamiento)
10. [Instalación y Puesta en Marcha](#10-instalación-y-puesta-en-marcha)
11. [Despliegue en PCs Cliente (LAN)](#11-despliegue-en-pcs-cliente-lan)
12. [Solución de Problemas](#12-solución-de-problemas)
13. [Mantenimiento](#13-mantenimiento)

---

## 1. Descripción General

El sistema Maquindus tiene dos acciones principales para interactuar con los archivos almacenados:

| Acción | Botón | Función |
|--------|-------|---------|
| **Ver en carpeta** | `📂` | Abre el Explorador de Windows en la ubicación del archivo seleccionándolo |
| **Abrir archivo** | `👁️` | Abre el archivo en una nueva pestaña del navegador para vista previa |

### Comportamiento en diferentes entornos:

| Entorno | Mecanismo | Puerto / Protocolo |
|---------|-----------|-------------------|
| **Servidor local** (accediendo desde el servidor) | `OpenFolderAction` → HTTP → `folder.php` → `explorer /select,"ruta"` | `127.0.0.1:8970` |
| **Cliente LAN** (accediendo desde otra PC en la red) | `gestor_net_url()` → `gestor://select?path=\\UNC\...` → `gestor_handler.ps1` → `explorer /select,"ruta"` | `gestor://` protocolo |

---

## 2. Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          NAVEGADOR WEB                                  │
│          (Usuario en el servidor o en PC de la LAN)                     │
└──┬────────────────────────────────────────────────────────────────┬─────┘
   │                                                                │
   ▼ (Servidor local)                                              ▼ (Cliente LAN)
┌─────────────────────────────────────┐           ┌─────────────────────────────────────┐
│   LARAVEL (Backend PHP/Filament)    │           │   LARAVEL (Backend PHP/Filament)    │
│                                     │           │                                     │
│  OpenFolderAction                   │           │  gestor_net_url()                   │
│  → record_folder_url()              │           │  → path($path, base: false)         │
│  → exec_url()                       │           │  → RUTA_RELATIVA                    │
│  → path($path, base: true)          │           │  → STORAGE_NETWORK_PATH + relativo  │
│  → RUTA_ABSOLUTA                    │           │  → gestor://select?path=\\UNC\...   │
│  → http://127.0.0.1:8970/folder.php │           └────────────┬────────────────────────┘
└──────────────┬──────────────────────┘                        │
               │                                               ▼ (Redirección del navegador)
               ▼ HTTP GET                                     ┌─────────────────────────────┐
┌──────────────────────────────┐              │  CLIENTE WINDOWS (LAN)                │
│  SERVIDOR PHP AUXILIAR       │              │  → protocolo gestor:// registrado     │
│  (Puerto 8970)               │              │  → PowerShell ejecuta gestor_handler  │
│                              │              │  → explorer /select,"ruta"            │
│  scripts/folder.php          │              └─────────────────────────────────────────┘
│  → exec("explorer /select,") │                           │
└──────────────┬───────────────┘                           ▼
               │                               ┌─────────────────────────────┐
               ▼                               │  EXPLORADOR DE WINDOWS     │
┌──────────────────────────────┐              │  (Se abre con el archivo)  │
│  EXPLORADOR DE WINDOWS       │              └─────────────────────────────┘
│  (Se abre con el archivo)    │
└──────────────────────────────┘
```

---

## 3. Requisitos Previos

- **Sistema Operativo:** Windows (Server 2012 R2 o superior)
- **PHP:** 8.1, 8.2 o 8.3
- **Laravel:** 11+
- **Servidor Web:** IIS con PHP habilitado (o Laragon en desarrollo)
- **Storage:** Disco local con los archivos físicos almacenados
- **Red (LAN):** Recurso compartido SMB accesible desde PCs cliente (`\\servidor\private`)

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

# URL del servidor PHP auxiliar para abrir carpetas/archivos (SOLO SERVIDOR LOCAL)
SHELL_API_URL=http://127.0.0.1:8970

# Ruta LOCAL física donde están los archivos (para el disco de Laravel)
STORAGE_LOCAL_PATH=C:/inetpub/wwwroot/gestor-archivos/storage/app/private

# Ruta UNC (red) para acceso desde PCs en la LAN (para protocolo gestor://)
STORAGE_NETWORK_PATH=\\\\192.168.0.4\\private

# Recurso compartido para el protocolo gestor:// (debe coincidir con STORAGE_NETWORK_PATH)
NETWORK_SHARE_ROOT=\\\\192.168.0.4\\private
```

> **⚠️ IMPORTANTE:** 
> - Las rutas LOCALES usan `/` (slash normal)
> - Las rutas UNC (red) usan `\\` (doble backslash escapado)
> - `STORAGE_NETWORK_PATH` debe coincidir con el recurso compartido SMB en Windows

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

Es un servidor PHP independiente que corre en `http://127.0.0.1:8970` y se encarga de ejecutar comandos del sistema operativo Windows para abrir el Explorador de Archivos **SOLO en el servidor local**.

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

## 7. Protocolo Personalizado `gestor://` (para LAN)

### 7.1 ¿Qué es?

Es un protocolo personalizado de Windows que permite a los navegadores web ejecutar un script local en la PC del usuario para abrir el Explorador de Archivos con la ruta UNC del servidor.

### 7.2 Archivos del protocolo

| Archivo | Ruta | Función |
|---------|------|---------|
| `gestor_handler.ps1` | `scripts/gestor_handler.ps1` | Script PowerShell que ejecuta `explorer /select,"ruta"` |
| `open_in_explorer.reg` | `scripts/open_in_explorer.reg` | Registro del protocolo en Windows |
| `deploy_user.bat` | `scripts/deploy_user.bat` | Instalador para PCs cliente |

### 7.3 `scripts/gestor_handler.ps1`

```powershell
param(
    [string]$action = "select",   # "select" o "open"
    [string]$path = ""             # Ruta UNC completa
)

$path = [System.Uri]::UnescapeDataString($path)
$path = $path -replace '[/]', '\'

# Usar Shell.Application COM object
$shell = New-Object -ComObject "Shell.Application"

switch ($action.ToLower()) {
    "select" {
        # Abrir carpeta y seleccionar el archivo
        $shell.Open([System.IO.Path]::GetDirectoryName($path))
        $folder = $shell.NameSpace([System.IO.Path]::GetDirectoryName($path))
        $item = $folder.ParseName([System.IO.Path]::GetFileName($path))
        if ($item) { $item.InvokeVerb("properties") }
    }
}
# Fallback a cmd /c explorer si falla COM
```

### 7.4 `scripts/open_in_explorer.reg`

```registry
[HKEY_CLASSES_ROOT\gestor]
@="URL:Gestor de Archivos Protocol"
"URL Protocol"=""

[HKEY_CLASSES_ROOT\gestor\shell\open\command]
@="powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File \"%USERPROFILE%\\scripts\\gestor_handler.ps1\" -action \"select\" -path \"%1\""
```

### 7.5 `scripts/deploy_user.bat`

Instalador que:
1. Copia `gestor_handler.ps1` a `%USERPROFILE%\scripts\`
2. Registra el protocolo `gestor://` en el Registro de Windows
3. Verifica la instalación

---

## 8. Archivos Clave del Sistema

### 8.1 `app/Filament/Actions/Documents/OpenFolderAction.php`

Acción "Ver en carpeta" (funciona SOLO en servidor local):

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
                    Http::timeout(5)->get($url);  // Llama a 127.0.0.1:8970
                }
            });
    }
}
```

### 8.2 `app/Filament/Actions/Documents/PreviewAction.php`

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

### 8.3 `app/helpers.php` - Funciones clave

```php
// Para servidor local: genera URL hacia el servidor PHP auxiliar
function exec_url(string $filepath, string $endpoint): ?string
{
    $base = env('SHELL_API_URL', 'http://127.0.0.1:8970');
    try {
        $replaced = path($filepath, base: true); // Ruta absoluta local
    } catch (\Throwable) {
        return null;
    }
    $path = urlencode($replaced);
    return "$base/$endpoint.php?path=$path";
}

// Para clientes LAN: genera URL del protocolo gestor:// con ruta UNC
function gestor_net_url(string $filepath, string $action = 'select'): ?string
{
    $base = env('STORAGE_NETWORK_PATH');
    if (!$base) return null;

    try {
        $relativePath = path($filepath, base: false); // Ruta relativa
    } catch (\Throwable) {
        return null;
    }
    $relativePath = ltrim($relativePath, '\\/');

    $fullPath = rtrim($base, '\\/') . '\\' . str_replace('/', '\\', $relativePath);

    return "gestor://$action?path=" . urlencode($fullPath);
}

// Obtiene URL para "Ver en carpeta" según el tipo de registro
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
    
    if ($asFolder && Storage::directoryMissing($folder))
        throw new Error('path() helper error: directory is missing');
    if (!$asFolder && Storage::fileMissing($folder))
        throw new Error('path() helper error: file is missing');
    
    return str($base ? Storage::path($folder) : $folder)
        ->replace('/', DIRECTORY_SEPARATOR)
        ->replace('\\', DIRECTORY_SEPARATOR);
}
```

### 8.4 `app/Http/Controllers/NetworkFileController.php`

Controlador para redirigir a clientes LAN al protocolo `gestor://`:

```php
class NetworkFileController extends Controller
{
    public function openFolder(Request $request)
    {
        $file = File::findOrFail($request->file);
        $url = gestor_net_url($file->path, 'select');
        if (!$url) abort(400, 'No se puede generar la URL de red.');
        return view('network.open-folder', compact('file', 'url'));
    }

    public function openFile(Request $request)
    {
        $file = File::findOrFail($request->file);
        $url = gestor_net_url($file->path, 'open');
        if (!$url) abort(400, 'No se puede generar la URL de red.');
        return view('network.open-file', compact('file', 'url'));
    }
}
```

### 8.5 `routes/web.php`

```php
Route::middleware(['permission.any:documents.show_file,files.show_file'])
    ->get('/files/{file}/preview', PreviewFileController::class)
    ->name('files.preview');

// Rutas para clientes LAN (usan protocolo gestor://)
Route::prefix('network')->middleware(['permission.any:documents.show_file,files.show_file'])->group(function () {
    Route::get('/folder/{file}', [NetworkFileController::class, 'openFolder'])->name('network.folder');
    Route::get('/file/{file}', [NetworkFileController::class, 'openFile'])->name('network.file');
});
```

---

## 9. Flujo de Funcionamiento

### 9.1 "Ver en carpeta" - En el servidor local

```
Usuario en el servidor hace clic en "📂 Ver en carpeta"
        │
        ▼
OpenFolderAction::action($record)
        │
        ▼
record_folder_url($record) → exec_url($filepath, 'folder')
        │
        ├── path($filepath, base: true) → C:\...\storage\app\private\Equipos\...
        │
        └── http://127.0.0.1:8970/folder.php?path=C%3A%5C...%5Carchivo.pdf
                │
                ▼
        Http::timeout(5)->get($url)  →  Servidor PHP auxiliar
                │
                ▼
        exec('cmd /c explorer /select,"C:\...\archivo.pdf"')
                │
                ▼
        ¡Se abre el Explorador con el archivo seleccionado!
```

### 9.2 "Ver en carpeta" - Desde un cliente LAN

```
Usuario en PC de la LAN hace clic en "📂 Ver en carpeta"
        │
        ▼
(La página web redirige al protocolo gestor://)
        │
        ▼
gestor://select?path=%5C%5C192.168.0.4%5Cprivate%5CEquipos%5C...%5Carchivo.pdf
        │
        ▼
Windows detecta el protocolo gestor:// registrado
        │
        ▼
Ejecuta: powershell.exe -File "%USERPROFILE%\scripts\gestor_handler.ps1"
         -action "select" -path "gestor://select?path=..."
        │
        ▼
gestor_handler.ps1:
  1. Decodifica la URL
  2. Extrae la ruta: \\192.168.0.4\private\Equipos\...\archivo.pdf
  3. Crea objeto COM Shell.Application
  4. Abre la carpeta \\192.168.0.4\private\Equipos\...
  5. Selecciona el archivo
        │
        ▼
¡Se abre el Explorador con el archivo seleccionado!
```

### 9.3 "Abrir archivo"

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

## 10. Instalación y Puesta en Marcha

### 10.1 Configuración inicial (Servidor)

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

### 10.2 Iniciar servidor PHP auxiliar (Servidor)

**Opción A - Manual (una vez):**

```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
scripts\start-server.bat
```

**Opción B - Con Tarea Programada:**

Usar `scripts/task.xml` para importar en el Programador de Tareas de Windows.

**Opción C - Al iniciar sesión:**

Crear acceso directo a `scripts\start-server.bat` en `shell:startup`.

### 10.3 Compartir la carpeta en red (LAN)

Para que clientes LAN puedan acceder a los archivos, la carpeta `storage/app/private` debe estar compartida en red:

1. Abrir **Administración de equipos** > **Recursos compartidos**
2. Compartir `C:\inetpub\wwwroot\gestor-archivos\storage\app\private` como `private`
3. Asignar permisos de lectura a los usuarios de red

### 10.4 Verificar funcionamiento (Servidor)

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

## 11. Despliegue en PCs Cliente (LAN)

Para que los usuarios de la LAN puedan usar "Ver en carpeta" desde sus propias PCs:

### 11.1 Requisitos en la PC cliente

- Windows 7/10/11 o Windows Server
- PowerShell 5.0+
- Acceso a la carpeta compartida `\\servidor\private`

### 11.2 Instalación automática

**Paso 1:** Ejecutar como **Administrador** en la PC cliente:

```cmd
\\servidor\gestor-archivos\scripts\deploy_user.bat
```

O copiar los archivos primero y ejecutar localmente:

```cmd
copy \\servidor\gestor-archivos\scripts\deploy_user.bat C:\temp\
copy \\servidor\gestor-archivos\scripts\gestor_handler.ps1 C:\temp\
cd /d C:\temp
deploy_user.bat
```

### 11.3 Instalación manual

Si no se puede ejecutar el instalador:

**1.** Copiar `gestor_handler.ps1` a `%USERPROFILE%\scripts\`

**2.** Ejecutar `scripts/open_in_explorer.reg` (doble clic, confirmar)

**3.** Verificar que el protocolo esté registrado:
```cmd
reg query HKCR\gestor
```

### 11.4 Desinstalación

```cmd
reg delete HKCR\gestor /f
del %USERPROFILE%\scripts\gestor_handler.ps1
```

---

## 12. Solución de Problemas

### 12.1 El botón "Ver en carpeta" no se muestra

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

### 12.2 El botón "Ver en carpeta" aparece pero no pasa nada (Servidor)

**Causa:** El servidor PHP auxiliar no está corriendo

**Solución:**
```cmd
tasklist | findstr php
```
Si no aparece `php.exe`, iniciar el servidor:
```cmd
scripts\start-server.bat
```

### 12.3 El botón "Ver en carpeta" no funciona (Cliente LAN)

**Causa:** El protocolo `gestor://` no está registrado

**Solución:** Ejecutar como Administrador:
```cmd
regedit /s \\servidor\gestor-archivos\scripts\open_in_explorer.reg
```

### 12.4 La ruta UNC no es accesible

**Solución:** Verificar que la carpeta esté compartida y que el usuario tenga permisos:
```cmd
net use \\servidor\private
```

### 12.5 El botón "Abrir archivo" no funciona (pestaña en blanco)

**Causa:** El archivo no se encuentra en Storage

**Diagnóstico:**
```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
php artisan tinker
>>> \Illuminate\Support\Facades\Storage::exists('Equipos/.../archivo.pdf');
```

### 12.6 Error "path() helper error: file is missing"

**Causa:** La función `path()` verifica que el archivo exista en Storage y no lo encuentra.

**Solución:** Verificar que el archivo físico esté en `storage/app/private/` y que el path en la BD coincida exactamente.

### 12.7 Las variables de entorno no se cargan

**Solución:**
```cmd
php artisan config:clear
```
Si el problema persiste, verificar permisos del directorio `bootstrap/cache/`.

---

## 13. Mantenimiento

### 13.1 Después de cada `git pull`

Siempre ejecutar:
```cmd
cd /d C:\inetpub\wwwroot\gestor-archivos
php artisan config:clear
php artisan cache:clear
```

### 13.2 Verificar servidor auxiliar periódicamente

```cmd
tasklist | findstr php
```
Debe mostrar al menos un proceso `php.exe`. Si no está, ejecutar `start-server.bat`.

### 13.3 Verificar storage

Asegurarse de que los archivos subidos a través del sistema se almacenen correctamente en:
```
C:\inetpub\wwwroot\gestor-archivos\storage\app\private\...
```

### 13.4 Recomendaciones para producción

1. **Configurar el servidor auxiliar como tarea programada** (`scripts/task.xml`) para que inicie automáticamente al iniciar sesión
2. **Monitorear el puerto 8970** con un chequeo periódico
3. **Mantener los logs de Laravel** visibles para detectar errores de `path()`
4. **Compartir la carpeta `private` en red** con permisos de solo lectura para usuarios LAN
5. **Para nuevas PCs cliente**, distribuir `deploy_user.bat` a través de la red

---

## 📦 Resumen de archivos del proyecto

### Archivos NUEVOS para soporte LAN:

| Archivo | Función |
|---------|---------|
| `scripts/gestor_handler.ps1` | Manejador del protocolo gestor:// en PowerShell |
| `scripts/open_in_explorer.reg` | Registro del protocolo en Windows |
| `scripts/deploy_user.bat` | Instalador para PCs cliente |
| `app/Http/Controllers/NetworkFileController.php` | Controlador para redirigir a protocolo gestor:// |
| `resources/views/network/open-folder.blade.php` | Vista de transición para abrir carpeta |
| `resources/views/network/open-file.blade.php` | Vista de transición para abrir archivo |

### Archivos MODIFICADOS:

| Archivo | Cambio |
|---------|--------|
| `app/helpers.php` | Se agregó función `gestor_net_url()` |
| `routes/web.php` | Se agregaron rutas `network.*` |
| `scripts/task.xml` | Se actualizó para usar start-server.bat directamente |

### Archivos que NO se modificaron (funcionan como antes):

| Archivo | Función |
|---------|---------|
| `app/Filament/Actions/Documents/OpenFolderAction.php` | Acción "Ver en carpeta" (servidor local) |
| `app/Filament/Actions/Documents/PreviewAction.php` | Acción "Abrir archivo" |
| `app/Filament/Resources/Documents/Tables/DocumentsTable.php` | Tabla con acciones |
| `app/Filament/Resources/Documents/RelationManagers/FilesRelationManager.php` | RM con acciones |
| `scripts/folder.php` | Script PHP auxiliar |
| `scripts/preview.php` | Script PHP auxiliar |
| `scripts/start-server.bat` | Iniciador del servidor auxiliar |

---

*Documento generado para la configuración de acciones "Ver en carpeta" y "Abrir archivo" en Maquindus.*
*Versión: 2.0 - Soporte para servidor local + clientes LAN*
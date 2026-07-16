# Funcionalidad "Ver en Carpeta" — Documentación Completa

## Índice

1. [Introducción](#1-introducción)
2. [Arquitectura General](#2-arquitectura-general)
3. [Modos de Operación](#3-modos-de-operación)
4. [MODO SERVIDOR LOCAL](#4-modo-servidor-local)
5. [MODO LAN (Clientes en Red)](#5-modo-lan-clientes-en-red)
6. [Archivos del Sistema](#6-archivos-del-sistema)
7. [Variables de Entorno (.env)](#7-variables-de-entorno-env)
8. [Instalación en PC Cliente](#8-instalación-en-pc-cliente)
9. [Solución de Problemas](#9-solución-de-problemas)
10. [Historial de Cambios](#10-historial-de-cambios)

---

## 1. Introducción

La funcionalidad **"Ver en carpeta"** permite a los usuarios abrir el Explorador de Windows en la ubicación exacta donde está almacenado un archivo/documento dentro del gestor documental. Soporta dos escenarios:

- **Servidor local:** El administrador abre archivos directamente en el servidor donde corre Laravel.
- **Clientes LAN:** Usuarios en la red local abren archivos desde sus propias PCs, accediendo a la carpeta compartida vía SMB/CIFS.

El sistema detecta automáticamente el modo según la configuración en el archivo `.env`.

---

## 2. Arquitectura General

```
┌─────────────────────────────────────────────────────────┐
│                    USUARIO                              │
│  (Navegador Web - Chrome/Edge/Firefox)                 │
└─────────────────┬───────────────────────────────────────┘
                  │ Clic en "Ver en carpeta"
                  ▼
┌─────────────────────────────────────────────────────────┐
│            OpenFolderAction.php                         │
│  (Filament Action - App/Laravel)                       │
│                                                         │
│  ┌─────────────┐    ┌──────────────┐                   │
│  │ ¿Hay        │    │ ¿No hay      │                   │
│  │ STORAGE_    │    │ STORAGE_     │                   │
│  │ NETWORK_PATH│    │ NETWORK_PATH?│                   │
│  └──────┬──────┘    └──────┬───────┘                   │
│         │ LAN              │ Servidor                   │
│         ▼                  ▼                            │
│  window.location     Http::get()                       │
│  .href =            → PHP auxiliar                     │
│  'gestor://...'      127.0.0.1:8970                    │
└─────────────────────────────────────────────────────────┘
```

### 2.1. Componentes Clave

| Componente | Tecnología | Rol |
|---|---|---|
| `OpenFolderAction.php` | PHP / Filament | Acción del botón en la interfaz |
| `gestor_net_url()` | PHP (Helper) | Genera URL con protocolo `gestor://` |
| `gestor_handler.ps1` | PowerShell | Recibe y procesa el protocolo `gestor://` |
| `folder.php` | PHP | Servidor auxiliar local (puerto 8970) |

---

## 3. Modos de Operación

El sistema opera en **2 modos** mutuamente excluyentes, determinados por la variable `STORAGE_NETWORK_PATH`:

| `STORAGE_NETWORK_PATH` | Modo activo | Método |
|---|---|---|
| **Definida** (ej: `\\192.168.0.4\private`) | 🌐 LAN | Protocolo `gestor://` → PowerShell → Explorer |
| **Vacía / no definida** | 🖥️ Servidor local | `Http::get()` → `folder.php` (puerto 8970) → Explorer |

**Detección automática en código (`OpenFolderAction.php`):**

```php
// app/Filament/Actions/Documents/OpenFolderAction.php
public static function make(): Action
{
    return Action::make('folder')
        ->label('Ver en carpeta')
        ->icon(Heroicon::FolderOpen)
        ->hidden(function (Model $record): bool {
            $file = static::resolveFile($record);
            return blank($file?->path);
        })
        ->action(function (Model $record, $livewire) {
            $file = static::resolveFile($record);
            if (!$file || blank($file->path)) return;

            $gestorUrl = record_folder_gestor_url($record);
            if ($gestorUrl) {
                $livewire->js("window.location.href = '{$gestorUrl}'");
            }
        });
}
```

**Nota:** El método `record_folder_gestor_url()` devuelve `null` si `STORAGE_NETWORK_PATH` no está definida, por lo que `window.location.href` no se ejecuta y la acción no tiene efecto. Sin embargo, el botón se oculta con `hidden()` que usa `record_folder_url()` (para servidor local) — esto es correcto porque en modo LAN la visibilidad también debe verificar que el archivo existe.

---

## 4. MODO SERVIDOR LOCAL

### 4.1. Descripción

Cuando `STORAGE_NETWORK_PATH` **NO** está definida en el `.env`, el botón "Ver en carpeta" ejecuta una petición HTTP desde **el propio servidor** hacia un servidor PHP auxiliar que corre en `http://127.0.0.1:8970`. Este servidor PHP ejecuta `explorer.exe` con la ruta absoluta del archivo.

### 4.2. Flujo Completo

```
1. Usuario hace clic en "Ver en carpeta"
       │
2. OpenFolderAction ejecuta el callback de acción.
       │
3. Se obtiene la URL de la shell API:
       │
   record_folder_url($record)
       │
   ┌─ ¿$record es File?  → $record->path
   ├─ ¿$record es Document? → $record->current->path
   └─ ¿tiene método documents()? → último documento → current->path
       │
4. exec_url($path, 'folder'):
       │
   SHELL_API_URL/folder.php?path=<ruta_absoluta_codificada>
       │
   Ejemplo:
   http://127.0.0.1:8970/folder.php?path=C%3A%5Cinetpub%5C...%5Carchivo.pdf
       │
5. Livewire ejecuta en el navegador: fetch('http://127.0.0.1:8970/...')
       │
6. folder.php recibe la petición:
   - Decodifica el path
   - Construye ruta absoluta + raíz del storage
   - Ejecuta: cmd /c explorer /select, "ruta"
       │
7. ¡Explorer se abre en el servidor!
```

### 4.3. Servidor Auxiliar PHP

El servidor PHP auxiliar corre en `http://127.0.0.1:8970` y es mantenido por:

- **Script:** `C:\inetpub\wwwroot\gestor-archivos\scripts\start-server.bat`
- **Tarea programada:** `scripts/task.xml` (se importa en el Programador de Tareas de Windows)

**`start-server.bat`:**
```batch
@echo off
set SCRIPTS_DIR=C:\inetpub\wwwroot\gestor-archivos\scripts
set SHELL_SHARE_ROOT=C:\inetpub\wwwroot\gestor-archivos\storage\app\private
REM Busca PHP y lo inicia en puerto 8970
start /B "" "%PHP_PATH%" -S 127.0.0.1:8970 -t "%SCRIPTS_DIR%"
```

**`folder.php`:**
```php
<?php
$input = $_GET['path'] ?? null;
if (!$input) { http_response_code(400); exit; }

$input = trim(urldecode($input));
$input = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $input);

// Si es ruta relativa, anteponer raíz
if (!preg_match('/^(?:[A-Za-z]:\\\\|\\\\\\\\)/', $input)) {
    $root = getenv('SHELL_SHARE_ROOT') ?: 'C:\\inetpub\\wwwroot\\gestor-archivos\\storage\\app\\private';
    $input = $root . DIRECTORY_SEPARATOR . ltrim($input, DIRECTORY_SEPARATOR);
}

$arg = escapeshellarg($input);
if (is_file($input)) {
    $cmd = 'cmd /c explorer /select,' . $arg;
} else {
    $cmd = 'cmd /c explorer ' . $arg;
}
exec($cmd);
```

### 4.4. Inicio Automático

Para que el servidor auxiliar se inicie automáticamente al encender el servidor:

1. Abrir **Programador de Tareas** de Windows
2. Importar `scripts\task.xml`
3. Asegurar que se ejecuta con la cuenta del usuario que inicia sesión

O manualmente:
```cmd
C:\inetpub\wwwroot\gestor-archivos\scripts\start-server.bat
```

---

## 5. MODO LAN (Clientes en Red)

### 5.1. Descripción

Cuando `STORAGE_NETWORK_PATH` **SÍ** está definida, el botón "Ver en carpeta" utiliza el **protocolo personalizado `gestor://`** registrado en Windows. El navegador del cliente lanza este protocolo, que a su vez ejecuta un script de PowerShell que abre el Explorador de Windows con la ruta UNC del archivo.

**No requiere PHP en el cliente.** Solo PowerShell (viene instalado en Windows 10/11).

### 5.2. Flujo Completo

```
1. Usuario en PC Cliente hace clic en "Ver en carpeta"
       │
2. OpenFolderAction resuelve el archivo asociado al registro
       │
3. record_folder_gestor_url($record)
       │
   ┌─ ¿$record es File?       → $record->path
   ├─ ¿$record es Document?   → $record->current->path
   └─ ¿tiene documents()?     → último doc → current->path
       │
4. gestor_net_url($filepath, 'select')
       │
   - Obtiene la ruta relativa desde STORAGE_LOCAL_PATH
   - Obtiene STORAGE_NETWORK_PATH (ej: \\192.168.0.4\private)
   - Normaliza backslashes
   - Construye UNC: \\192.168.0.4\private\Equipos\...\archivo.pdf
   - Codifica con rawurlencode (espacios → %20, no +)
       │
   Resultado:
   gestor://select?path=%5C%5C192.168.0.4%5Cprivate%5C...
       │
5. Livewire ejecuta:
   window.location.href = 'gestor://select?path=%5C%5C...'
       │
6. Navegador muestra: "Abrir gestor://?"
       │
7. Al aceptar, Windows ejecuta:
   powershell.exe -File gestor_handler.ps1 -path "gestor://select?path=\\..."
       │
8. gestor_handler.ps1:
   - Extrae la ruta con IndexOf("path=")
   - Decodifica: %5C→\, %20→espacio, +→espacio, %28→(, %29→)
   - Obtiene carpeta contenedora (GetDirectoryName)
   - Shell.Application.Open(carpeta)
       │
9. ¡Explorer se abre en el PC del cliente!
```

### 5.3. Script gestor_handler.ps1

El script PowerShell es el corazón del modo LAN. Está diseñado para ser mínimo y robusto:

```powershell
param([string]$path = "")

# Extraer todo lo que viene despues de "path=" en la URL
$i = $path.IndexOf("path=")
if ($i -lt 0) { exit 1 }

$ruta = $path.Substring($i + 5)

# Decodificar URL
$ruta = $ruta -replace '%5C', '\'
$ruta = $ruta -replace '%5c', '\'
$ruta = $ruta -replace '%20', ' '
$ruta = $ruta -replace '%28', '('
$ruta = $ruta -replace '%29', ')'
$ruta = $ruta -replace '+', ' '
$ruta = $ruta -replace '[/]', '\'

# Obtener carpeta contenedora (GetDirectoryName funciona con UNC)
$carpeta = [System.IO.Path]::GetDirectoryName($ruta)

# Abrir carpeta (intenta /select, fallback a carpeta)
try { Start-Process explorer -ArgumentList "/select,`"$ruta`"" -ErrorAction Stop; exit 0 }
catch {
    try { Start-Process explorer -ArgumentList "`"$carpeta`"" -ErrorAction Stop; exit 0 }
    catch { exit 1 }
}
```

**Puntos clave del diseño:**
- **No usa regex** — usa `IndexOf()` simple, evita problemas con caracteres especiales
- **No usa `[System.Uri]::UnescapeDataString()`** — porque no decodifica `+` como espacio
- **Decodificación manual** — reemplaza cada secuencia `%XX` individualmente
- **`Shell.Application.Open()`** — es el único método que funciona correctamente con rutas UNC largas
- **Fallback a `explorer.exe`** — si Shell COM falla
- **Exit codes** — 0 éxito, 1 error

### 5.4. Función gestor_net_url()

```php
function gestor_net_url(string $filepath, string $action = 'select'): ?string
{
    $base = env('STORAGE_NETWORK_PATH');
    if (!$base) return null;

    try {
        $relativePath = path($filepath, base: false);
    } catch (\Throwable) {
        return null;
    }

    // Normalizar: / → \, eliminar separadores iniciales
    $relativePath = str_replace('/', '\\', $relativePath);
    $relativePath = ltrim($relativePath, '\\');

    // Normalizar base UNC
    $base = str_replace('/', '\\', $base);
    $base = preg_replace('/\\\\+/', '\\', $base);  // colapsar \\\\... a \
    $base = ltrim($base, '\\');                     // quitar \ iniciales
    $base = '\\\\' . $base;                         // agregar exactamente \\

    $fullPath = $base . '\\' . $relativePath;

    return "gestor://$action?path=" . rawurlencode($fullPath);
}
```

**Nota importante:** Se usa `rawurlencode()` en lugar de `urlencode()` porque:
- `urlencode()` codifica espacios como `+`
- `rawurlencode()` codifica espacios como `%20`
- PowerShell `[System.Uri]::UnescapeDataString()` NO convierte `+` a espacio
- PowerShell `-replace` manual sí convierte `%20` y `+` a espacio

### 5.5. El protocolo gestor://

El protocolo `gestor://` es un protocolo personalizado de Windows registrado en el Registro. Cuando el navegador encuentra un enlace con este protocolo, delega la ejecución a un comando registrado.

**Registro en Windows (`scripts/open_in_explorer.reg`):**

```reg
Windows Registry Editor Version 5.00

[HKEY_CLASSES_ROOT\gestor]
@="URL:Gestor de Archivos Protocol"
"URL Protocol"=""

[HKEY_CLASSES_ROOT\gestor\shell]
@="open"

[HKEY_CLASSES_ROOT\gestor\shell\open]
@="Abrir en el explorador de archivos"

[HKEY_CLASSES_ROOT\gestor\shell\open\command]
@="powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File \"%USERPROFILE%\\scripts\\gestor_handler.ps1\" -path \"%1\""
```

**Formato de la URL:**
```
gestor://select?path=\\192.168.0.4\private\Equipos\...
                         ^^^^^^^^^^^^^^^^^^^^^^^^
                         STORAGE_NETWORK_PATH + ruta relativa
```

**Formato codificado (en la práctica):**
```
gestor://select/?path=%5C%5C192.168.0.4%5Cprivate%5C...
                ^
         Chrome/Edge añade / antes de ?
```

### 5.6. ¿Por qué Chrome/Edge añade un `/` antes de `?`?

Cuando el navegador procesa una URL con protocolo personalizado como `gestor://select?path=...`, automáticamente normaliza la URL agregando un `/` antes del `?`, resultando en `gestor://select/?path=...`. 

El script `gestor_handler.ps1` no necesita preocuparse por esto porque usa `IndexOf("path=")` que encuentra el texto independientemente de cómo esté estructurada la URL.

**Ejemplo de URLs que funcionan:**

```
gestor://select?path=\\...           ✅  Formato original
gestor://select/?path=\\...          ✅  Chrome/Edge normalizado
gestor://select/?path=%5C%5C...      ✅  Codificado
```

---

## 6. Archivos del Sistema

### 6.1. Archivos del Proyecto Laravel

| Ruta | Propósito | Modo |
|---|---|---|
| `app/Filament/Actions/Documents/OpenFolderAction.php` | Acción del botón | Ambos |
| `app/helpers.php` | Funciones helper (`gestor_net_url`, `record_folder_url`, `record_folder_gestor_url`) | Ambos |
| `app/Http/Controllers/NetworkFileController.php` | Controlador para rutas LAN | LAN |
| `routes/web.php` | Definición de rutas (`network.folder`, `network.file`) | Ambos |
| `config/filesystems.php` | Configuración de discos de almacenamiento | Ambos |

### 6.2. Archivos de Scripts (Servidor y Cliente)

| Ruta | Propósito | ¿Dónde se usa? |
|---|---|---|
| `scripts/folder.php` | Servidor PHP auxiliar para abrir carpetas | Servidor (puerto 8970) |
| `scripts/client_folder.php` | Versión para cliente LAN (con soporte `gestor=`) | Cliente (si tiene PHP) |
| `scripts/preview.php` | Servidor auxiliar para previsualizar archivos | Servidor (puerto 8970) |
| `scripts/start-server.bat` | Inicia servidor PHP auxiliar en el servidor | Servidor |
| `scripts/start-user-server.bat` | Inicia servidor PHP auxiliar en cliente | Cliente (si tiene PHP) |
| `scripts/gestor_handler.ps1` | Manejador del protocolo `gestor://` | Cliente (PowerShell) |
| `scripts/deploy_user.bat` | Instalador para clientes LAN | Cliente |
| `scripts/open_in_explorer.reg` | Registro del protocolo `gestor://` en Windows | Cliente |
| `scripts/task.xml` | Tarea programada para iniciar servidor auxiliar | Servidor |
| `scripts/fix_server.bat` | Repara `bootstrap/cache` y resuelve merges | Servidor |

### 6.3. Vistas Blade

| Ruta | Propósito |
|---|---|
| `resources/views/network/open-folder.blade.php` | Página HTML que lanza `gestor://` (fallback, actualmente no se usa directamente) |
| `resources/views/network/open-file.blade.php` | Página HTML para abrir archivos (fallback) |

---

## 7. Variables de Entorno (.env)

### 7.1. Variables relacionadas con "Ver en carpeta"

```ini
# ============================================
# CONFIGURACION VER EN CARPETA
# ============================================

# URL del servidor PHP auxiliar local (puerto 8970)
# Se usa en ambos modos (servidor y LAN para folder.php)
SHELL_API_URL=http://127.0.0.1:8970

# Ruta fisica del storage en el servidor
# Usada por path() helper para convertir rutas relativas a absolutas
STORAGE_LOCAL_PATH=C:/inetpub/wwwroot/gestor-archivos/storage/app/private

# Ruta UNC de la carpeta compartida via SMB
# SI esta definida → Activa MODO LAN
# SI NO esta definida → Activa MODO SERVIDOR LOCAL
# Formato: \\\\servidor\\recurso
STORAGE_NETWORK_PATH=\\\\192.168.0.4\\private

# Alias (debe coincidir con STORAGE_NETWORK_PATH)
NETWORK_SHARE_ROOT=\\\\192.168.0.4\\private
```

### 7.2. Explicación de `STORAGE_NETWORK_PATH`

El valor en el `.env` se escribe con **doble escape** porque el parser de dotenv interpreta las barras invertidas:

| En `.env` | Valor real |
|---|---|
| `\\\\192.168.0.4\\\\private` | `\\192.168.0.4\private` |

Pero debido a cómo PHP procesa las variables de entorno, el valor puede llegar con escapes adicionales. La función `gestor_net_url()` normaliza esto:

```php
$base = preg_replace('/\\\\+/', '\\', $base);  // \\\\... → \
$base = ltrim($base, '\\');                     // quita \ iniciales
$base = '\\\\' . $base;                         // agrega exactamente 2 \\
```

### 7.3. Activar/Desactivar Modo LAN

**Para activar modo LAN** (descomentar o definir `STORAGE_NETWORK_PATH`):
```ini
STORAGE_NETWORK_PATH=\\\\192.168.0.4\\private
```

**Para desactivar modo LAN** (servidor local):
```ini
# STORAGE_NETWORK_PATH=\\\\192.168.0.4\\private
```
O simplemente dejar la variable vacía o comentada.

---

## 8. Instalación en PC Cliente

### 8.1. Requisitos

- Windows 10 u 11
- PowerShell 5.0+ (viene instalado)
- **No requiere PHP**
- Acceso de red a `\\192.168.0.4\private` (recurso compartido SMB)
- Permisos de **Administrador** para la instalación

### 8.2. Instalación

Desde el PC cliente, abre **CMD como Administrador** y ejecuta:

```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

O copia la carpeta `scripts` a una USB y ejecuta localmente:

```cmd
D:\scripts\deploy_user.bat
```

### 8.3. ¿Qué hace deploy_user.bat?

El instalador realiza 3 pasos:

1. **Crea `%USERPROFILE%\scripts\`** — carpeta donde se aloja el script PowerShell
2. **Copia `gestor_handler.ps1`** — el manejador del protocolo
3. **Registra el protocolo `gestor://`** en el Registro de Windows

**Verificación:** Después de instalar, desde el navegador escribe:

```
gestor://select/?path=\\192.168.0.4\private\Equipos
```

Debería aparecer el diálogo "Abrir gestor://?" y al aceptar, abrirse el Explorador.

### 8.4. Desinstalación

```cmd
reg delete HKCR\gestor /f
del "%USERPROFILE%\scripts\gestor_handler.ps1"
```

### 8.5. Actualización

Para actualizar el script `gestor_handler.ps1` en un PC cliente ya instalado, simplemente ejecuta `deploy_user.bat` de nuevo:

```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

Esto sobrescribe el script existente con la nueva versión.

---

## 9. Solución de Problemas

### 9.1. Log de Depuración

El script `gestor_handler.ps1` incluye logging que se activa automáticamente. Para ver el log después de hacer clic en "Ver en carpeta":

```powershell
notepad $env:TEMP\gestor_debug.log
```

**Ejemplo de log exitoso:**
```
=== 15:30:22 ===
path_recibido=gestor://select/?path=%5C%5C192.168.0.4%5Cprivate%5C...
ruta_extraida=%5C%5C192.168.0.4%5Cprivate%5C...
ruta_decodificada=\\192.168.0.4\private\Equipos\Taladro Triturador...
carpeta=\\192.168.0.4\private\Equipos\Taladro Triturador...
OK: explorer /select
```

### 9.2. Problemas Comunes

#### El explorador se abre pero muestra "Este equipo"

**Causa:** La ruta UNC no se decodificó correctamente (los espacios como `+` no se convirtieron, o la ruta es demasiado larga para `explorer.exe`).

**Solución:** Verificar el log. Si la ruta decodificada contiene `+` en lugar de espacios, asegúrate de tener la última versión del script con `-replace '+', ' '`.

Si la ruta es muy larga, `Shell.Application.Open()` es el único método que funciona.

#### El navegador muestra "No se puede abrir esta página"

**Causa:** El protocolo `gestor://` no está registrado o el script no se encuentra.

**Solución:** Re-ejecutar `deploy_user.bat` como Administrador. Verificar que `%USERPROFILE%\scripts\gestor_handler.ps1` existe.

#### Error "La URL no tiene el formato esperado"

**Causa:** El script no pudo extraer `path=` de la URL recibida. Esto puede ocurrir si el registro de Windows pasa el argumento de forma incorrecta.

**Solución:** Verificar el registro de Windows:
```cmd
reg query HKCR\gestor\shell\open\command
```
Debe mostrar:
```
powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File "...\gestor_handler.ps1" -path "%1"
```

#### El botón "Ver en carpeta" no aparece

**Causa:** La función `hidden()` devuelve `true` porque no se pudo resolver el archivo o `record_folder_url()` / `record_folder_gestor_url()` devolvieron `null`.

**Solución:** Verificar que:
- El registro (Document, File, Equipment, etc.) tiene al menos un archivo asociado
- El archivo tiene una ruta (`path`) no vacía
- `STORAGE_LOCAL_PATH` está correctamente configurado en el `.env`

#### Error 500 al hacer clic

**Causa:** Puede ser un error de Livewire o de base de datos.

**Solución:**
- Verificar que MySQL esté corriendo
- Revisar `storage/logs/laravel.log`
- Limpiar caché: `php artisan route:clear && php artisan view:clear && php artisan config:clear`

### 9.3. Problemas del Servidor

#### `bootstrap/cache` no existe o no tiene permisos

```cmd
cd C:\inetpub\wwwroot\gestor-archivos
scripts\fix_server.bat
```

O manualmente:
```cmd
mkdir bootstrap\cache
echo * > bootstrap\cache\.gitignore
echo !.gitignore >> bootstrap\cache\.gitignore
icacls bootstrap\cache /grant "Todos:(OI)(CI)F" /T /Q
```

#### Estado MERGING después de git pull

```cmd
git merge --abort
git reset --hard origin/version-servidor-local
git pull
```

---

## 10. Historial de Cambios

### Commit `5322b45` — Actual
- Creado `scripts/fix_server.bat` para reparar `bootstrap/cache`

### Commit `0f4b87d`
- Cambiado `->url()` por `->action()` con `$livewire->js("window.location.href = '...'")`
- Elimina la ventana intermedia al hacer clic

### Commit `45bcfcf`
- Agregado logging detallado a `gestor_handler.ps1`

### Commit `f5dbede`
- **Bugfix:** Cambiado `urlencode()` por `rawurlencode()` en PHP (espacios como `%20` no `+`)
- **Bugfix:** Agregado `-replace '+', ' '` en PowerShell

### Commit `34754b7`
- Restructuración: `OpenFolderAction` redirige a `route('network.folder')`

### Commit `74fb657`
- Restaurado comportamiento exacto de `main`

### Commit `97df251`
- **Bugfix:** `Shell.Application.Open()` como método principal (explorer falla con UNC largas)
- **Bugfix:** `window.location.href` en vez de `window.open()` + `window.close()`

### Commit `e01a5c8`
- Creado `gestor_handler.bat` (fallback, no se usa activamente)
- Simplificado `gestor_handler.ps1` a 12 líneas

### Commit `28a221e`
- **Bugfix:** `IndexOf()` en vez de regex para extraer path de URL gestor://

### Commit `b06fa05`
- **Bugfix:** Regex acepta `/` opcional antes de `?` (Chrome añade `gestor://select/?path=`)

### Commit `8dcb223`
- **Bugfix:** `getElementById()` con verificaciones `if (element)` en la vista

### Commit `15345c8`
- **Bugfix:** Normalización de backslashes en `gestor_net_url()`

### Commit `850a097`
- Feature inicial: soporte LAN con protocolo `gestor://`

---

## Apéndice A: Función record_folder_gestor_url()

```php
function record_folder_gestor_url(Model $record): ?string
{
    $filePath = null;

    if ($record instanceof File) {
        $filePath = $record->path ?? null;
    } elseif ($record instanceof Document) {
        $filePath = $record->current?->path ?? null;
    } elseif (method_exists($record, 'documents')) {
        $document = $record->documents()->with('current')->latest('created_at')->first();
        $filePath = $document?->current?->path ?? null;
    }

    if (!$filePath) {
        return null;
    }

    return gestor_net_url($filePath, 'select');
}
```

**Comportamiento por tipo de registro:**

| Tipo de registro | ¿Qué archivo usa? |
|---|---|
| `App\Models\File` | El mismo registro (es un archivo) |
| `App\Models\Document` | `$record->current` (última versión) |
| `App\Models\Equipment` | Último documento → su current |
| `App\Models\EquipmentDataSheet` | Último documento → su current |
| `App\Models\EquipmentBlueprint` | Último documento → su current |
| `App\Models\EquipmentCatalog` | Último documento → su current |
| `App\Models\Part` | Último documento → su current |
| `App\Models\Person` | Último documento → su current |
| `App\Models\Supplier` | Último documento → su current |
| Cualquiera con `documents()` | Último documento → su current |

---

## Apéndice B: Función path() Helper

La función `path()` es crítica para convertir rutas de almacenamiento:

```php
function path(string $path, $asFolder = false, $base = true)
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

**Parámetros:**
- `$path`: Ruta relativa desde la base del storage (ej: `Equipos/Taladro/doc.pdf`)
- `$asFolder`: Si es `true`, trata el path como carpeta (quita el último segmento)
- `$base`: Si es `true`, devuelve ruta absoluta; si es `false`, devuelve ruta relativa

**Ejemplos:**
```php
path('Equipos/Taladro/doc.pdf', base: true);
// → C:\inetpub\wwwroot\gestor-archivos\storage\app\private\Equipos\Taladro\doc.pdf

path('Equipos/Taladro/doc.pdf', base: false);
// → Equipos\Taladro\doc.pdf
```

---

## Apéndice C: Verificación de funcionamiento

### Prueba 1: Protocolo gestor:// (en PC Cliente)

Escribe en la barra del navegador:
```
gestor://select/?path=\\192.168.0.4\private\Equipos
```

**Resultado esperado:** El navegador pregunta "Abrir gestor://?", al aceptar se abre el Explorador en la carpeta `\\192.168.0.4\private\Equipos`.

### Prueba 2: Desde la aplicación

1. Inicia sesión en `https://192.168.0.4:81`
2. Navega a cualquier equipo que tenga documentos
3. En la tabla de documentos, busca el botón "Ver en carpeta"
4. Haz clic

**Resultado esperado:** El explorador se abre en la carpeta donde está almacenado el archivo, con el archivo seleccionado.

### Prueba 3: Log de depuración

Después de hacer clic, revisa:
```powershell
notepad $env:TEMP\gestor_debug.log
```

**Log esperado:**
```
=== HH:mm:ss ===
path_recibido=gestor://select/?path=%5C%5C...
ruta_extraida=%5C%5C...
ruta_decodificada=\\192.168.0.4\private\...
carpeta=\\192.168.0.4\private\...
OK: explorer /select
```

---

*Documentación generada el 16/07/2026 — Versión 2.0*
*Commit: `5322b45`*
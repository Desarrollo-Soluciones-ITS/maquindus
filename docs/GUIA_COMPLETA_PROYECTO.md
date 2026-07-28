# Guía Completa del Proyecto Maquindus

## Sistema de Gestión Documental

---

## Índice

1. [Resumen del Proyecto](#1-resumen-del-proyecto)
2. [Tecnologías y Requisitos](#2-tecnologías-y-requisitos)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Modelos de Datos](#4-modelos-de-datos)
5. [Módulo de Documentos](#5-módulo-de-documentos)
6. [Funcionalidad "Ver en Carpeta"](#6-funcionalidad-ver-en-carpeta)
7. [Funcionalidad "Previsualizar Archivo"](#7-funcionalidad-previsualizar-archivo)
8. [Funcionalidad "Descargar Archivo"](#8-funcionalidad-descargar-archivo)
9. [Scripts de Sistema](#9-scripts-de-sistema)
10. [Configuración del Servidor](#10-configuración-del-servidor)
11. [Configuración de Clientes LAN](#11-configuración-de-clientes-lan)
12. [Mantenimiento](#12-mantenimiento)
13. [Apéndices](#13-apéndices)

---

## 1. Resumen del Proyecto

**Maquindus** es un sistema de gestión documental construido con Laravel 12 y Filament 4, diseñado para gestionar documentos técnicos de equipos industriales, incluyendo:

- Planos, catálogos, hojas de datos, especificaciones técnicas, normas, manuales, reportes, consultas de campo y repuestos.
- Gestión de versiones de archivos.
- Control de acceso basado en permisos.
- Archivado y restauración de documentos.
- Exploración de archivos directamente desde el navegador (en servidor o en red LAN).

---

## 2. Tecnologías y Requisitos

### 2.1. Stack Tecnológico

| Componente | Versión / Especificación |
|---|---|
| **Laravel** | 12.x |
| **PHP** | 8.2+ |
| **Filament** | 4.x (Panel Administrativo) |
| **Base de Datos** | MySQL / MariaDB |
| **Node.js** | 20+ (para Vite y assets) |
| **Frontend** | Blade + Tailwind CSS + Vite |
| **Servidor Web** | IIS (Internet Information Services) |
| **PowerShell** | 5.0+ (para clientes LAN) |

### 2.2. Dependencias Principales (Composer)

| Paquete | Propósito |
|---|---|
| `filament/filament` | Panel administrativo |
| `spatie/laravel-activitylog` | Registro de actividades |
| `spatie/laravel-backup` | Respaldo del sistema |
| `maatwebsite/excel` | Exportación a Excel |
| `laravel-lang/common` | Traducciones al español |
| `itsgoingd/clockwork` | Depuración y profiling |

### 2.3. Requisitos del Servidor

- Windows Server 2019/2022 con IIS
- PHP 8.2+ (nts, VC16/VS16)
- MySQL 8.0+ en puerto 3307
- Node.js 20+ (para compilar assets)
- Git (para actualizaciones)
- Acceso compartido SMB a `C:\inetpub\wwwroot\gestor-archivos\storage\app\private`

---

## 3. Estructura del Proyecto

```
📁 maquindus/
├── 📁 app/                          # Código de la aplicación
│   ├── 📁 Console/                  # Comandos Artisan
│   ├── 📁 Enums/                    # Enumeraciones PHP
│   ├── 📁 Filament/                 # Configuración de Filament
│   │   ├── 📁 Actions/              # Acciones personalizadas (botones)
│   │   ├── 📁 Filters/              # Filtros personalizados
│   │   ├── 📁 Inputs/               # Inputs personalizados
│   │   ├── 📁 Pages/                # Páginas del panel
│   │   ├── 📁 RelationManagers/     # Gestores de relaciones
│   │   ├── 📁 Resources/            # Recursos CRUD
│   │   └── 📁 Widgets/              # Widgets del dashboard
│   ├── 📁 Http/                     # Controladores y Middleware
│   │   ├── 📁 Controllers/          # Controladores HTTP
│   │   └── 📁 Middleware/           # Middleware personalizado
│   ├── 📁 Listeners/                # Event Listeners
│   ├── 📁 Models/                   # Modelos Eloquent
│   ├── 📁 Providers/                # Service Providers
│   ├── 📁 Rules/                    # Reglas de validación
│   ├── 📁 Services/                 # Servicios (Códigos, Búsqueda)
│   ├── 📁 Traits/                   # Traits reutilizables
│   └── 📄 helpers.php               # Funciones helper globales
├── 📁 bootstrap/                     # Bootstrap de Laravel
├── 📁 config/                       # Configuración de Laravel
├── 📁 database/                     # Migraciones y seeds
├── 📁 docker/                       # Configuración Docker
├── 📁 docs/                         # Documentación
├── 📁 lang/                         # Traducciones (español)
├── 📁 public/                       # Archivos públicos
├── 📁 resources/                     # Vistas y assets
│   ├── 📁 views/                    # Plantillas Blade
│   │   └── 📁 network/              # Vistas para funcionalidad LAN
├── 📁 routes/                       # Definición de rutas
│   └── 📄 web.php                   # Rutas web principales
├── 📁 scripts/                      # Scripts de sistema
│   ├── 📄 folder.php                # Servidor PHP auxiliar (abrir carpetas)
│   ├── 📄 client_folder.php         # Versión para clientes LAN
│   ├── 📄 preview.php               # Servidor PHP auxiliar (previsualizar)
│   ├── 📄 gestor_handler.ps1        # Manejador del protocolo gestor://
│   ├── 📄 deploy_user.bat           # Instalador para clientes LAN
│   ├── 📄 start-server.bat          # Inicia servidor PHP auxiliar
│   ├── 📄 start-user-server.bat     # Inicia servidor en cliente
│   ├── 📄 open_in_explorer.reg      # Registro del protocolo gestor://
│   ├── 📄 task.xml                  # Tarea programada Windows
│   ├── 📄 fix_server.bat            # Reparación de servidor
│   └── 📄 ...                       # Otros scripts utilitarios
├── 📁 storage/                      # Almacenamiento
│   ├── 📁 app/private/              # Archivos privados (documentos)
│   ├── 📁 app/public/               # Archivos públicos
│   ├── 📁 app/backup/               # Respaldos
│   ├── 📁 app/temp/                 # Archivos temporales
│   └── 📁 logs/                     # Logs de Laravel
├── 📁 tests/                        # Tests automatizados
├── 📄 .env                          # Variables de entorno
├── 📄 composer.json                 # Dependencias PHP
├── 📄 package.json                  # Dependencias Node
└── 📄 vite.config.js                # Configuración de Vite
```

---

## 4. Modelos de Datos

### 4.1. Diagrama de Entidades

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Document   │────▶│     File     │    │  Documentable │
│              │     │              │     │  (Polimórfico)│
│- id          │     │- id          │     │              │
│- name        │     │- document_id │     │- Equipment   │
│- category    │     │- path        │     │- Part        │
│- documentable│     │- mime        │     │- Person      │
│- review_date │     │- version     │     │- Supplier    │
│- locked_at   │     │- created_at  │     │- DataSheet   │
│- trashed_at  │     │              │     │- Blueprint   │
└──────────────┘     └──────────────┘     │- Catalog     │
                                          │- TechnicalSpec│
                                          │- Standard    │
                                          │- FieldQuery  │
                                          │- SparePart   │
                                          │- Report      │
                                          └──────────────┘
```

### 4.2. Modelos del Sistema

| Modelo | Tipo | Descripción |
|---|---|---|
| `Document` | Principal | Documento genérico con nombre, categoría y fechas |
| `File` | Versión | Versión de un archivo (ruta, mime, versión) |
| `Equipment` | Documentable | Equipo industrial |
| `Part` | Documentable | Repuesto / Parte |
| `Person` | Documentable | Contacto / Persona |
| `Supplier` | Documentable | Proveedor |
| `EquipmentDataSheet` | Documentable | Hoja de datos de equipo |
| `EquipmentBlueprint` | Documentable | Plano de equipo |
| `EquipmentCatalog` | Documentable | Catálogo de equipo |
| `EquipmentTechnicalSpecification` | Documentable | Especificación técnica |
| `EquipmentStandard` | Documentable | Norma / estándar |
| `EquipmentFieldQuery` | Documentable | Consulta de campo |
| `EquipmentSparePart` | Documentable | Repuesto de equipo |
| `EquipmentReport` | Documentable | Reporte de equipo |
| `EquipmentManual` | Documentable | Manual de equipo |
| `User` | Auth | Usuario del sistema |
| `Role` | Auth | Rol con permisos |
| `Permission` | Auth | Permiso individual |
| `Activity` | Log | Registro de actividad (usando spatie/laravel-activitylog) |

### 4.3. Relación Polimórfica Documentable

El modelo `Document` se relaciona polimórficamente con cualquier "documentable" (Equipment, Part, Person, Supplier, etc.) a través de `documentable_type` y `documentable_id`.

```php
// En Document.php
public function documentable(): MorphTo
{
    return $this->morphTo();
}

public function files(): HasMany
{
    return $this->hasMany(File::class);
}

public function current(): HasOne
{
    return $this->hasOne(File::class)->latestOfMany();
}
```

Cada `Document` tiene múltiples `File` (versiones), y `current` es la última versión.

### 4.4. Categorías de Documentos

Definidas en `App\Enums\Category`:

- Plano
- Catálogo
- Hoja de datos
- Especificación técnica
- Norma
- Manual
- Reporte
- Consulta de campo
- Repuesto

### 4.5. Prefijos de Códigos

Definidos en `App\Enums\Prefix`, usados por el servicio `Code` para generar códigos únicos para equipos y documentos.

---

## 5. Módulo de Documentos

### 5.1. Recursos Filament

| Recurso | Archivo | Propósito |
|---|---|---|
| DocumentsTable | `app/Filament/Resources/Documents/Tables/DocumentsTable.php` | Configuración de la tabla de documentos |
| FilesRelationManager | `app/Filament/Resources/Documents/RelationManagers/FilesRelationManager.php` | Gestor de versiones de archivos |

### 5.2. Acciones de Documentos

| Acción | Archivo | Descripción |
|---|---|---|
| `OpenFolderAction` | `app/Filament/Actions/Documents/OpenFolderAction.php` | Abre el explorador en la ubicación del archivo |
| `PreviewAction` | `app/Filament/Actions/Documents/PreviewAction.php` | Previsualiza el archivo en el navegador |
| `DownloadAction` | `app/Filament/Actions/Documents/DownloadAction.php` | Descarga el archivo |
| `ViewAction` | `app/Filament/Actions/Documents/ViewAction.php` | Muestra detalles del documento |
| `EditAction` | `app/Filament/Actions/Documents/EditAction.php` | Edita el documento |
| `ArchiveAction` | `app/Filament/Actions/ArchiveAction.php` | Archiva (soft delete) el documento |
| `RestoreAction` | `app/Filament/Actions/RestoreAction.php` | Restaura un documento archivado |

### 5.3. Permisos

| Permiso | Descripción |
|---|---|
| `documents.show_file` | Ver archivos de documentos |
| `documents.download` | Descargar documentos |
| `documents.show` | Ver detalles de documentos |
| `documents.edit` | Editar documentos |
| `documents.delete` | Archivar/eliminar documentos |
| `documents.restore` | Restaurar documentos archivados |
| `files.show_file` | Ver archivos individuales |
| `files.download` | Descargar archivos |
| `files.show` | Ver detalles de archivos |
| `files.create` | Subir nuevas versiones |

---

## 6. Funcionalidad "Ver en Carpeta"

*Documentación detallada en `docs/FUNCIONALIDAD_VER_EN_CARPETA.md`*

### 6.1. Resumen

El botón **"Ver en carpeta"** permite abrir el Explorador de Windows en la ubicación exacta donde está almacenado un archivo. Soporta dos modos:

- **Servidor local**: Ejecuta `explorer.exe` en el servidor via PHP auxiliar (puerto 8970).
- **LAN (clientes en red)**: Usa el protocolo personalizado `gestor://` registrado en Windows, que ejecuta PowerShell para abrir el explorador en el PC del cliente.

### 6.2. Modo LAN (Clientes)

**No requiere PHP en el cliente.** Solo PowerShell (incluido en Windows 10/11).

**Instalación en PC cliente (una vez, como Administrador):**
```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

### 6.3. Archivos Clave

| Ruta | Propósito |
|---|---|
| `app/Filament/Actions/Documents/OpenFolderAction.php` | Acción del botón |
| `app/helpers.php` | Funciones `gestor_net_url()`, `record_folder_gestor_url()`, `record_folder_url()` |
| `app/Http/Controllers/NetworkFileController.php` | Controlador LAN |
| `routes/web.php` | Rutas `network.folder` y `network.file` |
| `scripts/gestor_handler.ps1` | Manejador PowerShell del protocolo `gestor://` |
| `scripts/folder.php` | Servidor PHP auxiliar (local) |
| `scripts/deploy_user.bat` | Instalador para clientes LAN |
| `scripts/start-server.bat` | Inicia servidor PHP auxiliar |

### 6.4. Variables de Entorno Relacionadas

```ini
SHELL_API_URL=http://127.0.0.1:8970
STORAGE_LOCAL_PATH=C:/inetpub/wwwroot/gestor-archivos/storage/app/private
STORAGE_NETWORK_PATH=\\\\192.168.0.4\\private    # ← Activa modo LAN
NETWORK_SHARE_ROOT=\\\\192.168.0.4\\private
```

---

## 7. Funcionalidad "Previsualizar Archivo"

### 7.1. Descripción

Permite ver archivos PDF, imágenes y otros formatos directamente en el navegador sin descargarlos.

### 7.2. Flujo

1. El usuario hace clic en "Previsualizar"
2. `PreviewAction` redirige a `route('files.preview', ['file' => $file->id])`
3. `PreviewFileController` sirve el archivo usando `Storage::response()` o `Storage::temporaryUrl()`
4. El navegador muestra el archivo (PDF inline, imagen, etc.)

### 7.3. Archivos Clave

| Ruta | Propósito |
|---|---|
| `app/Filament/Actions/Documents/PreviewAction.php` | Acción del botón |
| `app/Http/Controllers/PreviewFileController.php` | Controlador que sirve el archivo |

---

## 8. Funcionalidad "Descargar Archivo"

### 8.1. Descripción

Permite descargar el archivo original al PC del usuario.

### 8.2. Flujo

1. El usuario hace clic en "Descargar"
2. `DownloadAction` genera una URL de descarga temporal
3. El navegador inicia la descarga

### 8.3. Archivos Clave

| Ruta | Propósito |
|---|---|
| `app/Filament/Actions/Documents/DownloadAction.php` | Acción del botón |

---

## 9. Scripts de Sistema

### 9.1. Scripts del Servidor

| Script | Propósito | ¿Cómo se ejecuta? |
|---|---|---|
| `scripts/start-server.bat` | Inicia servidor PHP auxiliar en puerto 8970 | Tarea programada o manual |
| `scripts/folder.php` | Recibe ruta y ejecuta `explorer /select` | Automático (PHP 8970) |
| `scripts/preview.php` | Sirve archivos para previsualización | Automático (PHP 8970) |
| `scripts/task.xml` | Tarea programada para iniciar servidor | Importar en Programador de Tareas |
| `scripts/fix_server.bat` | Repara `bootstrap/cache` y resuelve merges | Manual (como Admin) |

### 9.2. Scripts del Cliente LAN

| Script | Propósito | ¿Dónde se instala? |
|---|---|---|
| `scripts/deploy_user.bat` | Instalador del protocolo `gestor://` | En cada PC cliente |
| `scripts/gestor_handler.ps1` | Manejador del protocolo `gestor://` | `%USERPROFILE%\scripts\` |
| `scripts/open_in_explorer.reg` | Registro del protocolo en Windows | Usado por deploy_user.bat |
| `scripts/start-user-server.bat` | Inicia servidor PHP en cliente (opcional) | Si el cliente tiene PHP |

### 9.3. Scripts de Utilidad

| Script | Propósito |
|---|---|
| `scripts/check_*.php` | Diagnóstico y verificación de la base de datos |
| `scripts/fix_all_paths.php` | Corrección de rutas de archivos |
| `scripts/clean_copia.php` | Limpieza de datos duplicados |
| `scripts/preview.php` | Servidor auxiliar para previsualización |

---

## 10. Configuración del Servidor

### 10.1. Variables de Entorno (.env)

```ini
APP_NAME=Maquindus
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://192.168.0.4:81

STORAGE_ROOT=C:/inetpub/wwwroot/gestor-archivos/storage
SHELL_API_URL=http://127.0.0.1:8970
STORAGE_LOCAL_PATH=C:/inetpub/wwwroot/gestor-archivos/storage/app/private

# LAN (descomentar para activar):
STORAGE_NETWORK_PATH=\\\\192.168.0.4\\private
NETWORK_SHARE_ROOT=\\\\192.168.0.4\\private

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=maquindus
DB_USERNAME=root
DB_PASSWORD=MAQ-12-12-2025

SESSION_DRIVER=file
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

### 10.2. Archivo hosts

Para que la aplicación funcione con HTTPS en el servidor local, agregar al `C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1  maquindus.local
```

### 10.3. Inicio del Servidor PHP Auxiliar

El servidor PHP auxiliar debe estar corriendo en `http://127.0.0.1:8970` para la funcionalidad "Ver en carpeta" en modo servidor local.

**Inicio manual:**
```cmd
C:\inetpub\wwwroot\gestor-archivos\scripts\start-server.bat
```

**Inicio automático (Programador de Tareas):**
1. Abrir `taskschd.msc`
2. Importar `C:\inetpub\wwwroot\gestor-archivos\scripts\task.xml`
3. Ajustar la cuenta de usuario si es necesario

### 10.4. Compilación de Assets

```bash
npm install
npm run build
```

### 10.5. Migraciones

```bash
php artisan migrate --force
```

### 10.6. Limpieza de Caché

Después de actualizar el código:
```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

---

## 11. Configuración de Clientes LAN

### 11.1. Requisitos del Cliente

- Windows 10 u 11
- PowerShell 5.0+ (incluido)
- Acceso de red al servidor (`\\192.168.0.4\private`)
- **No requiere PHP**

### 11.2. Instalación

Abrir **CMD como Administrador** y ejecutar:

```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

### 11.3. ¿Qué instala?

1. **`%USERPROFILE%\scripts\gestor_handler.ps1`** — Script PowerShell que procesa el protocolo `gestor://`
2. **Registro de Windows** — Protocolo `gestor://` asociado al script

### 11.4. Verificación

En el navegador del cliente, escribir:
```
gestor://select/?path=\\192.168.0.4\private\Equipos
```

Debería preguntar "Abrir gestor://?" y al aceptar, abrir el Explorador.

### 11.5. Actualización

Para actualizar el script en clientes ya instalados:
```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

### 11.6. Desinstalación

```cmd
reg delete HKCR\gestor /f
del "%USERPROFILE%\scripts\gestor_handler.ps1"
```

---

## 12. Mantenimiento

### 12.1. Actualización del Código

```bash
cd C:\inetpub\wwwroot\gestor-archivos
git pull
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan migrate --force
```

### 12.2. Resolución de Conflictos de Merge

Si `git pull` falla con conflictos:

```cmd
git merge --abort
git reset --hard origin/version-servidor-local
git pull
php artisan route:clear
php artisan view:clear
php artisan config:clear
```

### 12.3. Reparación de bootstrap/cache

Si Laravel muestra error de `bootstrap/cache`:

```cmd
scripts\fix_server.bat
```
(Ejecutar como Administrador)

O manualmente:
```cmd
mkdir bootstrap\cache
echo * > bootstrap\cache\.gitignore
echo !.gitignore >> bootstrap\cache\.gitignore
icacls bootstrap\cache /grant "Todos:(OI)(CI)F" /T /Q
```

### 12.4. Respaldos

Configurados con `spatie/laravel-backup`:
```bash
php artisan backup:run
```

Los respaldos se almacenan en `storage/app/backup/`.

### 12.5. Logs

- **Laravel:** `storage/logs/laravel.log`
- **PowerShell (clientes):** `%TEMP%\gestor_debug.log`
- **Actividades de usuarios:** Tabla `activity_log` en la base de datos

---

## 13. Apéndices

### 13.1. Rutas de la Aplicación

| Método | Ruta | Propósito |
|---|---|---|
| GET | `/` | Redirige al dashboard |
| GET | `/dashboard` | Dashboard principal |
| GET | `/files/{file}/preview` | Previsualizar archivo |
| GET | `/network/folder/{file}` | Abrir carpeta (LAN) |
| GET | `/network/file/{file}` | Abrir archivo (LAN) |
| GET | `/admin/*` | Panel Filament |

### 13.2. Funciones Helper Globales

Definidas en `app/helpers.php` (autocargado via `composer.json`):

| Función | Propósito |
|---|---|
| `mime_type($mime)` | Traduce MIME a nombre legible |
| `check_solidworks($mime, $path)` | Detecta archivos SolidWorks |
| `model_to_spanish($model, $plural)` | Traduce nombre de modelo a español |
| `documentable_name_column($model)` | Columna de nombre según modelo |
| `path($path, $asFolder, $base)` | Convierte ruta relativa a absoluta |
| `hasPermission($perm)` | Verifica permiso del usuario actual |
| `exec_url($filepath, $endpoint)` | Genera URL para servidor PHP auxiliar |
| `gestor_net_url($filepath, $action)` | Genera URL del protocolo `gestor://` |
| `record_folder_url($record)` | Obtiene URL para abrir carpeta (servidor) |
| `record_folder_gestor_url($record)` | Obtiene URL gestor:// (LAN) |
| `documentable_view_url($model)` | URL para ver el documento padre |

### 13.3. Enumeraciones

| Enum | Archivo | Valores |
|---|---|---|
| `Category` | `App\Enums\Category` | `Plano`, `Catálogo`, `Hoja de datos`, `Especificación técnica`, `Norma`, `Manual`, `Reporte`, `Consulta de campo`, `Repuesto` |
| `Prefix` | `App\Enums\Prefix` | Prefijos para códigos de equipos |
| `Status` | `App\Enums\Status` | Estados de documentos |

### 13.4. Traits

| Trait | Modelos que lo usan | Propósito |
|---|---|---|
| `HasActivityLog` | Document, File, etc. | Registro de actividades |
| `Lockable` | Document | Bloqueo de documentos |
| `PreventsEditingTrashed` | Document | Evita editar documentos archivados |
| `Searchable` | Equipment | Búsqueda avanzada |

### 13.5. Comandos Artisan

| Comando | Propósito |
|---|---|
| `php artisan app:setup` | Configuración inicial del proyecto |
| `php artisan migrate --force` | Ejecutar migraciones |
| `php artisan queue:work` | Procesar cola de trabajos |
| `php artisan backup:run` | Ejecutar respaldo |

### 13.6. Puertos Utilizados

| Puerto | Servicio | Propósito |
|---|---|---|
| 81 | IIS (HTTPS) | Aplicación Laravel |
| 3307 | MySQL | Base de datos |
| 8970 | PHP Built-in Server | Servidor auxiliar (abrir carpetas) |
| 6379 | Redis (opcional) | Caché y sesiones |

---

*Documentación generada el 16/07/2026 — Versión 1.0*
*Commit: `9646023`*
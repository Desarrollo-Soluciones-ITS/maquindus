# Plan completo de despliegue para Windows Server 2008 + clientes LAN

Este documento detalla paso a paso la instalación, configuración y puesta en marcha de la aplicación Laravel y el cliente de escritorio para habilitar `Ver en carpeta` y `Abrir archivo` en la LAN.

---

## 1. Preparación del servidor

### 1.1 Requisitos previos

Asegúrate de tener disponibles:

- Servidor Windows Server 2008 con IIS.
- Acceso de administrador al servidor.
- Instalador de PHP 8.2 o 8.3 NTS para Windows.
- Composer.
- Node.js y npm.
- Certificado SSL (opcional, recomendado si se usa HTTPS).
- Acceso al repositorio Git de la aplicación.

### 1.2 Crear el directorio de la aplicación

1. En el servidor, crear la carpeta:
   - `C:\inetpub\wwwroot\gestor-archivos`
2. Copiar el repositorio o clonar desde Git:
   ```powershell
   cd C:\inetpub\wwwroot\gestor-archivos
   git clone <repo-url> .
   ```
3. Si ya había un repositorio, actualizarlo con:
   ```powershell
   git stash
   git pull origin main
   git stash pop
   ```

### 1.3 Configurar `.env`

1. Copiar `.env.example` a `.env` si aún no existe.
2. Editar `.env` con los valores de producción:
   ```env
   APP_NAME=GestorArchivos
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://192.168.0.4

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=<tu_bd>
   DB_USERNAME=<tu_usuario>
   DB_PASSWORD=<tu_contraseña>

   FILESYSTEM_DISK=local
   STORAGE_ROOT=C:\inetpub\wwwroot\gestor-archivos\storage\app\private
   SHELL_API_URL=http://127.0.0.1:8970
   ```
3. Generar la clave de aplicación:
   ```powershell
   php artisan key:generate
   ```

### 1.4 Instalar dependencias y construir assets

1. Instalar dependencias PHP y JS:
   ```powershell
   composer install --no-dev --optimize-autoloader
   npm install
   ```
2. Construir el frontend:
   ```powershell
   npm run build
   ```

### 1.5 Ejecutar migraciones y cargar datos

1. Ejecutar migraciones:
   ```powershell
   php artisan migrate --force
   ```
2. Si hay seeders relevantes:
   ```powershell
   php artisan db:seed --force
   ```
3. Indexar los archivos:
   ```powershell
   php artisan files:scan
   ```

### 1.6 Cachear configuraciones

```powershell
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.7 Configurar IIS

1. Crear un sitio nuevo en IIS con raíz de aplicación:
   - `C:\inetpub\wwwroot\gestor-archivos\public`
2. Usar un Application Pool con `No Managed Code`.
3. Asignar al AppPool una identidad que tenga acceso a:
   - `C:\inetpub\wwwroot\gestor-archivos\storage`
   - `C:\inetpub\wwwroot\gestor-archivos\bootstrap\cache`
4. Si usas SSL, asignar el binding HTTPS al sitio.

### 1.8 Configurar permisos de carpeta

Dar permisos NTFS de lectura/escritura a la cuenta del AppPool en:

- `C:\inetpub\wwwroot\gestor-archivos\storage`
- `C:\inetpub\wwwroot\gestor-archivos\bootstrap\cache`

### 1.9 Configurar el recurso compartido SMB

1. Compartir el directorio o la subcarpeta:
   - `C:\inetpub\wwwroot\gestor-archivos\storage\app\private`
2. Nombre del share recomendado:
   - `gestor-archivos`
3. Desde los clientes, la ruta UNC debe ser:
   - `\\192.168.0.4\gestor-archivos\storage\app\private`
4. Conceder permisos de lectura/exploración a los usuarios de la LAN.

---

## 2. Configuración del código y scripts de cliente

### 2.1 Archivos de cliente necesarios

En el repositorio deben estar:

- `scripts/folder.php`
- `scripts/preview.php`
- `app/helpers.php`
- `app/Filament/Actions/Documents/OpenFolderAction.php`
- `app/Filament/Actions/Documents/PreviewAction.php`

### 2.2 Qué hacen las acciones Filament

- `OpenFolderAction` ahora devuelve una URL al servicio local del cliente.
- `PreviewAction` también devuelve una URL al servicio local del cliente.
- El servidor Laravel no ejecuta `explorer` ni `start` directamente.

### 2.3 Configuración de `folder.php` y `preview.php`

Asegúrate de que ambos scripts usan la ruta UNC correcta:

```php
$relativePath = trim(str_replace('/', DIRECTORY_SEPARATOR, urldecode($input)), DIRECTORY_SEPARATOR);
$sharedRoot = '\\192.168.0.4\\gestor-archivos\\storage\\app\\private';
$root = rtrim($sharedRoot, DIRECTORY_SEPARATOR);
$path = $root . DIRECTORY_SEPARATOR . $relativePath;
```

### 2.4 Copiar `php.exe` portable a los clientes

1. Extraer `php.zip` en:
   - `C:\Users\<Usuario>\Gestor de Archivos - Scripts`
2. Copiar `folder.php` y `preview.php` a la misma carpeta.
3. Verificar que `php.exe` funciona en esa carpeta.

### 2.5 Crear la tarea programada en el cliente

1. Abrir el Programador de tareas.
2. Crear una tarea nueva con estas opciones:
   - Ejecutar con los privilegios más altos.
   - Desencadenador: `Al iniciar el sistema`.
   - Acción: `powershell.exe`
   - Argumentos:
     ```powershell
     -WindowStyle Hidden -ExecutionPolicy Bypass -Command ".\php.exe -S 127.0.0.1:8970"
     ```
   - Iniciar en:
     - `C:\Users\<Usuario>\Gestor de Archivos - Scripts`
3. Desmarcar condiciones de ahorro de energía y límites de tiempo.

### 2.6 Probar el servicio local en el cliente

1. Abrir el navegador y visitar:
   - `http://127.0.0.1:8970/preview.php?path=docs/sample1.pdf`
2. Debe devolver JSON con `cmd`, `out`, `code`.
3. Si no responde, revisar la tarea programada y el `php.exe`.

---

## 3. Pruebas y verificación

### 3.1 Probar desde el servidor

1. Abrir `http://192.168.0.4` en un navegador.
2. Iniciar sesión en Filament.
3. Verificar que `Descargar` funcione.

### 3.2 Probar desde un cliente LAN

1. Confirmar que el cliente puede acceder al share:
   - `\\192.168.0.4\gestor-archivos\storage\app\private`
2. Confirmar que el servicio local está activo:
   - `http://127.0.0.1:8970/preview.php?path=docs/sample1.pdf`
3. Abrir la app web en `http://192.168.0.4`.
4. Probar:
   - `Abrir archivo`
   - `Ver en carpeta`

### 3.3 Verificar permisos y firewall

1. El Firewall del servidor debe permitir el puerto `8970` para la LAN.
2. El Firewall del cliente debe permitir `localhost:8970`.
3. El recurso compartido SMB debe ser accesible desde las estaciones.

---

## 4. Checklist completo

- [ ] Servidor con IIS apuntando a `public`
- [ ] `.env` configurado con `STORAGE_ROOT` y `SHELL_API_URL`
- [ ] Dependencias instaladas (`composer`, `npm`)
- [ ] Migraciones y seeders ejecutados
- [ ] `php artisan files:scan` ejecutado
- [ ] Assets construidos (`npm run build`)
- [ ] Configuración de cache en Laravel
- [ ] Share SMB creado y accesible
- [ ] Scripts `folder.php` y `preview.php` configurados con UNC
- [ ] `php.exe` portable copiado a cada cliente
- [ ] Tarea programada creada en cada cliente
- [ ] Servicio local activo en `http://127.0.0.1:8970`
- [ ] Pruebas de `Ver en carpeta` y `Abrir archivo` exitosas

---

## 5. Notas finales

- Esta solución funciona en entornos legacy con Windows Server 2008 y clientes Windows.
- El servidor no ejecuta Explorador en los equipos cliente; cada desktop lo hace localmente.
- Si algún cliente no puede acceder a `\192.168.0.4\gestor-archivos\storage\app\private`, la acción fallará.
- Mantener IP estática del servidor y la ruta UNC alineada entre servidor y cliente.

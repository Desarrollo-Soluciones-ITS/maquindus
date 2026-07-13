# Pasos de Deploy

## 1. Preparación del servidor

### 1.1 Clonar o actualizar el repositorio

1. Abrir PowerShell o Git Bash en el servidor.
2. Ir a la carpeta de despliegue:
   ```powershell
   cd C:\inetpub\wwwroot\gestor-archivos
   ```
3. Si el repositorio no existe aún, clonar:
   ```powershell
   git clone <repo-url> .
   ```
4. Si ya existe, actualizar con los últimos cambios:
   ```powershell
   git fetch origin
   git checkout main
   git reset --hard origin/main
   ```
5. Si hay cambios locales pendientes que quieras guardar:
   ```powershell
   git stash push -m "deploy-save"
   git pull origin main
   git stash pop
   ```

### 1.2 Configurar `.env`

1. Copiar el archivo de ejemplo si hace falta:
   ```powershell
   copy .env.example .env
   ```
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
   php artisan key:generate --ansi
   ```
4. Confirmar que el `.env` contiene los valores correctos antes de continuar.

### 1.3 Instalar dependencias y construir assets

1. Instalar dependencias PHP:
   ```powershell
   composer install --no-dev --optimize-autoloader
   ```
2. Instalar dependencias Node:
   ```powershell
   npm install
   ```
3. Construir los assets:
   ```powershell
   npm run build
   ```

### 1.4 Migraciones y cache

1. Ejecutar migraciones en prod:
   ```powershell
   php artisan migrate --force
   ```
2. Cachear configuración, rutas y vistas:
   ```powershell
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## 2. Verificar que el servicio shell esté corriendo

### 2.1 Comprobar puerto en el servidor

1. Verificar que el puerto `8970` no esté en uso o que el servicio local pueda levantarse:
   ```powershell
   netstat -ano | findstr ":8970"
   ```
2. En PowerShell, verificar el puerto directamente:
   ```powershell
   Get-NetTCPConnection -LocalPort 8970
   ```

### 2.2 Probar la URL del servicio local

1. Abrir el navegador en la máquina cliente o servidor y visitar:
   - `http://127.0.0.1:8970/folder.php?path=docs/sample1.pdf`
   - `http://127.0.0.1:8970/preview.php?path=docs/sample1.pdf`
2. El servicio debe devolver JSON con los campos `cmd`, `out` y `code`.

### 2.3 Verificar acceso a la carpeta compartida desde el cliente

1. Abrir el Explorador de archivos en el cliente.
2. Acceder al recurso compartido:
   ```powershell
   \192.168.0.4\gestor-archivos\storage\app\private
   ```
3. Confirmar que el archivo existe en la ruta relativa usada por la aplicación.

## 3. Ajustes y archivos para las PCs de los usuarios

### 3.1 Carpeta local en cada cliente

1. Crear en cada cliente la carpeta:
   - `C:\Users\<Usuario>\Gestor de Archivos - Scripts`
2. Copiar a esa carpeta:
   - `php.exe` portable
   - `folder.php`
   - `preview.php`
3. Asegurarse de que `php.exe` y los scripts estén en la misma carpeta.

### 3.2 Configuración de `folder.php` y `preview.php`

1. Verificar que ambos scripts intenten resolver la ruta en el share UNC:
   - `\\192.168.0.4\\gestor-archivos\\storage\\app\\private`
2. Si se usa variable de entorno local, confirmar que `SHELL_SHARE_ROOT` apunte a esa ruta.
3. El script debe transformar la ruta relativa enviada por Laravel a una ruta completa de Windows.

### 3.3 Crear la tarea programada en el cliente

1. Abrir el Programador de tareas.
2. Crear una nueva tarea con estas opciones:
   - Ejecutar con los privilegios más altos.
   - Ejecutar solo cuando el usuario haya iniciado sesión.
   - Desencadenador: al iniciar el sistema.
   - Condiciones: desmarcar todas.
   - Configuración: desmarcar "Detener la tarea si se ejecuta durante más de...".
3. Acción:
   - Programa o script: `powershell.exe`
   - Argumentos:
     ```powershell
     -WindowStyle Hidden -ExecutionPolicy Bypass -Command ".\php.exe -S 127.0.0.1:8970"
     ```
   - Iniciar en: `C:\Users\<Usuario>\Gestor de Archivos - Scripts`
4. Guardar la tarea y reiniciar el cliente para validar que el servicio arranca.

### 3.4 Probar en cada cliente

1. Abrir el navegador del cliente e ingresar:
   - `http://127.0.0.1:8970/folder.php?path=docs/sample1.pdf`
   - `http://127.0.0.1:8970/preview.php?path=docs/sample1.pdf`
2. Si el servicio responde, entonces el servidor web y las acciones de Filament podrán invocar `Ver en carpeta` y `Abrir archivo`.

### 3.5 Acceso directo para usuarios

1. Crear un acceso directo en el escritorio de cada usuario a:
   - `http://192.168.0.4`
2. Esto facilita entrar a la aplicación desde la LAN.

## 4. Checklist rápido

- [ ] Repositorio actualizado con `git fetch` / `git reset --hard origin/main`
- [ ] `.env` configurado correctamente en producción
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm install` y `npm run build`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache` / `route:cache` / `view:cache`
- [ ] Servicio local `http://127.0.0.1:8970` activo en los clientes
- [ ] Share SMB accesible desde cada cliente
- [ ] Scripts `folder.php` / `preview.php` copiados en cada cliente
- [ ] Tarea programada creada en cada cliente
- [ ] Acceso directo web disponible para los usuarios

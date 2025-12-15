# Pasos de Deploy

## En scripts

1. Configurar prefijo correcto en folder.php y preview.php para la carpeta compartida "Proyecto Base de Datos"
2. Igualmente se tiene que chequear que funcione en cada cliente con esa ruta
3. Pasarlos a pendrive

## En Server

1. Instalar Git Bash
2. Copiar carpeta .git a gestor-archivos en server
3. Stash de lo que haya, git pull, y stash pop para ver qlq
4. Configurar .env con todo para prod y variables faltantes
5. Actualizar php.ini (revisar TODO en el php.ini develop y reemplazar el php.ini que hay ahorita en el server)
6. Cambiar IP en request.inf
7. Instalar certificado siguiendo los pasos de ssl.md
  - certreq -new request.inf GestorArchivos.cer
  - Verificar en manejador de certificados los permisos sobre la llave privada
  - Agregar el SSL a IIS en el binding
  - Pasar GestorArchivos.cer a pendrive
8. Ejecutar indexer de Jesús
9. Crear usuarios por defecto en prod
10. Eliminar carpeta .git, docs y eliminar todo.md
11. Ejecutar npm install, composer install, npm run build, y reiniciar el servidor.

## Server y Clientes

1. Crear carpeta C:\Users\(Usuario)\Gestor de Archivos - Scripts
2. Copiar archivos folder.php y preview.php a esa carpeta
3. Extraer php.zip en esa carpeta
4. En el programador de tareas, crear nueva tarea
  - Marcar "Ejecutar con privilegios más altos"
  - Marcar "Ejecutar solo cuando el usuario haya iniciado sesión"
  - Desencadenador: al iniciar el sistema
  - Desmarcar Condiciones > desmarcar todas
  - Desmarcar Configuración > Detener la tarea si se ejecuta durante más de
  - Programa o script: powershell.exe
    - Argumentos: -WindowStyle Hidden -ExecutionPolicy Bypass -Command ".\php.exe -S 127.0.0.1:8970"
  - Iniciar en: C:\Users\(Usuario)\scripts
5. Crear enlace en el escritorio a la IP y el puerto via HTTPS para que entren fácil

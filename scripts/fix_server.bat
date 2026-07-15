@echo off
title Reparando Servidor Gestor de Archivos
cd /d C:\inetpub\wwwroot\gestor-archivos

echo =============================================
echo Reparando Servidor de Produccion
echo EJECUTAR COMO ADMINISTRADOR
echo =============================================
echo.

echo PASO 1: Forzar salida del merge
git merge --abort 2>nul
echo OK
echo.

echo PASO 2: Crear bootstrap/cache
if not exist bootstrap\cache mkdir bootstrap\cache
echo OK
echo.

echo PASO 3: Forzar al ultimo commit remoto
git reset --hard origin/version-servidor-local
if %errorlevel% equ 0 (
    echo OK: git reset exitoso
) else (
    echo ERROR: git reset fallo - ejecuta este .bat como ADMINISTRADOR
    pause
    exit /b 1
)
echo.

echo PASO 4: Limpiar cache Laravel
php artisan route:clear
php artisan view:clear
php artisan config:clear
echo.

echo PASO 5: Dar permisos a bootstrap/cache
icacls bootstrap\cache /grant "Todos:(OI)(CI)F" /T /Q 2>nul
echo OK
echo.

echo =============================================
echo LISTO - Servidor reparado
echo =============================================
echo.
echo Probar haciendo clic en "Ver en carpeta"
echo Ya no deberia abrir ventanas intermedias
echo.
pause
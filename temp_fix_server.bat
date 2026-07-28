@echo off
echo =============================================
echo Arreglando servidor de produccion
echo =============================================
echo.

cd /d C:\inetpub\wwwroot\gestor-archivos

echo PASO 1: Crear directorio cache faltante
if not exist bootstrap\cache mkdir bootstrap\cache
echo OK
echo.

echo PASO 2: Forzar al ultimo commit remoto (tiene el fix)
git reset --hard origin/version-servidor-local
echo OK
echo.

echo PASO 3: Limpiar cache de Laravel
php artisan route:clear
php artisan view:clear
php artisan config:clear
echo OK
echo.

echo =============================================
echo LISTO. Ya deberia funcionar sin ventana intermedia
echo =============================================
echo.
pause
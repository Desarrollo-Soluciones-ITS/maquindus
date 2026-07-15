@echo off
echo =============================================
echo Resolviendo conflictos de merge en el servidor
echo =============================================
echo.

cd /d C:\inetpub\wwwroot\gestor-archivos

echo PASO 1: Ver estado actual
git status
echo.
pause

echo PASO 2: Aceptar nuestra version de helpers.php
git checkout --theirs app/helpers.php
echo.
if %errorlevel% equ 0 (echo OK: helpers.php resuelto) else (echo ERROR)
pause

echo PASO 3: Aceptar nuestra version de routes/web.php
git checkout --theirs routes/web.php
echo.
if %errorlevel% equ 0 (echo OK: routes/web.php resuelto) else (echo ERROR)
pause

echo PASO 4: Marcar conflictos como resueltos
git add app/helpers.php routes/web.php
echo.
pause

echo PASO 5: Hacer commit del merge
git commit -m "fix: resolve merge conflicts - keep LAN mode with route model binding"
echo.
pause

echo PASO 6: Sincronizar con remoto
git pull
echo.
if %errorlevel% equ 0 (echo OK: Pull completado) else (echo El branch ya esta actualizado)
pause

echo.
echo =============================================
echo CONFLICTOS RESUELTOS EXITOSAMENTE
echo =============================================
echo.
echo Ahora puedes verificar con:
echo   git log --oneline -5
echo.
pause
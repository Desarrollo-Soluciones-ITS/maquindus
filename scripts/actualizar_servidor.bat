@echo off
title Actualizar Servidor Gestor de Archivos
echo =============================================
echo  ACTUALIZAR SERVIDOR DE PRODUCCION
echo =============================================
echo.
echo Pasos para actualizar C:/inetpub/wwwroot/gestor-archivos
echo.
echo =============================================
echo PASO 1: Resolver conflictos de merge (si existen)
echo =============================================
echo.
echo git checkout --theirs app/helpers.php
echo git checkout --theirs routes/web.php
echo git add app/helpers.php routes/web.php
echo.
echo =============================================
echo PASO 2: Obtener ultimos cambios
echo =============================================
echo.
echo git commit -m "fix: resolve merge conflicts"
echo git pull
echo.
echo =============================================
echo PASO 3: Limpiar cache de Laravel
echo =============================================
echo.
echo php artisan route:clear
echo php artisan view:clear
echo php artisan config:clear
echo.
echo =============================================
echo  LISTO - Servidor actualizado
echo =============================================
echo.
pause
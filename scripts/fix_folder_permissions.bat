@echo off
setlocal EnableExtensions DisableDelayedExpansion
title Permisos de lectura para clientes - Maquindus

set "NEW_ROOT=F:\data\Gestor de Bases de Datos\private"

echo.
echo Aplicando permiso NTFS de lectura a los clientes en:
echo   %NEW_ROOT%
echo.

icacls "%NEW_ROOT%" /grant "*S-1-1-0:(OI)(CI)RX"
if errorlevel 1 (
    echo.
    echo [ERROR] No se pudieron aplicar los permisos. Ejecuta como Administrador.
    exit /b 1
)

echo.
echo Listo. Los clientes ya pueden abrir \\192.168.0.4\private desde el Explorador.
echo No hace falta reciclar IIS ni reiniciar el share.
echo.
endlocal

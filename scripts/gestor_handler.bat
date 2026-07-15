@echo off
REM ============================================
REM gestor_handler.bat - Protocolo gestor://
REM ============================================
REM USO:
REM   gestor://select?path=\\192.168.0.4\private\...
REM   gestor://open?path=\\...
REM
REM Este script es llamado por cmd.exe con la URL completa:
REM   cmd /c gestor_handler.bat "gestor://select/?path=\\..."
REM
REM Extrae la ruta y ejecuta explorer.
REM ============================================

setlocal enabledelayedexpansion

REM Obtener el argumento completo (la URL con %1)
set "url=%~1"

REM Si no hay %1, concatenar todos los argumentos
if "%url%"=="" (
    set "url=%*"
)

REM Buscar "path=" y extraer todo lo que sigue
set "suffix="
set "search=path="
call set "suffix=%%url:*%search%=%%"

if "%suffix%"=="" (
    echo ERROR: No se encontro path= en la URL
    pause
    exit /b 1
)

REM Ahora suffix tiene: \\192.168.0.4\private\Equipos\...\archivo%29.mp4
REM Quitar espacios al inicio
for /f "tokens=* delims= " %%a in ("%suffix%") do set "suffix=%%a"

REM explorer puede manejar espacios y caracteres especiales con comillas
start "" explorer /select,"%suffix%"

exit /b 0
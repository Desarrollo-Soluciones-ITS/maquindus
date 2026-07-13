@echo off
title Instalacion Gestor de Archivos - Cliente LAN
color 0A
setlocal enabledelayedexpansion

echo ===============================================
echo    Gestor de Archivos - Instalacion Cliente LAN
echo ===============================================
echo.
echo Este script instalara el servicio para abrir
echo carpetas desde el navegador web en esta PC.
echo.
echo Requiere permisos de Administrador.
echo.

:: Verificar si se esta ejecutando como administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Debes ejecutar este script como Administrador.
    echo        Haz clic derecho y selecciona "Ejecutar como administrador".
    echo.
    pause
    exit /b 1
)

:: ============================================
:: CONFIGURACION - Editar segun el entorno
:: ============================================

:: Ruta UNC del servidor (recurso compartido SMB)
set "SERVER_UNC=\\%COMPUTERNAME%\private"

:: Carpeta local donde se copiaran los scripts
set "SCRIPTS_DIR=%USERPROFILE%\scripts"

:: Ubicacion del script original (donde esta este batch)
set "SOURCE_DIR=%~dp0"

:: ============================================
:: PASO 1: Crear carpeta de scripts
:: ============================================
echo.
echo [1/4] Preparando carpeta de scripts...

if not exist "%SCRIPTS_DIR%" mkdir "%SCRIPTS_DIR%"
echo    + Carpeta: %SCRIPTS_DIR%

:: ============================================
:: PASO 2: Copiar los archivos necesarios
:: ============================================
echo.
echo [2/4] Copiando archivos...

:: Copiar el folder.php para cliente (convierte C:\ a UNC)
if exist "%SOURCE_DIR%client_folder.php" (
    copy /Y "%SOURCE_DIR%client_folder.php" "%SCRIPTS_DIR%\folder.php" >nul
    echo    + folder.php (cliente) copiado
) else (
    echo    [WARN] No se encontro client_folder.php
)

:: Copiar el script para iniciar el servidor
if exist "%SOURCE_DIR%start-user-server.bat" (
    copy /Y "%SOURCE_DIR%start-user-server.bat" "%SCRIPTS_DIR%\start-server.bat" >nul
    echo    + start-server.bat copiado
) else (
    echo    [WARN] No se encontro start-user-server.bat
)

:: ============================================
:: PASO 3: Registrar tarea programada
:: ============================================
echo.
echo [3/4] Creando tarea programada...

:: Crear tarea para iniciar el servidor al iniciar sesion
schtasks /Create /F /SC ONLOGON /TN "GestorArchivos-Cliente" /TR "\"%SCRIPTS_DIR%\start-server.bat\"" /RL HIGHEST /IT >nul 2>&1

if %errorLevel% equ 0 (
    echo    + Tarea "GestorArchivos-Cliente" creada correctamente
    echo    + Se iniciara automaticamente al iniciar sesion
) else (
    echo    [WARN] No se pudo crear la tarea programada.
    echo    Puedes iniciar el servidor manualmente con:
    echo    "%SCRIPTS_DIR%\start-server.bat"
)

:: ============================================
:: PASO 4: Iniciar el servidor ahora
:: ============================================
echo.
echo [4/4] Iniciando servidor...

start "" "%SCRIPTS_DIR%\start-server.bat"

:: Esperar un momento para que inicie
timeout /t 3 /nobreak >nul

:: Verificar que este corriendo
tasklist /FI "IMAGENAME eq php.exe" 2>nul | find /I "php.exe" >nul
if %errorLevel% equ 0 (
    echo    + Servidor PHP iniciado correctamente
) else (
    echo    [WARN] No se pudo iniciar el servidor PHP
    echo    Puedes iniciarlo manualmente con:
    echo    "%SCRIPTS_DIR%\start-server.bat"
)

:: ============================================
:: VERIFICACION FINAL
:: ============================================
echo.
echo ===============================================
echo    INSTALACION COMPLETADA
echo ===============================================
echo.
echo Resumen:
echo   - Scripts instalados en: %SCRIPTS_DIR%
echo   - Tarea programada: GestorArchivos-Cliente
echo   - Puerto: 8970
echo   - Ruta UNC: %SERVER_UNC%
echo.
echo PRUEBA DE FUNCIONAMIENTO:
echo   1. Abre el navegador y entra al sistema
echo   2. Haz clic en "Ver en carpeta"
echo   3. El explorador se abrira en esta PC
echo.
echo Para verificar el servidor ahora:
echo   Abre http://127.0.0.1:8970/folder.php?path=.
echo   en el navegador de esta PC.
echo.
echo Para desinstalar:
echo   schtasks /Delete /TN "GestorArchivos-Cliente" /F
echo   rmdir /s /q "%SCRIPTS_DIR%"
echo.
pause
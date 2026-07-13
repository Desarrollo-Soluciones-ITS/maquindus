@echo off
title Instalacion Gestor de Archivos - Cliente LAN
color 0A
setlocal enabledelayedexpansion

echo ===============================================
echo    Gestor de Archivos - Instalacion Cliente LAN
echo ===============================================
echo.
echo Este script instalara el protocolo gestor://
echo para abrir archivos desde el navegador web.
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

:: ============================================
:: PASO 1: Copiar scripts al usuario
:: ============================================
echo.
echo [1/3] Copiando scripts a %SCRIPTS_DIR%...

if not exist "%SCRIPTS_DIR%" mkdir "%SCRIPTS_DIR%"

:: Copiar el manejador de PowerShell
if exist "%~dp0gestor_handler.ps1" (
    copy /Y "%~dp0gestor_handler.ps1" "%SCRIPTS_DIR%\gestor_handler.ps1" >nul
    echo    + gestor_handler.ps1 copiado
) else (
    echo    [WARN] gestor_handler.ps1 no encontrado en %~dp0
)

:: ============================================
:: PASO 2: Registrar el protocolo gestor://
:: ============================================
echo.
echo [2/3] Registrando protocolo gestor://...

:: Crear archivo .reg temporal con las rutas correctas del usuario
set "REG_FILE=%TEMP%\gestor_install.reg"

(
    echo Windows Registry Editor Version 5.00
    echo.
    echo [HKEY_CLASSES_ROOT\gestor]
    echo @="URL:Gestor de Archivos Protocol"
    echo "URL Protocol"=""
    echo.
    echo [HKEY_CLASSES_ROOT\gestor\shell]
    echo @="open"
    echo.
    echo [HKEY_CLASSES_ROOT\gestor\shell\open]
    echo @="Abrir en el explorador de archivos"
    echo.
    echo [HKEY_CLASSES_ROOT\gestor\shell\open\command]
    echo @="powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File \"%SCRIPTS_DIR%\gestor_handler.ps1\" -action \"select\" -path \"%%1\""
) > "%REG_FILE%"

:: Aplicar el registro
regedit /s "%REG_FILE%"
if %errorLevel% equ 0 (
    echo    + Protocolo gestor:// registrado correctamente
) else (
    echo    [ERROR] No se pudo registrar el protocolo
    pause
    exit /b 1
)

:: Limpiar archivo temporal
del "%REG_FILE%" >nul 2>&1

:: ============================================
:: PASO 3: Verificar instalacion
:: ============================================
echo.
echo [3/3] Verificando instalacion...

:: Verificar que el script exista
if exist "%SCRIPTS_DIR%\gestor_handler.ps1" (
    echo    + Script manejador: OK
) else (
    echo    [ERROR] Script manejador no encontrado
)

:: Verificar que el protocolo este registrado
reg query HKCR\gestor >nul 2>&1
if %errorLevel% equ 0 (
    echo    + Protocolo registrado: OK
) else (
    echo    [ERROR] Protocolo no registrado
)

echo.
echo ===============================================
echo    INSTALACION COMPLETADA EXITOSAMENTE
echo ===============================================
echo.
echo Ahora puedes hacer clic en "Ver en carpeta" 
echo desde cualquier PC de la red y se abrira
echo el Explorador de Windows con el archivo.
echo.
echo Para desinstalar:
echo   reg delete HKCR\gestor /f
echo   del "%SCRIPTS_DIR%\gestor_handler.ps1"
echo.
pause
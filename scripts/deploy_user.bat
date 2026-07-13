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
echo NO requiere PHP - solo PowerShell.
echo.
echo Requiere permisos de Administrador.
echo.

:: Verificar administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Debes ejecutar como Administrador.
    pause
    exit /b 1
)

set "SCRIPTS_DIR=%USERPROFILE%\scripts"

:: ============================================
:: PASO 1: Crear carpeta de scripts
:: ============================================
echo.
echo [1/3] Preparando carpeta de scripts...
if not exist "%SCRIPTS_DIR%" mkdir "%SCRIPTS_DIR%"
echo    + Carpeta: %SCRIPTS_DIR%

:: ============================================
:: PASO 2: Copiar gestor_handler.ps1
:: ============================================
echo.
echo [2/3] Copiando gestor_handler.ps1...

if exist "%~dp0gestor_handler.ps1" (
    copy /Y "%~dp0gestor_handler.ps1" "%SCRIPTS_DIR%\gestor_handler.ps1" >nul
    if %errorLevel% equ 0 (
        echo    + gestor_handler.ps1 copiado correctamente
    ) else (
        echo    [ERROR] No se pudo copiar gestor_handler.ps1
        pause
        exit /b 1
    )
) else (
    echo    [ERROR] No se encontro gestor_handler.ps1 en %~dp0
    echo    Buscalo en: \\192.168.0.4\gestor-archivos\scripts\gestor_handler.ps1
    pause
    exit /b 1
)

:: ============================================
:: PASO 3: Registrar protocolo gestor://
:: ============================================
echo.
echo [3/3] Registrando protocolo gestor:// en Windows...

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
echo @="powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File \"%SCRIPTS_DIR:\=\\%\\gestor_handler.ps1\" -action \"select\" -path \"%%1\""
) > "%REG_FILE%"

regedit /s "%REG_FILE%"
if %errorLevel% equ 0 (
    echo    + Protocolo gestor:// registrado correctamente
) else (
    echo    [ERROR] No se pudo registrar el protocolo
    pause
    exit /b 1
)

del "%REG_FILE%" >nul 2>&1

:: ============================================
:: VERIFICACION
:: ============================================
echo.
echo ===============================================
echo    INSTALACION COMPLETADA EXITOSAMENTE
echo ===============================================
echo.
echo Resumen:
echo   - Script: %SCRIPTS_DIR%\gestor_handler.ps1
echo   - Protocolo: gestor:// registrado en Windows
echo.
echo PRUEBA DE FUNCIONAMIENTO:
echo   1. Abre Chrome/Edge y escribe en la barra:
echo      gestor://select?path=\\192.168.0.4\private\Equipos
echo   2. El navegador preguntara: "Abrir gestor://?" - Aceptar
echo   3. Deberia abrirse el Explorador en la carpeta del servidor
echo.
echo Para desinstalar:
echo   reg delete HKCR\gestor /f
echo   del "%SCRIPTS_DIR%\gestor_handler.ps1"
echo.
pause
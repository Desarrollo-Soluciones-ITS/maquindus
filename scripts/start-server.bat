@echo off
echo ============================================
echo Iniciando servidor para abrir carpetas...
echo ============================================

set "SCRIPTS_DIR=%~dp0"

REM Buscar PHP en rutas comunes
set PHP_PATH=

if exist "C:\laragon\bin\php\php8.3.0\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.3.0\php.exe
if exist "C:\laragon\bin\php\php8.2\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.2\php.exe
if exist "C:\laragon\bin\php\php8.1\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.1\php.exe
if exist "C:\laragon\bin\php\php8.0\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.0\php.exe
if exist "C:\php\php.exe" set PHP_PATH=C:\php\php.exe
if exist "C:\PHP\php.exe" set PHP_PATH=C:\PHP\php.exe
if exist "C:\Program Files\php\php.exe" set PHP_PATH=C:\Program Files\php\php.exe

if "%PHP_PATH%"=="" (
    echo [ERROR] No se encontro PHP en las rutas comunes.
    echo Busca manualmente donde esta php.exe y edita este archivo.
    pause
    exit /b 1
)

REM Obtener la ruta real desde config/filesystems.php (.env)
for /f "usebackq delims=" %%A in (`"%PHP_PATH%" "%~dp0print_storage_root.php"`) do set "SHELL_SHARE_ROOT=%%A"

if "%SHELL_SHARE_ROOT%"=="" (
    echo [ERROR] No se pudo obtener STORAGE_LOCAL_PATH/STORAGE_ROOT del .env
    pause
    exit /b 1
)

echo Usando PHP: %PHP_PATH%
echo Storage root: %SHELL_SHARE_ROOT%
echo Directorio scripts: %SCRIPTS_DIR%
echo Puerto: 8970
echo.
start /B "" "%PHP_PATH%" -S 127.0.0.1:8970 -t "%SCRIPTS_DIR%"
echo.
echo Servidor iniciado en segundo plano.
echo Para verificar: http://127.0.0.1:8970/folder.php?path=.
echo.
pause

@echo off
echo ===============================================
echo Iniciando servidor para abrir carpetas (CLIENTE)
echo ===============================================

set SCRIPTS_DIR=%USERPROFILE%\scripts

REM Ruta UNC del servidor (recurso compartido SMB)
set SHELL_SHARE_ROOT=\\192.168.0.4\private

REM Buscar PHP en rutas comunes
set PHP_PATH=

if exist "C:\laragon\bin\php\php8.3.0\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.3.0\php.exe
if exist "C:\laragon\bin\php\php8.2\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.2\php.exe
if exist "C:\laragon\bin\php\php8.1\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.1\php.exe
if exist "C:\laragon\bin\php\php8.0\php.exe" set PHP_PATH=C:\laragon\bin\php\php8.0\php.exe
if exist "C:\php\php.exe" set PHP_PATH=C:\php\php.exe
if exist "C:\PHP\php.exe" set PHP_PATH=C:\PHP\php.exe
if exist "C:\Program Files\php\php.exe" set PHP_PATH=C:\Program Files\php\php.exe

REM Buscar en xampp/wamp
if exist "C:\xampp\php\php.exe" set PHP_PATH=C:\xampp\php\php.exe
if exist "C:\wamp64\bin\php\php8.2.0\php.exe" set PHP_PATH=C:\wamp64\bin\php\php8.2.0\php.exe

if "%PHP_PATH%"=="" (
    echo [ERROR] No se encontro PHP en las rutas comunes.
    echo Instala PHP desde: https://windows.php.net/download/
    echo o usa el instalador completo deploy_user.bat
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
exit /b 0
@echo off
REM gestor_handler.bat
REM Llamado por Windows via: cmd /c gestor_handler.bat "gestor://select/?path=\\..."
setlocal enabledelayedexpansion

REM Extraer todo lo que viene despues de "path="
set "url=%*"
set "sufijo=!url:*path=!"

REM Si no tiene path=, salir
if "!sufijo!"=="!url!" exit /b 1

REM Quitar el signo =
if "!sufijo:~0,1!"=="=" set "sufijo=!sufijo:~1!"

REM Quitar comillas al inicio si las hay
if "!sufijo:~0,1!"==""^" set "sufijo=!sufijo:~1!"

REM ejecutar explorer con la ruta extraida
explorer /select,"!sufijo!"
@echo off
setlocal EnableExtensions DisableDelayedExpansion
title Instalar protocolo gestor:// - Maquindus

REM Instalar en la PC del cliente con el usuario que abre el navegador.
REM No necesita Administrador: se registra en HKCU.

set "HANDLER_DIR=%LOCALAPPDATA%\GestorArchivos"
set "HANDLER=%HANDLER_DIR%\gestor_handler.ps1"
set "B64=%TEMP%\gestor_handler_payload.b64"

if not exist "%HANDLER_DIR%" mkdir "%HANDLER_DIR%"
if exist "%B64%" del "%B64%" >nul 2>&1
>> "%B64%" echo cGFyYW0oCiAgICBbc3RyaW5nXSRwYXRoID0gIiIsCiAgICBbc3RyaW5nXSRhY3Rpb24gPSAic2VsZWN0IgopCgojIExvZwoiPT09ICQoR2V0LURhdGUgLUZvcm1hdCAnSEg6bW06c3MnKSA9PT0iIHwgT3V0LUZpbGUgIiRlbnY6VEVNUFxnZXN0b3JfZGVidWcubG9nIgoicGF0aF9yZWNpYmlkbz0kcGF0aCIgfCBPdXQtRmlsZSAtQXBwZW5kICIkZW52OlRFTVBcZ2VzdG9yX2RlYnVnLmxvZyIKImFjdGlvbj0kYWN0aW9uIiB8IE91dC1GaWxlIC1BcHBlbmQgIiRlbnY6VEVNUFxnZXN0b3JfZGVidWcubG9nIgoKIyBFeHRyYWVyIHRvZG8gbG8gcXVlIHZpZW5lIGRlc3B1ZXMgZGUgInBhdGg9IiBlbiBsYSBVUkwKJGkgPSAkcGF0aC5JbmRleE9mKCJwYXRoPSIpCmlmICgkaSAtbHQgMCkgeyAiRVJST1I6IG5vIHNlIGVuY29udHJvIHBhdGg9IiB8IE91dC1GaWxlIC1BcHBlbmQgIiRlbnY6VEVNUFxnZXN0b3JfZGVidWcubG9nIjsgZXhpdCAxIH0KCiRydXRhID0gJHBhdGguU3Vic3RyaW5nKCRpICsgNSkKInJ1dGFfZXh0cmFpZGE9JHJ1dGEiIHwgT3V0LUZpbGUgLUFwcGVuZCAiJGVudjpURU1QXGdlc3Rvcl9kZWJ1Zy5sb2ciCgojIERlY29kaWZpY2FyIFVSTCBjb21wbGV0YSAoaW5jbHV5ZSBhY2VudG9zLCDDsSwgJSwgZXRjLikKIyAxLiBQcmltZXJvICsgLT4gZXNwYWNpbyAoVW5lc2NhcGVEYXRhU3RyaW5nIG5vIGxvIGhhY2UpCiRydXRhID0gJHJ1dGEgLXJlcGxhY2UgJ1wrJywgJyAnCiMgMi4gRGVjb2RpZmljYXIgVE9E
>> "%B64%" echo T1MgbG9zICVYWCAoaW5jbHV5ZSAlNUMsICUyMCwgJUMzJUExLCAlQzMlQjEsIGV0Yy4pCiRydXRhID0gW1N5c3RlbS5VcmldOjpVbmVzY2FwZURhdGFTdHJpbmcoJHJ1dGEpCiMgMy4gLyAtPiBcCiRydXRhID0gJHJ1dGEgLXJlcGxhY2UgJ1svXScsICdcJwoicnV0YV9kZWNvZGlmaWNhZGE9JHJ1dGEiIHwgT3V0LUZpbGUgLUFwcGVuZCAiJGVudjpURU1QXGdlc3Rvcl9kZWJ1Zy5sb2ciCgojIE9idGVuZXIgY2FycGV0YSBjb250ZW5lZG9yYQokY2FycGV0YSA9IFtTeXN0ZW0uSU8uUGF0aF06OkdldERpcmVjdG9yeU5hbWUoJHJ1dGEpCiJjYXJwZXRhPSRjYXJwZXRhIiB8IE91dC1GaWxlIC1BcHBlbmQgIiRlbnY6VEVNUFxnZXN0b3JfZGVidWcubG9nIgoKIyBBYnJpciBjYXJwZXRhIChpbnRlbnRhIC9zZWxlY3QsIGZhbGxiYWNrIGEgY2FycGV0YSkKdHJ5IHsgU3RhcnQtUHJvY2VzcyBleHBsb3JlciAtQXJndW1lbnRMaXN0ICIvc2VsZWN0LGAiJHJ1dGFgIiIgLUVycm9yQWN0aW9uIFN0b3A7ICJPSzogZXhwbG9yZXIgL3NlbGVjdCIgfCBPdXQtRmlsZSAtQXBwZW5kICIkZW52OlRFTVBcZ2VzdG9yX2RlYnVnLmxvZyI7IGV4aXQgMCB9CmNhdGNoIHsgCiAgICAiRkFMTEJBQ0s6IC9zZWxlY3QgZmFsbG86ICRfIiB8IE91dC1GaWxlIC1BcHBlbmQgIiRlbnY6VEVNUFxnZXN0b3JfZGVidWcubG9nIgogICAgdHJ5IHsgU3RhcnQtUHJvY2VzcyBleHBsb3JlciAtQXJndW1lbnRMaXN0ICJgIiRjYXJwZXRhYCIiIC1FcnJvckFj
>> "%B64%" echo dGlvbiBTdG9wOyAiT0s6IGV4cGxvcmVyIGNhcnBldGEiIHwgT3V0LUZpbGUgLUFwcGVuZCAiJGVudjpURU1QXGdlc3Rvcl9kZWJ1Zy5sb2ciOyBleGl0IDAgfQogICAgY2F0Y2ggeyAiRVJST1I6ICRfLCBjYXJwZXRhPSRjYXJwZXRhIiB8IE91dC1GaWxlIC1BcHBlbmQgIiRlbnY6VEVNUFxnZXN0b3JfZGVidWcubG9nIjsgZXhpdCAxIH0KfQ==
powershell.exe -NoProfile -Command "$d=[IO.File]::ReadAllText('%B64%'); [IO.File]::WriteAllBytes('%HANDLER%',[Convert]::FromBase64String($d))"
if errorlevel 1 (
    echo [ERROR] No se pudo escribir %HANDLER%
    del "%B64%" >nul 2>&1
    exit /b 1
)
del "%B64%" >nul 2>&1

reg add "HKCU\Software\Classes\gestor" /ve /d "URL:Gestor de Archivos Protocol" /f >nul
reg add "HKCU\Software\Classes\gestor" /v "URL Protocol" /d "" /f >nul
reg add "HKCU\Software\Classes\gestor\shell" /ve /d "open" /f >nul
reg add "HKCU\Software\Classes\gestor\shell\open" /ve /d "Abrir en el explorador de archivos" /f >nul
reg add "HKCU\Software\Classes\gestor\shell\open\command" /ve /d "powershell.exe -NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File \"%HANDLER%\" -action \"select\" -path \"%%1\"" /f >nul
if errorlevel 1 (
    echo [ERROR] No se pudo registrar el protocolo gestor://
    exit /b 1
)

echo.
echo LISTO. Protocolo gestor:// instalado para el usuario actual.
echo Handler: %HANDLER%
echo.
echo Prueba en el navegador:
echo   gestor://select?path=%%5C%%5C192.168.0.4%%5Cprivate
echo.
reg query "HKCU\Software\Classes\gestor\shell\open\command" /ve
echo.
endlocal

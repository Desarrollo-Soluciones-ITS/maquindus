# Instalación en Clientes LAN

## Guía paso a paso para configurar el botón "Ver en carpeta" en PCs de la red

---

## Índice

1. [Requisitos](#1-requisitos)
2. [Instalación Rápida](#2-instalación-rápida)
3. [Instalación Manual Paso a Paso](#3-instalación-manual-paso-a-paso)
4. [Verificación de Funcionamiento](#4-verificación-de-funcionamiento)
5. [Solución de Problemas](#5-solución-de-problemas)
6. [Actualización](#6-actualización)
7. [Desinstalación](#7-desinstalación)
8. [Preguntas Frecuentes](#8-preguntas-frecuentes)

---

## 1. Requisitos

### 1.1. Requisitos del PC Cliente

| Requisito | Detalle |
|---|---|
| **Sistema Operativo** | Windows 10 u 11 |
| **PowerShell** | 5.0 o superior (viene instalado con Windows) |
| **PHP** | **NO REQUERIDO** |
| **Red** | Acceso al recurso compartido `\\192.168.0.4\private` |
| **Permisos** | Ser **Administrador** de la PC para la instalación |

### 1.2. Requisitos del Servidor

| Requisito | Detalle |
|---|---|
| **Carpeta compartida** | `\\192.168.0.4\gestor-archivos\scripts` debe ser accesible desde la red |
| **Protocolo SMB** | Cliente SMB habilitado en Windows (viene activado por defecto) |

### 1.3. Puertos Necesarios

| Puerto | Dirección | Propósito |
|---|---|---|
| 445 (SMB) | Cliente → Servidor | Acceso a la carpeta compartida |
| 81 (HTTPS) | Cliente → Servidor | Acceso a la aplicación Laravel |

---

## 2. Instalación Rápida

### 2.1. Un Solo Comando

En el PC cliente, abre **CMD como Administrador** y ejecuta:

```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

**Esto es todo lo que necesitas.** El instalador hace lo siguiente automáticamente:

1. Crea la carpeta `%USERPROFILE%\scripts\`
2. Copia el archivo `gestor_handler.ps1`
3. Registra el protocolo `gestor://` en Windows

**Tiempo estimado:** 30 segundos.

### 2.2. Instalación desde USB

Si no tienes acceso a la carpeta de red, copia la carpeta `scripts` desde el servidor a una USB y ejecuta:

```cmd
D:\scripts\deploy_user.bat
```

(Siendo `D:` la letra de la USB)

---

## 3. Instalación Manual Paso a Paso

### 3.1. Abrir CMD como Administrador

**Método 1:** Botón Inicio → escribir `cmd` → clic derecho → "Ejecutar como administrador"

**Método 2:** `Win + X` → "Terminal (Administrador)"

**Método 3:** `Win + R` → escribir `cmd` → `Ctrl + Shift + Enter`

### 3.2. Ejecutar el Instalador

```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

### 3.3. Lo que Verás

```
===============================================
   Gestor de Archivos - Instalacion Cliente LAN
===============================================

Este script instalara el protocolo gestor://
para abrir archivos desde el navegador web.
NO requiere PHP - solo PowerShell.

Requiere permisos de Administrador.


[1/3] Preparando carpeta de scripts...
   + Carpeta: C:\Users\tu_usuario\scripts

[2/3] Copiando gestor_handler.ps1...
   + gestor_handler.ps1 copiado correctamente

[3/3] Registrando protocolo gestor:// en Windows...
   + Protocolo gestor:// registrado correctamente

===============================================
   INSTALACION COMPLETADA EXITOSAMENTE
===============================================
```

### 3.4. Archivos Instalados

Después de la instalación, estos archivos se crean en el PC cliente:

```
C:\Users\tu_usuario\scripts\
    └── gestor_handler.ps1    (17 líneas - 629 bytes)
```

Y esta entrada en el Registro de Windows:

```
HKEY_CLASSES_ROOT\gestor
    → shell\open\command
    → powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass
      -File "C:\Users\tu_usuario\scripts\gestor_handler.ps1" -path "%1"
```

---

## 4. Verificación de Funcionamiento

### 4.1. Prueba Rápida desde el Navegador

En el navegador del PC cliente, escribe en la barra de direcciones:

```
gestor://select/?path=\\192.168.0.4\private\Equipos
```

**Resultado esperado:**

1. El navegador muestra un diálogo: **"Abrir gestor://?"**
2. Haces clic en **"Abrir"** o **"Aceptar"**
3. Se abre el Explorador de Windows en la carpeta `\\192.168.0.4\private\Equipos`

> **Nota:** En algunos navegadores (Edge/Chrome), después de aceptar puede que no pase nada visible. En ese caso, revisa el Paso 4.3.

### 4.2. Prueba desde la Aplicación

1. Abre `https://192.168.0.4:81`
2. Inicia sesión con tu usuario
3. Navega a cualquier equipo que tenga documentos
4. Busca un documento y haz clic en el botón **"Ver en carpeta"** (icono de carpeta)
5. El navegador muestra el diálogo **"Abrir gestor://?"**
6. Acepta

**Resultado esperado:** El Explorador de Windows se abre en la carpeta donde está almacenado el archivo.

### 4.3. Si el Navegador no Muestra el Diálogo

En Chrome/Edge, después del primer clic puede que el protocolo se abra sin preguntar. Para verificar que funcionó:

1. Presiona `Win + E` para abrir el Explorador de Windows
2. Revisa si se abrió una nueva ventana del explorador en la ubicación del archivo

O revisa el log de depuración (Paso 4.4).

### 4.4. Ver el Log de Depuración

Después de hacer clic en "Ver en carpeta", abre PowerShell y ejecuta:

```powershell
notepad $env:TEMP\gestor_debug.log
```

**Log exitoso (debe verse así):**

```
=== HH:mm:ss ===
path_recibido=gestor://select/?path=%5C%5C192.168.0.4%5Cprivate%5C...
ruta_extraida=%5C%5C192.168.0.4%5Cprivate%5C...
ruta_decodificada=\\192.168.0.4\private\Equipos\...
carpeta=\\192.168.0.4\private\Equipos\...
OK: explorer /select
```

**Log con error (ruta no decodificada):**

```
=== HH:mm:ss ===
path_recibido=gestor://select/?path=%5C%5C192.168.0.4%5Cprivate%5C...
ruta_extraida=%5C%5C192.168.0.4%5Cprivate%5C...
ruta_decodificada=\\192.168.0.4\private\Equipos\Taladro+Triturador+...
carpeta=\\192.168.0.4\private\Equipos\Taladro+Triturador+...
OK: explorer /select
```

Si ves `+` en lugar de espacios en `ruta_decodificada`, necesitas actualizar el script (ver [Sección 6](#6-actualización)).

### 4.5. Verificar el Registro de Windows

Para confirmar que el protocolo está registrado correctamente:

```cmd
reg query HKCR\gestor\shell\open\command
```

**Debe mostrar:**
```
HKEY_CLASSES_ROOT\gestor\shell\open\command
    (Predeterminado)    REG_SZ    powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File "C:\Users\tu_usuario\scripts\gestor_handler.ps1" -path "%1"
```

---

## 5. Solución de Problemas

### 5.1. "Acceso denegado" al ejecutar deploy_user.bat

**Causa:** No tienes permisos de Administrador.

**Solución:** Cierra y abre CMD como Administrador:

```
Win + X → "Terminal (Administrador)"
```

### 5.2. "No se encuentra la ruta de red" al ejecutar deploy_user.bat

**Causa:** El PC cliente no puede acceder a la carpeta compartida del servidor.

**Soluciones:**

1. Verificar conectividad con el servidor:
   ```cmd
   ping 192.168.0.4
   ```

2. Verificar acceso a la carpeta compartida:
   ```cmd
   net use \\192.168.0.4\gestor-archivos
   ```

3. Si pide credenciales, usar:
   ```cmd
   net use \\192.168.0.4\gestor-archivos /user:MAQUINDUSVE\tu_usuario
   ```

4. Como alternativa, copiar la carpeta `scripts` a una USB y ejecutar localmente.

### 5.3. El explorador se abre pero muestra "Este equipo"

**Causa:** La ruta UNC es muy larga para `explorer.exe`, o la ruta tiene caracteres no decodificados.

**Solución:**

1. Revisar el log (ver [Paso 4.4](#44-ver-el-log-de-depuración))
2. Si la ruta decodificada contiene `+` en lugar de espacios, actualizar el script (ver [Sección 6](#6-actualización))
3. Si la ruta es correcta pero aun así abre "Este equipo", ejecutar el script PowerShell manualmente para diagnosticar:
   ```powershell
   & "$env:USERPROFILE\scripts\gestor_handler.ps1" -path "gestor://select/?path=\\192.168.0.4\private\Equipos"
   ```

### 5.4. El navegador dice "No se puede abrir esta página"

**Causa:** El protocolo `gestor://` no está registrado o el script no se encuentra.

**Solución:**

1. Verificar que el script existe:
   ```cmd
   dir "%USERPROFILE%\scripts\gestor_handler.ps1"
   ```

2. Verificar el registro de Windows:
   ```cmd
   reg query HKCR\gestor\shell\open\command
   ```

3. Re-ejecutar `deploy_user.bat` como Administrador.

### 5.5. El botón "Ver en carpeta" no aparece en la aplicación

**Causa:** El archivo no tiene una ruta válida o la configuración del servidor no incluye `STORAGE_NETWORK_PATH`.

**Solución:** Esto es un problema del servidor, no del cliente. Verificar en el servidor:

- Que el documento tenga al menos un archivo (versión) subido
- Que `STORAGE_NETWORK_PATH` esté definido en el `.env`
- Que `STORAGE_LOCAL_PATH` apunte a la ruta correcta

### 5.6. Error 500 al hacer clic en "Ver en carpeta"

**Causa:** Problema de Livewire o base de datos en el servidor.

**Solución:** Reportar al administrador del servidor. Verificar:

- MySQL corriendo
- Caché de Laravel limpia
- Logs en `storage/logs/laravel.log`

### 5.7. El script PowerShell no se ejecuta (política de ejecución)

En algunos PCs, PowerShell puede tener restringida la ejecución de scripts.

**Solución:** El instalador `deploy_user.bat` ya incluye `-ExecutionPolicy Bypass` en el registro, lo que evita este problema. Pero si quieres verificarlo:

```powershell
Get-ExecutionPolicy
```

Debe mostrar `RemoteSigned` o `Unrestricted`. Si muestra `Restricted`, ejecutar como Administrador:

```powershell
Set-ExecutionPolicy RemoteSigned -Scope LocalMachine
```

---

## 6. Actualización

### 6.1. ¿Cuándo Actualizar?

Cada vez que se realicen cambios en el script `gestor_handler.ps1` en el servidor, los clientes deben actualizarse.

### 6.2. Cómo Actualizar

Simplemente ejecuta el instalador de nuevo (sobrescribe el script existente):

```cmd
\\192.168.0.4\gestor-archivos\scripts\deploy_user.bat
```

### 6.3. Verificar la Versión Instalada

Revisa la fecha del archivo:

```cmd
dir "%USERPROFILE%\scripts\gestor_handler.ps1"
```

Compáralo con la fecha del archivo en el servidor:
```cmd
dir \\192.168.0.4\gestor-archivos\scripts\gestor_handler.ps1
```

Si la fecha del servidor es más reciente, necesitas actualizar.

---

## 7. Desinstalación

### 7.1. Desinstalación Completa

Abre **CMD como Administrador** y ejecuta:

```cmd
reg delete HKCR\gestor /f
del "%USERPROFILE%\scripts\gestor_handler.ps1"
```

### 7.2. Verificar Desinstalación

```cmd
reg query HKCR\gestor
```

Debe mostrar: `ERROR: The system was unable to find the specified registry key or value.`

---

## 8. Preguntas Frecuentes

### ¿Requiere PHP en el cliente?

**No.** El sistema usa PowerShell, que viene instalado en todas las versiones modernas de Windows (10 y 11).

### ¿Requiere configuración de red especial?

Solo necesita acceso al recurso compartido `\\192.168.0.4\private` (puerto 445 SMB). Esto normalmente ya está configurado en redes empresariales.

### ¿Funciona con cualquier navegador?

Sí. Chrome, Edge, Firefox y Opera soportan protocolos personalizados como `gestor://`.

### ¿Qué pasa si el usuario no tiene permisos de Administrador?

La instalación requiere permisos de Administrador **una sola vez**. Después de instalado, cualquier usuario de la PC puede usar el botón "Ver en carpeta" sin problemas.

### ¿Se actualiza automáticamente?

No. Cuando el script `gestor_handler.ps1` cambie en el servidor, debes ejecutar `deploy_user.bat` de nuevo para actualizar el cliente. No hay actualización automática.

### ¿Funciona con rutas UNC muy largas?

Sí. El script PowerShell usa `Shell.Application.Open()` que es el único método de Windows que maneja correctamente rutas UNC de más de 80 caracteres. `explorer.exe /select` falla con rutas UNC largas (abre "Este equipo"), por eso el script usa Shell COM como método principal.

### ¿Qué hace exactamente el protocolo gestor://?

Cuando haces clic en "Ver en carpeta":

1. **Laravel** genera una URL como `gestor://select?path=\\192.168.0.4\private\...`
2. **Tu navegador** detecta el protocolo `gestor://` y pregunta si quieres abrirlo
3. **Windows** ejecuta PowerShell con el script `gestor_handler.ps1`
4. **PowerShell** extrae la ruta de la URL, la decodifica y abre el Explorador
5. **El Explorador** se abre exactamente en la carpeta donde está el archivo

### ¿Cómo sé que el script se ejecutó correctamente?

Revisa el log de depuración:

```powershell
notepad $env:TEMP\gestor_debug.log
```

Si ves `OK: explorer /select` o `OK: explorer carpeta` al final, funcionó correctamente.

---

*Documentación generada el 16/07/2026 — Versión 1.0*
*Commit: `9646023`*
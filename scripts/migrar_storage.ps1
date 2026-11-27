<#
    Migración completa del almacenamiento privado de Maquindus a una nueva ruta.

    El archivo .bat llama a este script. Todas las rutas configurables vienen de
    variables de entorno que define el .bat.

    El script:
      1. Valida permisos de administrador y rutas.
      2. Hace backup en C:\Temp de la base de datos, .env y carpeta private.
      3. Detiene sitio, Application Pool y servidor auxiliar.
      4. Copia private a la nueva ruta y verifica cantidad/tamaño.
      5. Renombra la carpeta antigua como respaldo.
      6. Reapunta el share "private" a la nueva ruta.
      7. Actualiza STORAGE_LOCAL_PATH / STORAGE_ROOT / STORAGE_NETWORK_PATH / NETWORK_SHARE_ROOT y SHELL_SHARE_ROOT en .env.
      8. Regenera cachés, reinicia IIS y la tarea auxiliar.
      9. Verifica Laravel, share e integridad.
      10. Si algo falla, revierte automáticamente.
#>

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

# ---------------------------------------------------------------------
# Configuración (la define el .bat)
# ---------------------------------------------------------------------

$APP_ROOT    = if ($env:APP_ROOT) { $env:APP_ROOT.TrimEnd('\','/') } else { 'C:\inetpub\wwwroot\gestor-archivos' }
$OLD_ROOT    = if ($env:OLD_ROOT) { $env:OLD_ROOT.TrimEnd('\','/') } else { Join-Path $APP_ROOT 'storage\app\private' }
$NEW_ROOT    = if ($env:NEW_ROOT) { $env:NEW_ROOT.TrimEnd('\','/') } else { '' }
$BACKUP_ROOT = if ($env:BACKUP_ROOT) { $env:BACKUP_ROOT.TrimEnd('\','/') } else { 'C:\Temp\GestorArchivos_Migracion' }
$SHARE_NAME  = if ($env:SHARE_NAME) { $env:SHARE_NAME } else { 'private' }
$NETWORK_PATH = if ($env:NETWORK_PATH) { $env:NETWORK_PATH.TrimEnd('\','/') } else { '\\192.168.0.4\private' }
$SITE_NAME   = if ($env:SITE_NAME) { $env:SITE_NAME } else { 'Gestor de Archivos' }
$POOL_NAME   = if ($env:POOL_NAME) { $env:POOL_NAME } else { 'Gestor de Archivos' }
$TASK_NAME   = if ($env:TASK_NAME) { $env:TASK_NAME } else { 'Gestor de Archivos - Servidor Auxiliar' }
$MYSQLDUMP   = if ($env:MYSQLDUMP) { $env:MYSQLDUMP } else { '' }
$PHP_EXE     = if ($env:PHP_EXE) { $env:PHP_EXE } else { '' }
$APP_POOL_USER = if ($env:APP_POOL_USER) { $env:APP_POOL_USER } else { '' }
$AUTO        = if ($env:AUTO) { $env:AUTO } else { '1' }

$TIMESTAMP = Get-Date -Format 'yyyyMMdd_HHmmss'
$WORK_DIR  = Join-Path $BACKUP_ROOT "MIGRACION_$TIMESTAMP"
$LOG_FILE  = Join-Path $WORK_DIR 'migracion.log'
$DB_DUMP   = Join-Path $WORK_DIR 'base_de_datos.sql'
$ENV_BAK   = Join-Path $WORK_DIR 'env.bak'
$PRIV_BAK  = Join-Path $WORK_DIR 'private'
$OLD_RENAMED_NAME = "private_OLD_$TIMESTAMP"

$appcmd = Join-Path $env:windir 'System32\inetsrv\appcmd.exe'

# Estados para rollback
$servicesStopped = $false
$backupDone = $false
$copyDone = $false
$oldRenamed = $false
$shareChanged = $false
$envChanged = $false
$oldRenamedPath = ''

# ---------------------------------------------------------------------
# Funciones
# ---------------------------------------------------------------------

function Write-Log {
    param(
        [Parameter(Mandatory = $true)][string]$Message,
        [string]$Level = 'INFO'
    )

    if (-not (Test-Path -LiteralPath $WORK_DIR)) {
        New-Item -ItemType Directory -Path $WORK_DIR -Force | Out-Null
    }

    $line = '{0} [{1}] {2}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Level, $Message
    Add-Content -LiteralPath $LOG_FILE -Value $line -Encoding UTF8
    Write-Host $line
}

function Get-EnvValue {
    param(
        [Parameter(Mandatory = $true)][string[]]$Lines,
        [Parameter(Mandatory = $true)][string]$Key,
        [string]$Default = ''
    )

    $line = $Lines | Where-Object { $_ -match "^\s*$Key\s*=" } | Select-Object -Last 1
    if (-not $line) { return $Default }

    $value = ($line -split '=', 2)[1].Trim()
    $value = $value.Trim('"').Trim("'")
    return $value
}

function Set-EnvValue {
    param(
        [Parameter(Mandatory = $true)][string[]]$Lines,
        [Parameter(Mandatory = $true)][string]$Key,
        [Parameter(Mandatory = $true)][string]$Value
    )

    $escaped = $Value.Replace('\', '\\')
    $found = $false

    $result = foreach ($line in $Lines) {
        if ($line -match "^\s*$Key\s*=") {
            $found = $true
            '{0}="{1}"' -f $Key, $escaped
        } else {
            $line
        }
    }

    if (-not $found) {
        $result += '{0}="{1}"' -f $Key, $escaped
    }

    return $result
}

function Get-FolderStats {
    param([Parameter(Mandatory = $true)][string]$Path)

    if (-not (Test-Path -LiteralPath $Path)) {
        return [pscustomobject]@{ Count = [int64]0; Sum = [int64]0 }
    }

    $measure = Get-ChildItem -LiteralPath $Path -Recurse -File -Force -ErrorAction SilentlyContinue |
        Measure-Object -Property Length -Sum

    return [pscustomobject]@{
        Count = [int64]$measure.Count
        Sum   = [int64]$measure.Sum
    }
}

function Get-FreeSpaceBytes {
    param([Parameter(Mandatory = $true)][string]$Path)

    $probe = $Path
    if (-not (Test-Path -LiteralPath $probe)) {
        $probe = Split-Path -Path $probe -Parent
    }

    $drive = $null
    $item = Get-Item -LiteralPath $probe -ErrorAction SilentlyContinue
    if ($item -and $item.PSDrive) { $drive = $item.PSDrive.Name }

    if (-not $drive) {
        $qualifier = Split-Path -Path $Path -Qualifier -ErrorAction SilentlyContinue
        if ($qualifier) { $drive = $qualifier.TrimEnd(':') }
    }

    if (-not $drive) { return $null }

    $psdrive = Get-PSDrive -Name $drive -ErrorAction SilentlyContinue
    if (-not $psdrive) { return $null }

    return [int64]$psdrive.Free
}

function Get-DriveName {
    param([Parameter(Mandatory = $true)][string]$Path)

    $qualifier = Split-Path -Path $Path -Qualifier -ErrorAction SilentlyContinue
    if ($qualifier) { return $qualifier.TrimEnd(':') }

    $item = Get-Item -LiteralPath $Path -ErrorAction SilentlyContinue
    if ($item -and $item.PSDrive) { return $item.PSDrive.Name }

    return ''
}

function Find-Executable {
    param(
        [string]$Explicit,
        [string[]]$Names,
        [string[]]$Paths
    )

    if ($Explicit -and (Test-Path -LiteralPath $Explicit)) { return $Explicit }

    foreach ($name in $Names) {
        $cmd = Get-Command -Name $name -CommandType Application -ErrorAction SilentlyContinue |
            Select-Object -First 1
        if ($cmd) { return $cmd.Path }
    }

    foreach ($path in $Paths) {
        if ($path -and (Test-Path -LiteralPath $path)) { return $path }
    }

    return $null
}

function Invoke-Robocopy {
    param(
        [Parameter(Mandatory = $true)][string]$Source,
        [Parameter(Mandatory = $true)][string]$Destination,
        [Parameter(Mandatory = $true)][string]$Log
    )

    if (-not (Test-Path -LiteralPath $Source)) {
        throw "No existe la ruta de origen: $Source"
    }

    New-Item -ItemType Directory -Path $Destination -Force | Out-Null

    & robocopy $Source $Destination /E /COPY:DAT /DCOPY:DAT /R:2 /W:2 /XJ /NP "/LOG+:$Log" | Out-Null
    $rc = $LASTEXITCODE

    if ($rc -ge 8) {
        throw "robocopy falló con código $rc. Revisa $Log"
    }

    Write-Log "robocopy completado con código $rc : $Source -> $Destination"
}

function Stop-Services {
    Write-Log 'Deteniendo sitio y Application Pool...'
    $script:servicesStopped = $true

    & $appcmd stop site "/site.name:$SITE_NAME" | Out-Null
    & $appcmd stop apppool "/apppool.name:$POOL_NAME" | Out-Null

    & schtasks /End /TN "$TASK_NAME" 2>$null | Out-Null

    try {
        $conns = Get-NetTCPConnection -LocalPort 8970 -State Listen -ErrorAction SilentlyContinue
        foreach ($conn in $conns) {
            Stop-Process -Id $conn.OwningProcess -Force -ErrorAction SilentlyContinue
        }
        if ($conns) {
            Write-Log 'Proceso auxiliar del puerto 8970 detenido.'
        }
    } catch {
        Write-Log "No se pudo inspeccionar el puerto 8970: $($_.Exception.Message)" 'WARN'
    }

    Write-Log 'Sitio y Application Pool detenidos.'
}

function Start-Services {
    Write-Log 'Iniciando sitio y Application Pool...'

    & $appcmd start apppool "/apppool.name:$POOL_NAME" | Out-Null
    & $appcmd recycle apppool "/apppool.name:$POOL_NAME" | Out-Null
    & $appcmd start site "/site.name:$SITE_NAME" | Out-Null
    & schtasks /Run /TN "$TASK_NAME" 2>$null | Out-Null

    Write-Log 'Sitio y Application Pool iniciados.'
}

function Set-ShareToPath {
    param([Parameter(Mandatory = $true)][string]$Path)

    Remove-SmbShare -Name $SHARE_NAME -Force -ErrorAction SilentlyContinue | Out-Null

    try {
        New-SmbShare -Name $SHARE_NAME -Path $Path -ReadAccess 'Everyone' -ErrorAction Stop | Out-Null
    } catch {
        try {
            New-SmbShare -Name $SHARE_NAME -Path $Path -ReadAccess 'Todos' -ErrorAction Stop | Out-Null
        } catch {
            New-SmbShare -Name $SHARE_NAME -Path $Path -ReadAccess '*S-1-1-0' -ErrorAction Stop | Out-Null
        }
    }

    Write-Log "Share '$SHARE_NAME' apuntando a: $Path"
}

function Rebuild-Caches {
    Set-Location -LiteralPath $APP_ROOT

    & $php artisan optimize:clear 2>&1 | Tee-Object -FilePath $LOG_FILE -Append | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'php artisan optimize:clear falló' }

    & $php artisan optimize 2>&1 | Tee-Object -FilePath $LOG_FILE -Append | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'php artisan optimize falló' }

    & $php artisan filament:optimize 2>&1 | Tee-Object -FilePath $LOG_FILE -Append | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'php artisan filament:optimize falló' }

    Write-Log 'Cachés de Laravel/Filament regeneradas.'
}

# ---------------------------------------------------------------------
# Ejecución
# ---------------------------------------------------------------------

try {
    New-Item -ItemType Directory -Path $WORK_DIR -Force | Out-Null
    Write-Log '=== INICIO DE MIGRACIÓN ==='
    Write-Log "Backup: $WORK_DIR"
    Write-Log "Origen: $OLD_ROOT"
    Write-Log "Destino: $NEW_ROOT"

    # Administrador
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($identity)
    if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
        throw 'Este script debe ejecutarse como Administrador.'
    }

    # Validaciones básicas
    if ([string]::IsNullOrWhiteSpace($NEW_ROOT)) {
        throw 'NEW_ROOT no está definido. Edita la variable NEW_ROOT en el .bat.'
    }

    if ($NEW_ROOT -eq $OLD_ROOT) {
        throw 'NEW_ROOT debe ser distinto de OLD_ROOT.'
    }

    if (-not (Test-Path -LiteralPath $APP_ROOT)) { throw "No existe APP_ROOT: $APP_ROOT" }
    if (-not (Test-Path -LiteralPath (Join-Path $APP_ROOT 'artisan'))) { throw "No se encontró artisan en $APP_ROOT" }
    if (-not (Test-Path -LiteralPath (Join-Path $APP_ROOT '.env'))) { throw "No se encontró .env en $APP_ROOT" }
    if (-not (Test-Path -LiteralPath $OLD_ROOT)) { throw "No existe OLD_ROOT: $OLD_ROOT" }
    if (-not (Test-Path -LiteralPath $appcmd)) { throw "No se encontró appcmd.exe: $appcmd" }

    # Ejecutables
    $php = Find-Executable -Explicit $PHP_EXE -Names @('php') -Paths @(
        'C:\php\php.exe',
        'C:\PHP\php.exe',
        'C:\Program Files\php\php.exe',
        'C:\laragon\bin\php\php8.4.15-Win32-vs17-x64\php.exe',
        'C:\laragon\bin\php\php8.3.0\php.exe',
        'C:\laragon\bin\php\php8.2\php.exe',
        'C:\laragon\bin\php\php8.1\php.exe',
        'C:\laragon\bin\php\php8.0\php.exe',
        'C:\xampp\php\php.exe',
        'C:\wamp64\bin\php\php8.2.0\php.exe'
    )
    if (-not $php) { throw 'No se encontró php.exe. Define PHP_EXE en el .bat.' }
    Write-Log "PHP: $php"

    $mysqldump = Find-Executable -Explicit $MYSQLDUMP -Names @('mysqldump') -Paths @(
        'C:\xampp\mysql\bin\mysqldump.exe',
        'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
        'C:\Program Files\MariaDB 10.11\bin\mysqldump.exe'
    )

    if (-not $mysqldump) {
        $searchRoots = @(
            'C:\laragon\bin\mysql',
            'C:\xampp\mysql',
            'C:\wamp64\bin\mysql',
            'C:\Program Files\MySQL',
            'C:\Program Files (x86)\MySQL',
            'C:\Program Files\MariaDB*',
            'C:\Program Files (x86)\MariaDB*',
            'D:\MySQL',
            'D:\MariaDB*'
        )

        foreach ($pattern in $searchRoots) {
            foreach ($root in (Get-Item -Path $pattern -ErrorAction SilentlyContinue)) {
                $found = Get-ChildItem -LiteralPath $root.FullName -Recurse -Filter mysqldump.exe -ErrorAction SilentlyContinue |
                    Select-Object -First 1
                if ($found) { $mysqldump = $found.FullName; break }
            }
            if ($mysqldump) { break }
        }
    }

    if (-not $mysqldump) { throw 'No se encontró mysqldump.exe. Define MYSQLDUMP en el .bat.' }
    Write-Log "mysqldump: $mysqldump"

    # Leer .env
    $envLines = Get-Content -LiteralPath (Join-Path $APP_ROOT '.env')
    $dbHost = Get-EnvValue -Lines $envLines -Key 'DB_HOST' -Default '127.0.0.1'
    $dbPort = Get-EnvValue -Lines $envLines -Key 'DB_PORT' -Default '3306'
    $dbName = Get-EnvValue -Lines $envLines -Key 'DB_DATABASE'
    $dbUser = Get-EnvValue -Lines $envLines -Key 'DB_USERNAME'
    $dbPass = Get-EnvValue -Lines $envLines -Key 'DB_PASSWORD' -Default ''

    if ([string]::IsNullOrWhiteSpace($dbName) -or [string]::IsNullOrWhiteSpace($dbUser)) {
        throw 'No se pudieron leer DB_DATABASE / DB_USERNAME del .env.'
    }
    Write-Log "BD: $dbName @ $($dbHost):$dbPort"

    # Espacio
    $oldStats = Get-FolderStats -Path $OLD_ROOT
    Write-Log "Private actual: $($oldStats.Count) archivos, $($oldStats.Sum) bytes"

    $backupFree = Get-FreeSpaceBytes -Path $BACKUP_ROOT
    $newFree = Get-FreeSpaceBytes -Path $NEW_ROOT
    $need = [int64]$oldStats.Sum + 100MB

    if ($backupFree -ne $null -and $backupFree -lt $need) {
        throw "Espacio insuficiente para backup en $BACKUP_ROOT. Libre: $backupFree, necesario: $need"
    }

    if ($newFree -ne $null) {
        if ((Get-DriveName -Path $BACKUP_ROOT) -eq (Get-DriveName -Path $NEW_ROOT)) {
            $needNew = ([int64]$oldStats.Sum * 2) + 100MB
        } else {
            $needNew = $need
        }
        if ($newFree -lt $needNew) {
            throw "Espacio insuficiente en $NEW_ROOT. Libre: $newFree, necesario: $needNew"
        }
    }

    # Backup de .env
    Copy-Item -LiteralPath (Join-Path $APP_ROOT '.env') -Destination $ENV_BAK -Force
    Write-Log "Backup .env: $ENV_BAK"

    # Backup de archivos private
    Invoke-Robocopy -Source $OLD_ROOT -Destination $PRIV_BAK -Log (Join-Path $WORK_DIR 'robocopy_backup.log')
    $bakStats = Get-FolderStats -Path $PRIV_BAK
    if ($bakStats.Count -ne $oldStats.Count -or $bakStats.Sum -ne $oldStats.Sum) {
        throw "El backup de archivos no coincide con el origen. Origen: $($oldStats.Count)/$($oldStats.Sum); Backup: $($bakStats.Count)/$($bakStats.Sum)"
    }
    Write-Log "Backup private verificado: $($bakStats.Count) archivos, $($bakStats.Sum) bytes"

    # Backup de base de datos
    $dumpArgs = @(
        "--host=$dbHost",
        "--port=$dbPort",
        "--user=$dbUser",
        '--single-transaction',
        '--quick',
        '--routines',
        '--triggers',
        "--result-file=$DB_DUMP",
        $dbName
    )

    if (-not [string]::IsNullOrEmpty($dbPass)) {
        $dumpArgs = @("--password=$dbPass") + $dumpArgs
    }

    & $mysqldump @dumpArgs 2>&1 | Tee-Object -FilePath $LOG_FILE -Append | Out-Null
    if ($LASTEXITCODE -ne 0) { throw "mysqldump falló con código $LASTEXITCODE" }
    if (-not (Test-Path -LiteralPath $DB_DUMP) -or ((Get-Item -LiteralPath $DB_DUMP).Length -le 0)) {
        throw 'El archivo de backup de base de datos está vacío o no existe.'
    }
    Write-Log "Backup BD: $DB_DUMP ($((Get-Item -LiteralPath $DB_DUMP).Length) bytes)"

    $backupDone = $true

    if ($AUTO -ne '1' -and $AUTO.ToLower() -ne 'true') {
        $answer = Read-Host 'Escribe SI para continuar con el corte'
        if ($answer -ne 'SI') {
            Write-Log 'Cancelado por el usuario después del backup.'
            exit 0
        }
    }

    # Preparar destino
    New-Item -ItemType Directory -Path $NEW_ROOT -Force | Out-Null
    & icacls $NEW_ROOT /grant 'IIS_IUSRS:(OI)(CI)M' /T 2>&1 | Tee-Object -FilePath $LOG_FILE -Append | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'No se pudieron aplicar permisos en NEW_ROOT.' }

    if (-not [string]::IsNullOrWhiteSpace($APP_POOL_USER)) {
        & icacls $NEW_ROOT /grant "${APP_POOL_USER}:(OI)(CI)M" /T 2>&1 | Tee-Object -FilePath $LOG_FILE -Append | Out-Null
        if ($LASTEXITCODE -ne 0) { throw "No se pudieron aplicar permisos a $APP_POOL_USER en NEW_ROOT." }
    }

    Write-Log "Permisos aplicados en $NEW_ROOT"

    # Corte
    Stop-Services

    # Copia final
    Invoke-Robocopy -Source $OLD_ROOT -Destination $NEW_ROOT -Log (Join-Path $WORK_DIR 'robocopy_final.log')
    $newStats = Get-FolderStats -Path $NEW_ROOT

    if ($newStats.Count -ne $oldStats.Count -or $newStats.Sum -ne $oldStats.Sum) {
        throw "La copia final no coincide. Origen: $($oldStats.Count)/$($oldStats.Sum); Destino: $($newStats.Count)/$($newStats.Sum)"
    }
    $copyDone = $true
    Write-Log "Copia final verificada: $($newStats.Count) archivos, $($newStats.Sum) bytes"

    # Renombrar antiguo
    $oldParent = Split-Path -Path $OLD_ROOT -Parent
    $oldRenamedPath = Join-Path $oldParent $OLD_RENAMED_NAME
    if (Test-Path -LiteralPath $oldRenamedPath) {
        throw "Ya existe $oldRenamedPath. No se puede renombrar la carpeta antigua."
    }
    Rename-Item -LiteralPath $OLD_ROOT -NewName $OLD_RENAMED_NAME
    $oldRenamed = $true
    Write-Log "Carpeta antigua renombrada a: $oldRenamedPath"

    # Share
    $shareChanged = $true
    Set-ShareToPath -Path $NEW_ROOT

    # .env
    $envLines = Set-EnvValue -Lines $envLines -Key 'STORAGE_LOCAL_PATH' -Value $NEW_ROOT
    $envLines = Set-EnvValue -Lines $envLines -Key 'STORAGE_ROOT' -Value $NEW_ROOT
    $envLines = Set-EnvValue -Lines $envLines -Key 'STORAGE_NETWORK_PATH' -Value $NETWORK_PATH
    $envLines = Set-EnvValue -Lines $envLines -Key 'NETWORK_SHARE_ROOT' -Value $NETWORK_PATH
    $envLines = Set-EnvValue -Lines $envLines -Key 'SHELL_SHARE_ROOT' -Value $NETWORK_PATH

    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    $envChanged = $true
    [IO.File]::WriteAllLines((Join-Path $APP_ROOT '.env'), $envLines, $utf8NoBom)
    Write-Log '.env actualizado con las nuevas rutas.'

    # Cachés y arranque
    Rebuild-Caches
    Start-Services

    # Verificación
    $actualRoot = (& $php scripts/print_storage_root.php 2>$null | Select-Object -Last 1)
    $actualRoot = [string]$actualRoot
    $actualRoot = $actualRoot.Trim().TrimEnd('\','/')
    $expectedRoot = $NEW_ROOT.TrimEnd('\','/')

    if ($actualRoot -ne $expectedRoot) {
        throw "Laravel no usa la ruta esperada. Esperada: $expectedRoot; real: $actualRoot"
    }
    Write-Log "Laravel usa: $actualRoot"

    $share = Get-SmbShare -Name $SHARE_NAME -ErrorAction SilentlyContinue
    if (-not $share) { throw "No se encontró el share $SHARE_NAME" }

    $sharePath = [string]$share.Path
    $sharePath = $sharePath.Trim().TrimEnd('\','/')
    if ($sharePath -ne $expectedRoot) {
        throw "El share no apunta a la ruta esperada. Esperada: $expectedRoot; real: $sharePath"
    }
    Write-Log "Share verificado: $($share.Name) -> $($share.Path)"

    Set-Location -LiteralPath $APP_ROOT
    & $php scripts/audit_storage.php 2>&1 | Tee-Object -FilePath $LOG_FILE -Append | Out-Null

    $summary = Join-Path $WORK_DIR 'RESUMEN.txt'
    @(
        'MIGRACIÓN COMPLETADA',
        "Fecha: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')",
        "Nueva ruta private: $NEW_ROOT",
        "Carpeta antigua: $oldRenamedPath",
        "Backup BD: $DB_DUMP",
        "Backup .env: $ENV_BAK",
        "Backup private: $PRIV_BAK",
        "Log: $LOG_FILE"
    ) | Set-Content -LiteralPath $summary -Encoding UTF8

    Write-Log '=== MIGRACIÓN COMPLETADA CORRECTAMENTE ==='
    Write-Log "Resumen: $summary"
    exit 0

} catch {
    $msg = $_.Exception.Message
    Write-Log "ERROR: $msg" 'ERROR'

    if ($backupDone -and $envChanged) {
        Copy-Item -LiteralPath $ENV_BAK -Destination (Join-Path $APP_ROOT '.env') -Force
        Write-Log '.env restaurado desde backup.' 'WARN'
    }

    if ($oldRenamed -and $oldRenamedPath -and (Test-Path -LiteralPath $oldRenamedPath)) {
        Rename-Item -LiteralPath $oldRenamedPath -NewName 'private' -Force
        Write-Log 'Carpeta antigua restaurada como private.' 'WARN'
    }

    if ($shareChanged) {
        try {
            Set-ShareToPath -Path $OLD_ROOT
            Write-Log 'Share restaurado a la ruta antigua.' 'WARN'
        } catch {
            Write-Log "No se pudo restaurar el share: $($_.Exception.Message)" 'ERROR'
        }
    }

    if ($envChanged) {
        try { Rebuild-Caches } catch { Write-Log "Error regenerando cachés en rollback: $($_.Exception.Message)" 'ERROR' }
    }

    if ($servicesStopped) {
        try { Start-Services } catch { Write-Log "Error reiniciando servicios en rollback: $($_.Exception.Message)" 'ERROR' }
    }

    Write-Log '=== ROLLBACK FINALIZADO ===' 'WARN'
    Write-Log "Revisa el log: $LOG_FILE" 'WARN'
    exit 1
}

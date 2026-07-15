<#
.SYNOPSIS
    Manejador del protocolo gestor://
    Abre el Explorador de Windows en la ruta UNC del archivo.
.DESCRIPTION
    Invocado por el navegador via protocolo gestor:// registrado en Windows.
    Recibe la URL completa como parametro -path:
        powershell ... -path "gestor://select?path=\\192.168.0.4\private\..."
    Extrae la ruta real desde el query string de la URL y abre la carpeta.
.EXAMPLE
    gestor://select?path=\\192.168.0.4\private\Equipos\...
.PARAMETER path
    URL completa del protocolo gestor:// (pasada por el registro de Windows como %1)
#>

param(
    [string]$path = ""
)

Add-Type -AssemblyName System.Windows.Forms
$logFile = "$env:TEMP\gestor_debug.log"

function Write-Log { param([string]$msg) "$(Get-Date -Format 'HH:mm:ss'): $msg" | Out-File -Append $logFile }
function Show-Error { param([string]$msg) [System.Windows.Forms.MessageBox]::Show($msg, "Gestor de Archivos - Error", [System.Windows.Forms.MessageBoxButtons]::OK, [System.Windows.Forms.MessageBoxIcon]::Error) | Out-Null }

Write-Log "=== INICIO ==="
Write-Log "path_recibido=$path"

# ============================================================
# 1. EXTRAER ACCION Y RUTA DESDE LA URL gestor://
# El registro de Windows pasa: -path "gestor://select?path=\\...\archivo"
# $path contiene exactamente esa URL completa.
# ============================================================

$action = "select"
$realPath = ""

if ($path -match 'gestor://(\w+)/?\?path=(.+)$') {
    $action = $matches[1]
    $realPath = $matches[2]
    Write-Log "extraido: action=$action realPath=$realPath"
} else {
    Show-Error "La URL del protocolo gestor:// no tiene el formato esperado.`n`nRecibido: $path"
    Write-Log "ERROR: no se pudo extraer path de: $path"
    exit 1
}

# ============================================================
# 2. DECODIFICAR URL (%5C -> \, %20 -> espacio, etc.)
# ============================================================

try {
    $realPath = [System.Uri]::UnescapeDataString($realPath)
} catch {
    Write-Log "UnescapeDataString fallo, usando reemplazo manual"
    $realPath = $realPath -replace '%5C', '\'
    $realPath = $realPath -replace '%20', ' '
    $realPath = $realPath -replace '%28', '('
    $realPath = $realPath -replace '%29', ')'
    $realPath = $realPath -replace '%2C', ','
    $realPath = $realPath -replace '%27', "'"
}

# Normalizar separadores: / -> \
$realPath = $realPath -replace '[/]', '\'

Write-Log "realPath_decoded=$realPath"

if ([string]::IsNullOrWhiteSpace($realPath)) {
    Show-Error "La ruta extraida esta vacia.`n`nURL recibida: $path"
    Write-Log "ERROR: ruta vacia"
    exit 1
}

# ============================================================
# 3. ABRIR EXPLORADOR WINDOWS
# explorer /select NO funciona con rutas UNC largas (bug Windows).
# Usamos Shell.Application.Open() con la carpeta contenedora.
# 3 intentos: Shell COM -> explorer.exe -> cmd /c start
# ============================================================

$folderPath = if ($action -eq "select") { [System.IO.Path]::GetDirectoryName($realPath) } else { $realPath }

Write-Log "folderPath=$folderPath"

if ([string]::IsNullOrWhiteSpace($folderPath)) {
    $folderPath = $realPath
}

$opened = $false

# Intento 1: Shell.Application COM (funciona mejor con UNC)
try {
    Write-Log "Intento 1: Shell.Application..."
    $shell = New-Object -ComObject "Shell.Application"
    $shell.Open($folderPath)
    $opened = $true
    Write-Log "Shell.Application OPEN OK"
} catch {
    Write-Log "Shell.Application fallo: $_"
}

# Intento 2: explorer.exe directo
if (-not $opened) {
    try {
        Write-Log "Intento 2: explorer.exe..."
        Start-Process -FilePath "explorer.exe" -ArgumentList "`"$folderPath`"" -ErrorAction Stop
        $opened = $true
        Write-Log "explorer.exe OK"
    } catch {
        Write-Log "explorer.exe fallo: $_"
    }
}

# Intento 3: cmd /c start
if (-not $opened) {
    try {
        Write-Log "Intento 3: cmd /c start..."
        Start-Process -FilePath "cmd.exe" -ArgumentList "/c start `"`" `"$folderPath`"" -WindowStyle Hidden
        $opened = $true
        Write-Log "cmd /c start OK"
    } catch {
        Write-Log "cmd /c start fallo: $_"
    }
}

if (-not $opened) {
    Show-Error "No se pudo abrir el Explorador de Windows.`n`nRuta: $folderPath"
    Write-Log "ERROR: no se pudo abrir el explorador"
    exit 1
}

Write-Log "=== FIN OK ==="
exit 0

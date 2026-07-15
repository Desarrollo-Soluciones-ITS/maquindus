<#
.SYNOPSIS
    Manejador del protocolo gestor://
    Abre el Explorador de Windows en la ruta UNC del archivo.
.DESCRIPTION
    Invocado por el navegador via protocolo gestor:// registrado en Windows.
    Recibe la URL completa como unico argumento:
        gestor://select?path=\\192.168.0.4\private\...
        gestor://open?path=\\192.168.0.4\private\...
.EXAMPLE
    Desde navegador:
        gestor://select?path=\\192.168.0.4\private\Equipos\...
    Desde PowerShell:
        .\gestor_handler.ps1 "gestor://select?path=\\192.168.0.4\private\..."
#>

Add-Type -AssemblyName System.Windows.Forms
$logFile = "$env:TEMP\gestor_debug.log"

function Write-Log { param([string]$msg) "$(Get-Date -Format 'HH:mm:ss'): $msg" | Out-File -Append $logFile }
function Show-Error { param([string]$msg) [System.Windows.Forms.MessageBox]::Show($msg, "Gestor de Archivos - Error", [System.Windows.Forms.MessageBoxButtons]::OK, [System.Windows.Forms.MessageBoxIcon]::Error) | Out-Null }

Write-Log "=== INICIO ==="

# ============================================================
# 1. OBTENER LA URL COMPLETA DESDE $args[0]
# Windows pasa la URL como %1 (argumento posicional)
# La URL tiene formato: gestor://select?path=\\192.168.0.4\private\...
# ============================================================

$rawUrl = ""
if ($args.Count -gt 0) {
    $rawUrl = $args[0]
}

Write-Log "rawUrl=$rawUrl"

if ([string]::IsNullOrWhiteSpace($rawUrl)) {
    Show-Error "No se recibio la URL del protocolo gestor://`n`nEjecuta este script desde el navegador web."
    Write-Log "ERROR: no se recibio URL"
    exit 1
}

# ============================================================
# 2. EXTRAER ACCION Y RUTA DE LA URL
# ============================================================

$action = "select"
$path = ""

# Buscar patron: gestor://<accion>?path=<ruta>
if ($rawUrl -match 'gestor://(\w+)\?path=(.+)$') {
    $action = $matches[1]
    $path = $matches[2]
    Write-Log "extraido: action=$action path=$path"
} else {
    Show-Error "La URL del protocolo gestor:// no tiene el formato esperado.`n`nURL recibida: $rawUrl"
    Write-Log "ERROR: formato inesperado: $rawUrl"
    exit 1
}

# ============================================================
# 3. DECODIFICAR URL (%5C -> \, %20 -> espacio, etc.)
# ============================================================

try {
    $path = [System.Uri]::UnescapeDataString($path)
} catch {
    Write-Log "UnescapeDataString fallo: $_"
    # Fallback manual
    $path = $path -replace '%5C', '\'
    $path = $path -replace '%20', ' '
    $path = $path -replace '%28', '('
    $path = $path -replace '%29', ')'
}

# Normalizar separadores
$path = $path -replace '[/]', '\'

Write-Log "path_decoded=$path"

if ([string]::IsNullOrWhiteSpace($path)) {
    Show-Error "La ruta del archivo esta vacia.`n`nNo se puede abrir el explorador."
    Write-Log "ERROR: ruta vacia"
    exit 1
}

# ============================================================
# 4. ABRIR EXPLORADOR WINDOWS
# explorer /select NO funciona con rutas UNC largas.
# Usamos Shell.Application.Open() con la carpeta contenedora.
# ============================================================

$folderPath = ""
if ($action -eq "select") {
    $folderPath = [System.IO.Path]::GetDirectoryName($path)
} else {
    $folderPath = $path
}

Write-Log "folderPath=$folderPath"

if ([string]::IsNullOrWhiteSpace($folderPath)) {
    # Si la ruta es la raiz del share, usarla directamente
    $folderPath = $path
}

$opened = $false

# Intento 1: Shell.Application COM
try {
    Write-Log "Intentando Shell.Application..."
    $shell = New-Object -ComObject "Shell.Application"
    $shell.Open($folderPath)
    $opened = $true
    Write-Log "Shell.Application.Open OK: $folderPath"
} catch {
    Write-Log "Shell.Application fallo: $_"
}

# Intento 2: explorer.exe directo
if (-not $opened) {
    try {
        Write-Log "FALLBACK: explorer.exe..."
        Start-Process -FilePath "explorer.exe" -ArgumentList "`"$folderPath`"" -ErrorAction Stop
        $opened = $true
        Write-Log "explorer.exe directo OK"
    } catch {
        Write-Log "explorer.exe fallo: $_"
    }
}

# Intento 3: cmd /c start
if (-not $opened) {
    try {
        Write-Log "FALLBACK 2: cmd /c start..."
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
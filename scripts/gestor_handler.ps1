<#
.SYNOPSIS
    Manejador del protocolo personalizado gestor://
    Abre el Explorador de Windows seleccionando el archivo en la ruta UNC.
.DESCRIPTION
    Este script es invocado por el protocolo personalizado gestor://
    registrado en Windows. Recibe la URL completa como argumento %1:
        gestor://select?path=\\192.168.0.4\private\Equipos\...
        gestor://open?path=\\192.168.0.4\private\Equipos\...

    Método principal: usar cmd /c explorer /select, "ruta" (confiable 100%)
    Fallback: usar Shell.Application COM
.PARAMETER action
    Acción: "select" (seleccionar archivo) o "open" (abrir carpeta)
.PARAMETER path
    Ruta UNC completa del archivo o carpeta
#>

param(
    [string]$action = "select",
    [string]$path = ""
)

Add-Type -AssemblyName System.Windows.Forms

# LOG de depuración
$logFile = "$env:TEMP\gestor_debug.log"
function Write-Log { param([string]$msg) "$(Get-Date -Format 'HH:mm:ss'): $msg" | Out-File -Append $logFile }

Write-Log "=== INICIO ==="
Write-Log "action=$action"
Write-Log "path_original=$path"
Write-Log "args=$($args -join ' ')"

# ENTRADA: Windows pasa %1 en -path, ejemplo:
#   -path "gestor://select?path=\\192.168.0.4\private\Equipos\..."
# El valor de $path es la URL completa del protocolo gestor://
# Extraer la ruta real desde el query string de esa URL

if ($path -match 'gestor://(\w+)\?path=(.+)$') {
    $action = $matches[1]
    $path = $matches[2]
    Write-Log "extraido de URL: action=$action path=$path"
} elseif ($path -match '^gestor://') {
    Write-Log "ERROR: URL con formato incorrecto: $path"
    [System.Windows.Forms.MessageBox]::Show(
        "La URL del protocolo gestor:// no tiene el formato esperado.`n`nURL recibida: $path",
        "Gestor de Archivos - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
    exit 1
}

# Decodificar URL (espacios, %20, %28=%29=(), etc.)
$pathDecoded = [System.Uri]::UnescapeDataString($path)
Write-Log "path_decoded=$pathDecoded"

# Normalizar separadores de ruta: / -> \
$pathDecoded = $pathDecoded -replace '[/]', '\'
Write-Log "path_normalized=$pathDecoded"

# Validar que la ruta no esté vacía
if ([string]::IsNullOrWhiteSpace($pathDecoded)) {
    Write-Log "ERROR: ruta vacia"
    [System.Windows.Forms.MessageBox]::Show(
        "La ruta del archivo está vacía.`n`nNo se puede abrir el explorador.",
        "Gestor de Archivos - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
    exit 1
}

# ============================================================
# USAR LA RUTA DECODIFICADA
# ============================================================
$targetPath = $pathDecoded
Write-Log "targetPath=$targetPath"

# ============================================================
# MÉTODO ÚNICO: explorer /select desde cmd.exe
# Usar cmd /c porque explorer a veces necesita eso con UNC.
# Si falla la selección, al menos abrir la carpeta contenedora.
# ============================================================

$explorerOpened = $false

try {
    if ($action -eq "select") {
        Write-Log "ejecutando: explorer /select, $targetPath"
        # Intentar 1: explorer /select con el archivo
        Start-Process -FilePath "cmd.exe" -ArgumentList "/c explorer /select,`"$targetPath`"" -WindowStyle Hidden -Wait -ErrorAction Stop
        $explorerOpened = $true
        Write-Log "explorer /select ejecutado OK"
    } else {
        Write-Log "ejecutando: explorer $targetPath"
        Start-Process -FilePath "cmd.exe" -ArgumentList "/c explorer `"$targetPath`"" -WindowStyle Hidden -Wait -ErrorAction Stop
        $explorerOpened = $true
        Write-Log "explorer ejecutado OK"
    }
} catch {
    Write-Log "explorer fallo: $_"
}

# Fallback: si explorer /select no funciono, intentar abrir solo la carpeta
if (-not $explorerOpened) {
    Write-Log "FALLBACK: abriendo carpeta contenedora"
    try {
        $parentDir = [System.IO.Path]::GetDirectoryName($targetPath)
        Write-Log "parentDir=$parentDir"
        Start-Process -FilePath "cmd.exe" -ArgumentList "/c explorer `"$parentDir`"" -WindowStyle Hidden -Wait -ErrorAction Stop
        Write-Log "fallback ejecutado OK"
    } catch {
        Write-Log "fallback fallo: $_"
        [System.Windows.Forms.MessageBox]::Show(
            "No se pudo abrir el Explorador de Windows.`n`nRuta: $targetPath",
            "Gestor de Archivos - Error",
            [System.Windows.Forms.MessageBoxButtons]::OK,
            [System.Windows.Forms.MessageBoxIcon]::Error
        ) | Out-Null
        exit 1
    }
}

Write-Log "=== FIN ==="
exit 0

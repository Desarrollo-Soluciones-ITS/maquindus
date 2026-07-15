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
# MÉTODO ÚNICO: explorer.exe con la carpeta contenedora
# Windows Explorer NO puede hacer /select con rutas UNC largas.
# Lo mejor es abrir la carpeta contenedora.
# Si el usuario necesita seleccionar el archivo, puede navegar
# hasta la carpeta que ya está abierta.
# ============================================================

Write-Log "METODO: explorer carpeta contenedora"
$explorerOpened = $false

try {
    if ($action -eq "select") {
        # Abrir la carpeta PADRE donde está el archivo
        $folderPath = [System.IO.Path]::GetDirectoryName($targetPath)
        Write-Log "folderPath=$folderPath"
        
        # Intentar 1: Shell.Application (abre la carpeta correctamente con UNC)
        try {
            $shell = New-Object -ComObject "Shell.Application"
            $shell.Open($folderPath)
            $explorerOpened = $true
            Write-Log "Shell.Application.Open OK"
        } catch {
            Write-Log "Shell.Application fallo: $_"
        }
        
        # Intentar 2: explorer.exe directo si COM falló
        if (-not $explorerOpened) {
            Write-Log "FALLBACK: explorer.exe $folderPath"
            Start-Process -FilePath "explorer.exe" -ArgumentList "`"$folderPath`"" -ErrorAction Stop
            $explorerOpened = $true
            Write-Log "explorer.exe directo OK"
        }
    } else {
        # "open": abrir la ruta directamente
        Write-Log "open: explorer.exe $targetPath"
        Start-Process -FilePath "explorer.exe" -ArgumentList "`"$targetPath`"" -ErrorAction Stop
        $explorerOpened = $true
        Write-Log "explorer.exe open OK"
    }
} catch {
    Write-Log "ERROR general: $_"
}

# Si nada funcionó, mostrar error
if (-not $explorerOpened) {
    Write-Log "ERROR: no se pudo abrir el explorador"
    [System.Windows.Forms.MessageBox]::Show(
        "No se pudo abrir el Explorador de Windows.`n`nRuta: $targetPath",
        "Gestor de Archivos - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
    exit 1
}

Write-Log "=== FIN ==="
exit 0

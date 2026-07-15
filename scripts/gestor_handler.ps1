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

param()

# Cargar ensamblado para MessageBox
Add-Type -AssemblyName System.Windows.Forms

# Variables de accion y ruta (se extraen de la URL)
$action = "select"
$path = ""

# Windows pasa la URL completa como argumento: gestor://select?path=\\...
# Extraer accion y ruta de la URL
if ($args.Count -gt 0) {
    $rawUrl = $args -join " "
    
    if ($rawUrl -match 'gestor://(\w+)\?path=(.+)$') {
        $action = $matches[1]
        $path = $matches[2]
    } elseif ($rawUrl -match 'path=(.+)$') {
        $path = $matches[1]
    }
} else {
    # Si no hay argumentos (ejecucion manual), mostrar error
    [System.Windows.Forms.MessageBox]::Show(
        "No se recibio la URL del protocolo gestor://`n`nEjecuta este script desde el navegador web.",
        "Gestor de Archivos - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
    exit 1
}

# Decodificar URL (espacios, %20, etc.)
try {
    $path = [System.Uri]::UnescapeDataString($path)
} catch {
    # Si falla la decodificación, intentar con una simple
    $path = $path -replace '%20', ' '
}

# Normalizar separadores de ruta
$path = $path -replace '[/]', '\'

# Validar que la ruta no esté vacía
if ([string]::IsNullOrWhiteSpace($path)) {
    [System.Windows.Forms.MessageBox]::Show(
        "La ruta del archivo está vacía.`n`nNo se puede abrir el explorador.",
        "Gestor de Archivos - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
    exit 1
}

# ============================================================
# MÉTODO PRINCIPAL: explorer /select desde cmd.exe
# Este método es el más confiable para seleccionar un archivo
# en el Explorador de Windows, incluso con rutas UNC.
# ============================================================
function Open-ExplorerSelect {
    param([string]$TargetPath)
    
    try {
        if ($action -eq "select") {
            # explorer /select, "ruta" abre la carpeta y selecciona el archivo
            Start-Process -FilePath "cmd.exe" -ArgumentList "/c explorer /select,`"$TargetPath`"" -WindowStyle Hidden
        } else {
            # explorer "ruta" abre la carpeta
            Start-Process -FilePath "cmd.exe" -ArgumentList "/c explorer `"$TargetPath`"" -WindowStyle Hidden
        }
        return $true
    } catch {
        return $false
    }
}

# ============================================================
# MÉTODO FALLBACK: Shell.Application COM
# ============================================================
function Open-ExplorerCOM {
    param([string]$TargetPath)

    try {
        $shell = New-Object -ComObject "Shell.Application"
        
        if ($action -eq "select" -and (Test-Path -Path $TargetPath)) {
            $parentDir = [System.IO.Path]::GetDirectoryName($TargetPath)
            $shell.Open($parentDir)
            Start-Sleep -Milliseconds 300
            $folder = $shell.NameSpace($parentDir)
            if ($folder) {
                $item = $folder.ParseName([System.IO.Path]::GetFileName($TargetPath))
                if ($item) {
                    $item.InvokeVerb("open")
                }
            }
        } else {
            $target = if (Test-Path -Path $TargetPath) { $TargetPath } else { [System.IO.Path]::GetDirectoryName($TargetPath) }
            if (Test-Path -Path $target) {
                $shell.Open($target)
            }
        }
        return $true
    } catch {
        return $false
    }
}

# ============================================================
# EJECUCIÓN
# ============================================================

# 1. Intentar método principal (explorer /select)
$result = Open-ExplorerSelect -TargetPath $path

# 2. Si falla, intentar fallback COM
if (-not $result) {
    $result = Open-ExplorerCOM -TargetPath $path
}

# 3. Si todo falla, mostrar error
if (-not $result) {
    [System.Windows.Forms.MessageBox]::Show(
        "No se pudo abrir el Explorador de Windows.`n`nRuta: $path",
        "Gestor de Archivos - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
    exit 1
}

exit 0
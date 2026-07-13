<#
.SYNOPSIS
    Manejador del protocolo personalizado gestor://
    Abre el Explorador de Windows en la ubicación del archivo o carpeta.
.DESCRIPTION
    Este script es invocado por el protocolo personalizado gestor://
    registrado en Windows. Recibe la ruta UNC y ejecuta explorer para
    abrir la carpeta con el archivo seleccionado.

    Uso desde navegador:
        gestor://select?path=\\192.168.0.4\private\Equipos\...
        gestor://open?path=\\192.168.0.4\private\Equipos\...

.PARAMETER action
    Acción a realizar: "select" (seleccionar archivo) o "open" (abrir carpeta)
.PARAMETER path
    Ruta UNC completa del archivo o carpeta
#>

param(
    [string]$action = "select",
    [string]$path = ""
)

# Si no recibió parámetros, intentar parsear de la línea de comandos
if ($path -eq "" -and $args.Count -gt 0) {
    $rawArgs = $args -join " "
    Write-Host "Argumentos recibidos: $rawArgs" | Out-File -Append "$env:TEMP\gestor_debug.log"
    
    # Intentar extraer action y path de la URL completa
    if ($rawArgs -match 'gestor://(\w+)\?path=(.+?)$') {
        $action = $matches[1]
        $path = $matches[2]
    } elseif ($rawArgs -match 'path=(.+?)$') {
        $path = $matches[1]
    }
}

# Decodificar URL
$path = [System.Uri]::UnescapeDataString($path)

# Normalizar separadores de ruta
$path = $path -replace '[/]', '\'

Write-Host "Acción: $action" | Out-File -Append "$env:TEMP\gestor_debug.log"
Write-Host "Ruta: $path" | Out-File -Append "$env:TEMP\gestor_debug.log"

# Validar que la ruta no esté vacía
if ([string]::IsNullOrWhiteSpace($path)) {
    Write-Host "ERROR: Ruta vacía" | Out-File -Append "$env:TEMP\gestor_debug.log"
    [System.Windows.Forms.MessageBox]::Show(
        "La ruta del archivo está vacía.`n`nNo se puede abrir el explorador.",
        "Gestor de Archivos - Error",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Error
    ) | Out-Null
    exit 1
}

# Crear objeto COM para Shell
try {
    $shell = New-Object -ComObject "Shell.Application"
    
    switch ($action.ToLower()) {
        "select" {
            # Abrir carpeta y seleccionar el archivo
            if (Test-Path -Path $path) {
                $shell.Open( [System.IO.Path]::GetDirectoryName($path) )
                # Enfocar y seleccionar el archivo
                $folder = $shell.NameSpace( [System.IO.Path]::GetDirectoryName($path) )
                $item = $folder.ParseName( [System.IO.Path]::GetFileName($path) )
                if ($item) {
                    $item.InvokeVerb("properties")
                }
            } else {
                # Si no existe, abrir la carpeta contenedora
                $parentDir = [System.IO.Path]::GetDirectoryName($path)
                if (Test-Path -Path $parentDir) {
                    $shell.Open($parentDir)
                } else {
                    # Intentar abrir la raíz del compartido
                    $uncRoot = $path.Substring(0, $path.IndexOf('\', $path.IndexOf('\\') + 2))
                    if (Test-Path -Path $uncRoot) {
                        $shell.Open($uncRoot)
                    }
                }
            }
            break
        }
        "open" {
            # Abrir carpeta directamente
            $targetPath = $path
            if (Test-Path -Path $targetPath) {
                $shell.Open($targetPath)
            } else {
                $parentDir = [System.IO.Path]::GetDirectoryName($targetPath)
                if (Test-Path -Path $parentDir) {
                    $shell.Open($parentDir)
                }
            }
            break
        }
        default {
            Write-Host "ERROR: Acción desconocida: $action" | Out-File -Append "$env:TEMP\gestor_debug.log"
            break
        }
    }
    
    Write-Host "Explorer abierto correctamente" | Out-File -Append "$env:TEMP\gestor_debug.log"
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" | Out-File -Append "$env:TEMP\gestor_debug.log"
    
    # Fallback: usar cmd /c explorer directamente
    try {
        if ($action -eq "select") {
            $folderPath = [System.IO.Path]::GetDirectoryName($path)
            Start-Process -FilePath "cmd" -ArgumentList "/c explorer /select,`"$path`"" -WindowStyle Normal -Wait
        } else {
            Start-Process -FilePath "cmd" -ArgumentList "/c explorer `"$path`"" -WindowStyle Normal -Wait
        }
    } catch {
        [System.Windows.Forms.MessageBox]::Show(
            "Error al abrir el explorador:`n$($_.Exception.Message)",
            "Gestor de Archivos - Error",
            [System.Windows.Forms.MessageBoxButtons]::OK,
            [System.Windows.Forms.MessageBoxIcon]::Error
        ) | Out-Null
    }
}
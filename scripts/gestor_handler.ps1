param([string]$path = "")

# Log
"=== $(Get-Date -Format 'HH:mm:ss') ===" | Out-File "$env:TEMP\gestor_debug.log"
"path_recibido=$path" | Out-File -Append "$env:TEMP\gestor_debug.log"

# Extraer todo lo que viene despues de "path=" en la URL
$i = $path.IndexOf("path=")
if ($i -lt 0) { "ERROR: no se encontro path=" | Out-File -Append "$env:TEMP\gestor_debug.log"; exit 1 }

$ruta = $path.Substring($i + 5)
"ruta_extraida=$ruta" | Out-File -Append "$env:TEMP\gestor_debug.log"

# Decodificar URL completa (incluye acentos, ñ, %, etc.)
# 1. Primero + -> espacio (UnescapeDataString no lo hace)
$ruta = $ruta -replace '\+', ' '
# 2. Decodificar TODOS los %XX (incluye %5C, %20, %C3%A1, %C3%B1, etc.)
$ruta = [System.Uri]::UnescapeDataString($ruta)
# 3. / -> \
$ruta = $ruta -replace '[/]', '\'
"ruta_decodificada=$ruta" | Out-File -Append "$env:TEMP\gestor_debug.log"

# Obtener carpeta contenedora
$carpeta = [System.IO.Path]::GetDirectoryName($ruta)
"carpeta=$carpeta" | Out-File -Append "$env:TEMP\gestor_debug.log"

# Abrir carpeta (intenta /select, fallback a carpeta)
try { Start-Process explorer -ArgumentList "/select,`"$ruta`"" -ErrorAction Stop; "OK: explorer /select" | Out-File -Append "$env:TEMP\gestor_debug.log"; exit 0 }
catch { 
    "FALLBACK: /select fallo: $_" | Out-File -Append "$env:TEMP\gestor_debug.log"
    try { Start-Process explorer -ArgumentList "`"$carpeta`"" -ErrorAction Stop; "OK: explorer carpeta" | Out-File -Append "$env:TEMP\gestor_debug.log"; exit 0 }
    catch { "ERROR: $_, carpeta=$carpeta" | Out-File -Append "$env:TEMP\gestor_debug.log"; exit 1 }
}
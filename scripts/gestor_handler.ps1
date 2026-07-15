param([string]$path = "")

# Extraer todo lo que viene despues de "path=" en la URL
$i = $path.IndexOf("path=")
if ($i -lt 0) { exit 1 }

$ruta = $path.Substring($i + 5)

# Decodificar caracteres URL
$ruta = [System.Uri]::UnescapeDataString($ruta)
$ruta = $ruta -replace '[/]', '\'

# Obtener carpeta contenedora
$carpeta = [System.IO.Path]::GetDirectoryName($ruta)

# Abrir carpeta (intenta /select, fallback a carpeta)
try { Start-Process explorer -ArgumentList "/select,`"$ruta`"" -ErrorAction Stop; exit 0 }
catch { try { Start-Process explorer -ArgumentList "`"$carpeta`"" -ErrorAction Stop; exit 0 } catch { exit 1 } }
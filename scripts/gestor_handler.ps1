param([string]$path = "")

# Extraer path= de la URL
$idx = $path.IndexOf("path=")
if ($idx -lt 0) { exit 1 }

$p = $path.Substring($idx + 5)

# Decodificar URL
$p = [System.Uri]::UnescapeDataString($p)
$p = $p -replace '[/]', '\'

# Obtener carpeta contenedora
$folder = [System.IO.Path]::GetDirectoryName($p)

# Abrir explorador (intenta /select, si falla solo abre la carpeta)
try { Start-Process explorer -ArgumentList "/select,`"$p`"" -ErrorAction Stop }
catch { Start-Process explorer -ArgumentList "`"$folder`"" }
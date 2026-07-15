param([string]$path = "")

# Extraer ruta real de la URL
$idx = $path.IndexOf("path=")
if ($idx -lt 0) { exit 1 }

$p = $path.Substring($idx + 5)

# Decodificar URL
$p = [System.Uri]::UnescapeDataString($p)
$p = $p -replace '[/]', '\'

# Obtener carpeta contenedora
$folder = [System.IO.Path]::GetDirectoryName($p)

# Shell.Application es el unico metodo que funciona con UNC largas
try {
    $shell = New-Object -ComObject Shell.Application
    $shell.Open($folder)
    exit 0
} catch {
    # Fallback: explorer directo
    try { Start-Process explorer -ArgumentList "`"$folder`"" -ErrorAction Stop; exit 0 }
    catch { exit 1 }
}

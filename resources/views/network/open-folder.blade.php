<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abriendo carpeta...</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 { color: #333; margin-bottom: 0.5rem; }
        p { color: #666; margin: 0.5rem 0; }
        .file-name {
            background: #f8f8f8;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            word-break: break-all;
            margin: 1rem 0;
        }
        .fallback-link {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .fallback-link:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="spinner"></div>
        <h2>Abriendo carpeta...</h2>
        <p>Se está intentando abrir el Explorador de Windows</p>
        <div class="file-name">{{ basename($file->path) }}</div>
        <a href="{{ $url }}" class="fallback-link" id="manualLink">
            Abrir carpeta manualmente
        </a>
    </div>

    <script>
        // Abrir el protocolo gestor:// en UNA VENTANA AUXILIAR que se cierra sola
        // La pestaña actual se cierra inmediatamente

        // 1. Abrir la URL del protocolo en una ventana auxiliar
        var auxWindow = window.open('{{ $url }}', '_blank');

        // 2. Cerrar esta pestaña inmediatamente (vuelve a la vista del equipo)
        window.close();
    </script>
</body>
</html>
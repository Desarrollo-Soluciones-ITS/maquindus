<?php

/**
 * @param string $mime El tipo MIME completo del archivo (ej: 'image/png').
 * @return string Una etiqueta de texto amigable (ej: 'PNG', 'Word', 'Archivo').
 */
if (! function_exists('mime_type')) {
  function mime_type(string $mime): string
  {
    return match ($mime) {
      'application/pdf' => 'PDF',
      'image/jpeg'=> 'Imagen',
      'image/jpg' => 'Imagen',
      'image/png' => 'Imagen',
      'image/webp' => 'Imagen',
      'image/gif' => 'GIF',
      'application/msword' => 'Word',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
      'application/vnd.ms-excel' => 'Excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
      'application/zip' => 'ZIP',
      'text/plain' => 'Texto',
      'text/csv' => 'CSV',
      'application/json' => 'JSON',
      'video/mp4' => 'MP4',
      'audio/mpeg' => 'MP3',
      'application/acad' => 'AutoCAD',
      'image/vnd.dwg' => 'AutoCAD',
      'image/x-dwg' => 'AutoCAD',
      'application/dwg' => 'AutoCAD',
      'application/x-autocad' => 'AutoCAD',
      'application/x-dwg' => 'AutoCAD',
      default => 'Archivo',
    };
  }
}

if (! function_exists('model_to_folder')) {
    function model_name_to_spanish_plural(string $model) {
        return match ($model) {
            'Activity' => 'Actividades',
            'City' => 'Ciudades',
            'Customer' => 'Clientes',
            'Document' => 'Documentos',
            'Equipment' => 'Equipos',
            'File' => 'Archivos',
            'Part' => 'Repuestos',
            'Person' => 'Contactos',
            'Project' => 'Proyectos',
            'State' => 'Estados',
            'Supplier' => 'Proveedor',
            'User' => 'Usuarios',
        };
    }
}

if (! function_exists('is_local')) {
    function is_localhost_request() {
        return collect(['127.0.0.1', '::1'])
            ->doesntContain(request()->ip());
    }
}

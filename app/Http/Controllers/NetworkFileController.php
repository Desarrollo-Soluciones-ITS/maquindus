<?php

namespace App\Http\Controllers;

use App\Models\File;

class NetworkFileController extends Controller
{
    /**
     * Redirige al protocolo gestor:// para abrir carpeta con archivo seleccionado
     * desde un cliente LAN.
     */
    public function openFolder(File $file)
    {
        // Generar URL del protocolo gestor://
        $url = gestor_net_url($file->path, 'select');

        if (!$url) {
            abort(400, 'No se puede generar la URL de red para este archivo.');
        }

        return view('network.open-folder', compact('file', 'url'));
    }

    /**
     * Redirige al protocolo gestor:// para abrir archivo directamente
     * desde un cliente LAN.
     */
    public function openFile(File $file)
    {
        // Generar URL del protocolo gestor:// para abrir
        $url = gestor_net_url($file->path, 'open');

        if (!$url) {
            abort(400, 'No se puede generar la URL de red para este archivo.');
        }

        return view('network.open-file', compact('file', 'url'));
    }
}
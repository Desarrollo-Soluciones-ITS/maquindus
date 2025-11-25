<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FilesReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the file storage folders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (['Equipos', 'Repuestos', 'Proyectos', 'Contactos', 'Clientes', 'Proveedores', 'Superado'] as $folder) {
            Storage::disk('local')
                ->deleteDirectory($folder);
            }

            $this->info('File storage folders were reset succesfully.');
        }
}

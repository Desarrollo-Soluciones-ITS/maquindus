<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FilesMockup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:mockup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mockup a previous storage filesystem';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $list = include('app/console/Commands/paths.php');

        foreach ($list as $value) {
            Storage::copy('sample.pdf', "Estructura previa/$value");
        }

        $this->info('Mockup storage generated.');
    }
}

<?php

namespace App\Console\Commands;

use App\Services\SearchIndexer;
use App\Traits\Searchable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ManageSearchIndex extends Command
{
    protected $signature = 'search:index 
                            {--rebuild : Reconstruir la base de datos y volver a indexar los datos}
                            {--search= : Término de búsqueda para consultar el índice}
                            {--type= : Filtrar resultados por tipo de modelo (ej. User, Supplier)}';

    protected $description = 'Gestionar el índice de búsqueda: crear, reconstruir o buscar.';

    public function handle()
    {
        if ($this->option('rebuild')) {
            Log::info('Iniciando reconstrucción del índice de búsqueda');
            $this->rebuildIndex();
        }

        if ($this->option('search')) {
            Log::info('Ejecutando búsqueda en el índice con término: ' . $this->option('search'));
            $this->performSearch();
        } elseif (! $this->option('rebuild')) {
            $this->info('El índice de búsqueda está listo. Use --rebuild para refrescar los datos o --search="término" para consultar.');
        }
    }

    protected function rebuildIndex()
    {
        $this->info('Iniciando la indexación de los modelos...');
        Log::info('Recreando índice de búsqueda');
        DB::table('search_index')->truncate();
        $models = $this->getSearchableModels();

        foreach ($models as $modelClass) {
            $count = $modelClass::count();
            if ($count === 0) continue;

            $this->info("Indexando {$modelClass} ({$count} registros)...");
            Log::info("Indexando modelo {$modelClass} con {$count} registros");
            $bar = $this->output->createProgressBar($count);

            $modelClass::chunk(100, function ($records) use ($bar) {
                foreach ($records as $record) {
                    $record->updateSearchIndex();
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();
        }

        $this->info('¡Reconstrucción del índice completada!');
        Log::info('Reconstrucción del índice finalizada');
    }

    protected function performSearch()
    {
        $term = $this->option('search');
        $type = $this->option('type');

        $this->info("Buscando: '{$term}'" . ($type ? " en el modelo: {$type}" : ""));
        Log::info("Búsqueda ejecutada con término '{$term}'" . ($type ? " y tipo '{$type}'" : ""));

        $indexer = app(SearchIndexer::class);
        
        // Resolver nombre completo del modelo si se proporciona
        $models = [];
        if ($type) {
            $fullType = 'App\\Models\\' . $type;
            if (class_exists($fullType)) {
                $models[] = $fullType;
            } else {
                $this->error("Tipo de modelo '{$type}' no encontrado.");
                Log::error("Tipo de modelo no encontrado: {$type}");
                return;
            }
        }

        $results = $indexer->search($term, $models, 20); // Limitar a 20 resultados para CLI

        if (empty($results['data'])) {
            $this->warn('No se encontraron resultados.');
            Log::warning('Búsqueda sin resultados');
            return;
        }

        $headers = ['Tipo', 'Nombre', 'Descripción'];
        $rows = array_map(function ($item) {
            return [
                class_basename($item->model_type),
                $item->result_name,
                Str::limit($item->result_description, 50),
            ];
        }, $results['data']);

        $this->table($headers, $rows);
        $this->info("Total de resultados: {$results['total']}");
        Log::info("Búsqueda completada con {$results['total']} resultados");
    }

    protected function getSearchableModels(): array
    {
        $models = [];
        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->in(app_path('Models'))
            ->notName('Model.php');

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $className = 'App\\Models\\' . Str::before($relativePath, '.php');
            $className = str_replace('/', '\\', $className);

            if (
                class_exists($className) &&
                is_subclass_of($className, Model::class) &&
                in_array(Searchable::class, class_uses_recursive($className))
            ) {
                $models[] = $className;
            }
        }

        return $models;
    }
}

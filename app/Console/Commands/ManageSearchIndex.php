<?php

namespace App\Console\Commands;

use App\Services\SearchIndexer;
use App\Traits\Searchable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ManageSearchIndex extends Command
{
    protected $signature = 'search:index 
                            {--rebuild : Reconstruir la base de datos y volver a indexar los datos}
                            {--search= : Término de búsqueda para consultar el índice}
                            {--type= : Filtrar resultados por tipo de modelo (ej. User, Customer)}';

    protected $description = 'Gestionar el índice de búsqueda: crear, reconstruir o buscar.';

    public function handle()
    {
        $databasePath = database_path('search-index.sqlite');

        // 1. Manejar creación / reconstrucción de la base de datos
        if ($this->option('rebuild') || !File::exists($databasePath)) {
            Log::info('Iniciando creación/reconstrucción de la base de datos del índice de búsqueda');
            $this->info('Configurando la base de datos del índice de búsqueda...');
            $this->createDatabase($databasePath);
            $this->rebuildIndex();
        }

        // 2. Manejar búsqueda
        if ($this->option('search')) {
            Log::info('Ejecutando búsqueda en el índice con término: ' . $this->option('search'));
            $this->performSearch();
        } elseif (! $this->option('rebuild')) {
            $this->info('El índice de búsqueda está listo. Use --rebuild para refrescar los datos o --search="término" para consultar.');
        }
    }

    protected function createDatabase(string $databasePath)
    {
        $this->info('Creando la base de datos del índice de búsqueda...');
        Log::info('Creando archivo de base de datos en: ' . $databasePath);
        // Asegurar que el directorio exista
        $directory = dirname($databasePath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
            Log::info('Directorio creado: ' . $directory);
        }

        // Crear archivo si no existe o si se está reconstruyendo (truncaremos tablas más tarde, pero asegurar que el archivo exista es clave)
        if (!File::exists($databasePath)) {
            touch($databasePath);
            $this->info("Archivo de base de datos creado: {$databasePath}");
            Log::info('Archivo de base de datos creado');
        }

        // Configurar conexión temporal
        config([
            'database.connections.search_manage' => [
                'driver' => 'sqlite',
                'database' => $databasePath,
                'foreign_key_constraints' => false,
            ]
        ]);

        $connection = DB::connection('search_manage');
        $schema = $connection->getSchemaBuilder();

        // Eliminar tablas si se está reconstruyendo
        if ($this->option('rebuild')) {
            $schema->dropIfExists('search_index');
            $connection->statement('DROP TABLE IF EXISTS search_index_fts');
            Log::info('Tablas existentes eliminadas por opción de reconstrucción');
        }

        // Crear tablas si no existen
        if (! $schema->hasTable('search_index')) {
            $schema->create('search_index', function ($table) {
                $table->increments('id');
                $table->string('model_type');
                $table->string('model_id');
                $table->text('searchable_content');
                $table->text('searchable_content_normalized');
                $table->string('result_name');
                $table->text('result_description')->nullable();
                $table->timestamps();
            });

            $connection->statement('CREATE INDEX idx_model_type_id ON search_index(model_type, model_id);');
            $connection->statement('CREATE INDEX idx_normalized_content ON search_index(searchable_content_normalized);');

            $connection->statement(
                "CREATE VIRTUAL TABLE search_index_fts USING fts5(
                    searchable_content,
                    searchable_content_normalized,
                    result_name,
                    result_description,
                    content='search_index'
                );"
            );

            $this->info('Tablas de la base de datos creadas exitosamente.');
            Log::info('Tablas creadas y índices configurados');
        }
    }

    protected function rebuildIndex()
    {
        $this->info('Iniciando la indexación de los modelos...');
        Log::info('Recreando índice de búsqueda');
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

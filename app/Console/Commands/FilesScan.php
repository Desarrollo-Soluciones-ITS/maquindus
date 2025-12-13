<?php

namespace App\Console\Commands;

use App\Enums\Category;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


// Comando para carga inicial

// - si es muy incompatible con el estado actual del filesystem, se elimina todos los onlydocs y se dejan que se carguen solo documents y files, sin documentables ni más nada

// - si acaso pueden cargarse también los equipments por su nombre, con todos sus campos null

// - y bueno, los paths intermedios, pueden ir al nombre con el método que tenemos, o pueden simplemente perderse, pero si se pierden muchos paths intermedios aumentan los duplicates

// - hay que limitar el depth de esos path intermedios en todo caso, para que no haya problemas de overflow

// finalmente, este código tiene mucha repetición y puede mejorarse, pero será a futuro porque ahorita no hay tiempo

class FilesScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan the storage and load to database';

    protected $duplicated = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Navegando directorios...');

        $base = str(Storage::path(''));

        $cmd = "powershell -Command \"Get-ChildItem -Path '{$base->trim('\\')}' -Recurse -File | Select-Object -ExpandProperty FullName | Out-File -FilePath 'files.txt' -Encoding UTF8\"";

        $ls = escapeshellcmd($cmd);

        shell_exec($ls);

        $this->info('Navegación completada. Procesando archivos...');

        $equipments = [];

        $currentcontainer = '';
        $currentequipment = '';

        File::lines('files.txt')->each(function ($line) use ($base, &$currentcontainer, &$currentequipment, &$equipments, ) {
            $line = str($line)->trim();

            if ($line->toString() === '')
                return;

            $line = $line->remove($base->replace('/', '\\'));
            $path = $line->toString();

            if ($line->doesntStartWith('Estructura previa'))
                return;

            $segs = $line->explode('\\');
            $segs->shift();

            $name = $segs->shift();
            $equipment = null;

            if (isset($equipments[$name])) {
                $equipment = $equipments[$name];
            } else {
                $equipment = Equipment::firstOrCreate([
                    'name' => $name,
                ]);

                $equipments[$name] = $equipment;
            }

            $container = $segs->shift();
            $line = $segs->join('\\');

            if ($currentequipment !== $name) {
                $currentequipment = $name;
                $this->info('Procesando el equipo: ' . $name);
            }

            if ($currentcontainer !== $container) {
                $currentcontainer = $container;
                $this->info('Moviendo carpeta: ' . $container);
            }

            $container = str($container)
                ->lower()->explode(' ')->shift();

            DB::beginTransaction();
            try {
                if (!method_exists($this, $container)) {
                    DB::rollBack();
                    return;
                }
                $this->$container($line, $path, $equipment);
            } catch (\Throwable $th) {
                DB::rollBack();
            }

            DB::commit();
        });

        $string = '';

        foreach ($this->duplicated as $path => $dest) {
            $string .= "$path => $dest\n";
        }

        Storage::put('duplicados.txt', $string);

        File::delete('files.txt');

        $this->info("\nStorage scanned succesfully");
    }

    public function contactos(string $line, string $path)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $model = '';
        $person = null;

        if ($folders->isNotEmpty()) {
            // eliminar para onlydocs
            $model = $folders->shift();

            $person = Person::firstOrCreate([
                'name' => $model,
            ]);

            $model = '/' . $model;
        }

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Contactos{$model}/{$prefix}{$name} - V1.{$ext}";

        $this->move($path, $dest, $name, $mime, documentable: $person);
    }

    public function repuestos(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $model = '';
        $part = null;
        if ($folders->isNotEmpty()) {
            // eliminar para onlydocs
            $model = $folders->shift();

            $part = $equipment->parts()->firstOrCreate([
                'name' => $model,
            ]);

            $model = '/' . $model;
        }

        $category = '';
        $categoryenum = Category::tryFrom($folders[0]);
        if ($folders->isNotEmpty() && $categoryenum !== null) {
            // eliminar para onlydocs
            $category = "/{$folders->shift()}";
        }

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Repuestos{$model}{$category}/{$prefix}{$name} - V1.{$ext}";

        $this->move(
            $path,
            $dest,
            $name,
            $mime,
            documentable: $part,
            category: $categoryenum
        );
    }

    public function proveedores(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $model = '';
        $supplier = null;
        if ($folders->isNotEmpty()) {
            // eliminar para onlydocs
            $model = $folders->shift();

            $supplier = $equipment->suppliers()->firstOrCreate([
                'name' => $model,
            ]);

            $model = '/' . $model;
        }

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Proveedores{$model}/{$prefix}{$name} - V1.{$ext}";

        $this->move($path, $dest, $name, $mime, documentable: $supplier);
    }

    public function proyectos(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $model = '';
        $project = null;
        if ($folders->isNotEmpty()) {
            // eliminar para onlydocs
            $model = $folders->shift();

            $project = $equipment->projects()->firstOrCreate([
                'name' => $model,
            ]);

            $model = '/' . $model;
        }

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Proyectos{$model}/{$prefix}{$name} - V1.{$ext}";

        $this->move($path, $dest, $name, $mime, documentable: $project);
    }

    public function planos(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Equipos/{$equipment->name}/Planos/{$prefix}{$name} - V1.{$ext}";

        $this->move(
            $path,
            $dest,
            $name,
            $mime,
            documentable: $equipment,
            category: Category::Blueprint
        );
    }

    public function manuales(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Equipos/{$equipment->name}/Manuales/{$prefix}{$name} - V1.{$ext}";

        $this->move(
            $path,
            $dest,
            $name,
            $mime,
            documentable: $equipment,
            category: Category::Manual
        );
    }

    public function reportes(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Equipos/{$equipment->name}/Reportes/{$prefix}{$name} - V1.{$ext}";

        $this->move(
            $path,
            $dest,
            $name,
            $mime,
            documentable: $equipment,
            category: Category::Report
        );
    }

    public function especificaciones(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Equipos/{$equipment->name}/Especificaciones Tecnicas/{$prefix}{$name} - V1.{$ext}";

        $this->move(
            $path,
            $dest,
            $name,
            $mime,
            documentable: $equipment,
            category: Category::Specs
        );
    }

    public function ofertas(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Equipos/{$equipment->name}/Ofertas/{$prefix}{$name} - V1.{$ext}";

        $this->move(
            $path,
            $dest,
            $name,
            $mime,
            documentable: $equipment,
            category: Category::Offer
        );
    }

    public function fotos(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Equipos/{$equipment->name}/Planos/{$prefix}{$name} - V1.{$ext}";

        $this->move(
            $path,
            $dest,
            $name,
            $mime,
            documentable: $equipment,
            category: Category::Photo
        );
    }

    public function descripcion(string $line, string $path, Equipment $equipment)
    {
        [$folders, $ext, $name, $mime] = $this->pathing($line, $path);

        $prefix = '';
        if ($folders->isNotEmpty()) {
            // maybe limitar esto a una cierta profunidad usando ->pop($depth) before ->join
            $prefix = $folders->join(' - ') . ' - ';
        }

        $dest = "Equipos/{$equipment->name}/{$prefix}{$name} - V1.{$ext}";

        $this->move($path, $dest, $name, $mime, documentable: $equipment);
    }

    public function pathing(string $line, string $path)
    {
        $segments = str($line)
            ->explode('\\');

        $filesegments = str($segments->pop())
            ->explode('.');

        $extension = $filesegments->pop();

        $filename = $filesegments->join('.');

        $mime = check_solidworks(
            mime: Storage::mimeType($path),
            path: $path
        );

        return [$segments, $extension, $filename, $mime];
    }

    protected function move(
        string $path,
        string $dest,
        string $filename,
        string $mime,
        ?Model $documentable = null,
        ?Category $category = null,
    ) {

        if (Storage::exists($dest)) {
            $key = str($path)->remove('Estructura previa\\')->toString();
            $this->duplicated[$key] = $dest;
            return;
        }

        Storage::copy($path, $dest);

        $document = null;

        $data = ['name' => $filename];

        if ($category) {
            $data['category'] = $category;
        }

        if ($documentable) {
            $document = $documentable->documents()->create($data);
        } else {
            $document = Document::create($data);
        }

        $document->files()->create([
            'path' => $dest,
            'mime' => mime_type($mime),
            'version' => 1,
        ]);
    }
}

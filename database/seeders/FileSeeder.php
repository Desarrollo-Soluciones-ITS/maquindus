<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Equipment;
use App\Models\File;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = Document::query()
            ->with('documentable')
            ->whereHas('documentable')
            ->get();

        foreach ($documents as $document) {
            $documentable = $document->documentable;

            if (!$documentable) {
                continue;
            }

            foreach ([1, 2] as $version) {
                $path = $this->buildPath($document, $documentable, $version);

                // Si el archivo ya existe en BD (creado por EquipmentSeeder), saltar
                if (File::withTrashed()->where('path', $path)->exists()) {
                    continue;
                }

                if (!Storage::exists($path)) {
                    Storage::put($path, $this->buildFileContents($document, $documentable, $version));
                }

                File::withTrashed()->updateOrCreate(
                    [
                        'document_id' => $document->id,
                        'version' => $version,
                    ],
                    [
                        'path' => $path,
                        'mime' => 'Texto',
                        'file_size' => strlen($this->buildFileContents($document, $documentable, $version)),
                        'deleted_at' => null,
                    ],
                );
            }
        }
    }

    private function buildPath(Document $document, mixed $documentable, int $version): string
    {
        // Si el documentable es un equipo o pertenece a un equipo, usar estructura Equipos/{nombre}/
        $equipment = $this->getEquipment($documentable);
        if ($equipment) {
            return $this->buildEquipmentPath($document, $documentable, $equipment, $version);
        }

        // Para otros tipos (Supplier, Person), mantener estructura anterior
        $segments = array_filter([
            model_to_spanish($documentable::class, plural: true) ?? class_basename($documentable::class),
            $this->entityFolderName($documentable),
            $document->category?->value,
            $this->safeFilename($document->name, $version),
        ]);

        return collect($segments)
            ->map(fn(string $segment) => $this->sanitizePathSegment($segment))
            ->filter()
            ->implode('/');
    }

    private function getEquipment(mixed $documentable): ?Equipment
    {
        if ($documentable instanceof Equipment) {
            return $documentable;
        }

        if (method_exists($documentable, 'equipment')) {
            $relation = $documentable->equipment();
            if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                return $documentable->equipment;
            }
            if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                return $documentable->equipment()->first();
            }
        }

        return null;
    }

    private function buildEquipmentPath(Document $document, mixed $documentable, Equipment $equipment, int $version): string
    {
        $section = $this->getSectionForDocumentable($documentable);
        $descriptor = $this->getDescriptorForDocumentable($documentable);
        $filename = $this->safeFilename($document->name, $version);

        $parts = ['Equipos', $equipment->name];

        // Las secciones de "Especificación técnica" van anidadas bajo Especificaciones Tecnicas/
        $specSections = ['Hoja De Datos', 'Planos', 'Catálogos', 'Manuales', 'Especificaciones Tecnicas', 'Normas'];
        if (in_array($section, $specSections)) {
            $parts[] = 'Especificaciones Tecnicas';
            // La sección "Especificaciones Tecnicas" pasa a llamarse "Revisiones" cuando está anidada
            $nestedSection = $section === 'Especificaciones Tecnicas' ? 'Revisiones' : $section;
            $parts[] = $nestedSection;
        } else {
            $parts[] = $section;
        }

        if ($descriptor) {
            $parts[] = $descriptor;
        }
        $parts[] = $filename;

        return implode('/', $parts);
    }

    private function getSectionForDocumentable(mixed $documentable): string
    {
        $class = class_basename($documentable::class);

        return match ($class) {
            'EquipmentDataSheet' => 'Hoja De Datos',
            'EquipmentBlueprint' => 'Planos',
            'EquipmentCatalog' => 'Catálogos',
            'EquipmentManual' => 'Manuales',
            'EquipmentTechnicalSpecification' => 'Especificaciones Tecnicas',
            'EquipmentStandard' => 'Normas',
            'EquipmentFieldQuery' => 'Consultas de Campo',
            'EquipmentReport' => 'Reportes',
            'EquipmentSparePart' => 'Repuestos',
            'Equipment' => 'Manuales',
            default => 'General',
        };
    }

    private function getDescriptorForDocumentable(mixed $documentable): ?string
    {
        if ($documentable instanceof Equipment) {
            return null; // Los documentos directos del equipo van sin descriptor
        }

        foreach (['name', 'document_name', 'sheet_number', 'blueprint_number', 'revision_name', 'part_number', 'catalog_number'] as $key) {
            $value = $documentable->$key ?? null;
            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function entityFolderName(mixed $documentable): string
    {
        if ($documentable instanceof Person) {
            $email = trim((string) ($documentable->email ?? ''));

            return $email !== ''
                ? $documentable->name . ' - ' . $email
                : $documentable->name;
        }

        return (string) ($documentable->name ?? class_basename($documentable::class));
    }

    private function safeFilename(string $name, int $version): string
    {
        return Str::of($name)->trim()->value() . ' - V' . $version . '.txt';
    }

    private function sanitizePathSegment(string $value): string
    {
        $value = str_replace(['\\', '/'], ' ', $value);
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value, " .\t\n\r\0\x0B");
    }

    private function buildFileContents(Document $document, mixed $documentable, int $version): string
    {
        $lines = [
            'Documento de prueba de Maquindus',
            'Documento: ' . $document->name,
            'Entidad: ' . $this->entityFolderName($documentable),
            'Categoría: ' . ($document->category?->value ?? 'Sin categoría'),
            'Versión: ' . $version,
            'Generado automáticamente por DatabaseSeeder.',
        ];

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}

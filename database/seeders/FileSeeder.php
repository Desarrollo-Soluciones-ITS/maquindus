<?php

namespace Database\Seeders;

use App\Models\Document;
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
                        'deleted_at' => null,
                    ],
                );
            }
        }
    }

    private function buildPath(Document $document, mixed $documentable, int $version): string
    {
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

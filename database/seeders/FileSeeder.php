<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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

            $folder = model_to_spanish(
                model: $documentable::class,
                plural: true,
            ) ?? class_basename($documentable::class);

            $documentToken = (string) str($document->id)->afterLast('-');
            $baseName = $documentable->name ?? class_basename($documentable::class);
            $segments = collect([$folder, $documentToken, $baseName]);

            if ($document->category?->value) {
                $segments->push($document->category->value);
            }

            $segments->push('documento-v1.pdf');
            $path = $segments->join('/');

            if (Storage::exists('sample.pdf')) {
                if (!Storage::exists($path)) {
                    Storage::copy('sample.pdf', $path);
                }
            } elseif (!Storage::exists($path)) {
                Storage::put($path, 'Documento de prueba generado por seeder.');
            }

            File::withTrashed()->updateOrCreate(
                [
                    'document_id' => $document->id,
                    'version' => 1,
                ],
                [
                    'path' => $path,
                    'mime' => 'PDF',
                    'deleted_at' => null,
                ],
            );
        }
    }
}

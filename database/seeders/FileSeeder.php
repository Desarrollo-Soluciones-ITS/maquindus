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
            ->latest()
            ->whereHas('documentable')
            ->get();

        $files = [
            [
                'path' => 'docs/sample1.pdf',
                'mime' => 'PDF',
                'version' => 1,
                'document_id' => $documents[0]->id
            ],
            [
                'path' => 'docs/sample2.pdf',
                'mime' => 'PDF',
                'version' => 1,
                'document_id' => $documents[1]->id
            ],
            [
                'path' => 'docs/sample3.pdf',
                'mime' => 'PDF',
                'version' => 1,
                'document_id' => $documents[2]->id
            ],
        ];

        foreach ($files as $file) {
            File::create($file);
        }

        if (Storage::missing('sample.pdf'))
            return;

        $files = File::query()
            ->oldest()
            ->limit(3)
            ->get();

        foreach ($files as $file) {
            $document = $file->document;
            $documentable = $document->documentable;

            $folder = model_to_spanish(
                model: $documentable::class,
                plural: true
            );

            $segments = collect([$folder, $documentable->name]);
            $category = $document->category;

            if ($category) {
                $segments->push($category->value);
            }

            $name = str($document->name)
                ->append(" - V1.pdf");

            $segments->push($name);

            $path = $segments->join('/');

            Storage::copy('sample.pdf', $path);

            $file->update([
                'path' => $path,
            ]);
        }
    }
}

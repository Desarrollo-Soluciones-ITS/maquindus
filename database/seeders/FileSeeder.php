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
        $documents = Document::oldest()->get();

        $files = [
            [
                'path' => 'docs/sample.pdf',
                'mime' => 'application/pdf',
                'version' => 1,
                'document_id' => $documents[0]->id
            ],
            [
                'path' => 'docs/sample.pdf',
                'mime' => 'application/pdf',
                'version' => 1,
                'document_id' => $documents[1]->id
            ],
            [
                'path' => 'docs/sample.pdf',
                'mime' => 'application/pdf',
                'version' => 1,
                'document_id' => $documents[2]->id
            ],
        ];

        foreach ($files as $file) {
            File::create($file);
        }

        if (Storage::missing('sample.pdf')) return;

        $files = File::with(['document', 'document.documentable'])
            ->get();

        foreach ($files as $file) {
            $document = $file->document;

            $folder = model_to_spanish(
                model: $document->documentable_type,
                plural: true
            );

            $segments = collect([$folder, $document->documentable->name]);
            $category = $document->category;
            
            if ($category) {
                $segments->push($category->value);
            }

            $name = str($file->document->name)
                ->append(" - V1.pdf");
            $segments->push($name);

            $path = $segments->join('/');

            Storage::copy('sample.pdf', $path);
        }
    }
}

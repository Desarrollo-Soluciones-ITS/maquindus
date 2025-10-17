<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\File;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = Document::all();

        $files = [
            [
                'path' => 'docs/plano_conjunto.pdf',
                'mime' => 'application/pdf',
                'version' => 1,
                'document_id' => $documents[0]->id
            ],
            [
                'path' => 'docs/manual_operacion.pdf',
                'mime' => 'application/pdf',
                'version' => 1,
                'document_id' => $documents[1]->id
            ],
        ];

        foreach ($files as $file) {
            File::create($file);
        }
    }
}

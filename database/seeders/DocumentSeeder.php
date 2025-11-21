<?php

namespace Database\Seeders;

use App\Enums\Category;
use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\Project;
use App\Models\Equipment;
use App\Models\Part;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = Equipment::first();
        $part = Part::first();
        $project = Project::first();

        $documents = [
            [
                'name' => 'Plano de conjunto',
                'category' => Category::Blueprint->value,
                'documentable_type' => Part::class,
                'documentable_id' => $part->id
            ],
            [
                'name' => 'Manual de operación',
                'category' => Category::Manual->value,
                'documentable_type' => Equipment::class,
                'documentable_id' => $equipment->id
            ],
            [
                'name' => 'Propuesta realizada',
                'documentable_type' => Project::class,
                'documentable_id' => $project->id
            ],
        ];

        foreach ($documents as $data) {
            Document::create($data);
        }
    }
}

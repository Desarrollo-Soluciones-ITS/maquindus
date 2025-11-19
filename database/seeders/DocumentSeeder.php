<?php

namespace Database\Seeders;

use App\Enums\Category;
use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\Project;
use App\Models\Equipment;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::first();
        $equipment = Equipment::first();

        $documents = [
            [
                'name' => 'Plano de conjunto',
                'category' => Category::Blueprint->value,
                'documentable_type' => Project::class,
                'documentable_id' => $project->id
            ],
            [
                'name' => 'Manual de operación',
                'category' => Category::Manual->value,
                'documentable_type' => Equipment::class,
                'documentable_id' => $equipment->id
            ],
        ];

        foreach ($documents as $data) {
            Document::create($data);
        }
    }
}

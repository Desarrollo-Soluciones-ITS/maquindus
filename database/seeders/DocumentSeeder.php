<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\Type;
use App\Enums\Category;
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
                'type' => Type::Blueprint->value,
                'category' => Category::Set->value,
                'documentable_type' => Project::class,
                'documentable_id' => $project->id
            ],
            [
                'name' => 'Manual de operación',
                'type' => Type::Manual->value,
                'category' => Category::Operation->value,
                'documentable_type' => Equipment::class,
                'documentable_id' => $equipment->id
            ],
        ];

        foreach ($documents as $data) {
            Document::create($data);
        }
    }
}

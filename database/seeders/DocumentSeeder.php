<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Enums\Type;
use App\Enums\Category;
use App\Models\Document;
use App\Models\User;
use App\Models\Project;
use App\Models\Equipment;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        $project = Project::first();
        $equipment = Equipment::first();

        $documents = [
            ['id' => (string) Str::uuid(), 'name' => 'Plano de conjunto','path' => 'docs/plano_conjunto.pdf','mime' => 'application/pdf','version' => 1,'type' => Type::BLUEPRINT->value,'category' => Category::SET->value,'documentable_type' => Project::class,'documentable_id' => $project->id,'user_id' => $user->id],
            ['id' => (string) Str::uuid(), 'name' => 'Manual de operación','path' => 'docs/manual_operacion.pdf','mime' => 'application/pdf','version' => 1,'type' => Type::MANUAL->value,'category' => Category::OPERATION->value,'documentable_type' => Equipment::class,'documentable_id' => $equipment->id,'user_id' => $user->id],
        ];

        foreach ($documents as $d) {
            Document::create($d);
        }
    }
}

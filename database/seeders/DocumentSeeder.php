<?php

namespace Database\Seeders;

use App\Enums\Category;
use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentReport;
use App\Models\EquipmentStandard;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\Part;
use App\Models\Person;
use App\Models\Supplier;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            ...Equipment::query()->get()->map(fn(Equipment $equipment) => [
                'name' => 'Manual de operación ' . $equipment->name,
                'category' => Category::Manual,
                'documentable_type' => Equipment::class,
                'documentable_id' => $equipment->id,
            ])->all(),
            ...Part::query()->get()->map(fn(Part $part) => [
                'name' => 'Plano de conjunto ' . $part->name,
                'category' => Category::Blueprint,
                'documentable_type' => Part::class,
                'documentable_id' => $part->id,
            ])->all(),
            ...Supplier::query()->get()->map(fn(Supplier $supplier) => [
                'name' => 'Oferta comercial ' . $supplier->name,
                'category' => Category::Offer,
                'documentable_type' => Supplier::class,
                'documentable_id' => $supplier->id,
            ])->all(),
            ...Person::query()->get()->map(fn(Person $person) => [
                'name' => 'Ficha de contacto ' . $person->name,
                'category' => Category::Report,
                'documentable_type' => Person::class,
                'documentable_id' => $person->id,
            ])->all(),
            ...EquipmentDataSheet::query()->get()->map(fn(EquipmentDataSheet $record) => [
                'name' => 'Hoja de datos ' . $record->sheet_number,
                'category' => Category::Specs,
                'documentable_type' => EquipmentDataSheet::class,
                'documentable_id' => $record->id,
            ])->all(),
            ...EquipmentBlueprint::query()->get()->map(fn(EquipmentBlueprint $record) => [
                'name' => 'Plano ' . $record->name,
                'category' => Category::Blueprint,
                'documentable_type' => EquipmentBlueprint::class,
                'documentable_id' => $record->id,
            ])->all(),
            ...EquipmentCatalog::query()->get()->map(fn(EquipmentCatalog $record) => [
                'name' => 'Catálogo ' . $record->name,
                'category' => Category::Photo,
                'documentable_type' => EquipmentCatalog::class,
                'documentable_id' => $record->id,
            ])->all(),
            ...EquipmentTechnicalSpecification::query()->get()->map(fn(EquipmentTechnicalSpecification $record) => [
                'name' => 'Revisión ' . $record->revision_name,
                'category' => Category::Specs,
                'documentable_type' => EquipmentTechnicalSpecification::class,
                'documentable_id' => $record->id,
            ])->all(),
            ...EquipmentStandard::query()->get()->map(fn(EquipmentStandard $record) => [
                'name' => 'Norma ' . $record->name,
                'category' => Category::Specs,
                'documentable_type' => EquipmentStandard::class,
                'documentable_id' => $record->id,
            ])->all(),
            ...EquipmentFieldQuery::query()->get()->map(fn(EquipmentFieldQuery $record) => [
                'name' => $record->document_name,
                'category' => Category::Report,
                'documentable_type' => EquipmentFieldQuery::class,
                'documentable_id' => $record->id,
            ])->all(),
            ...EquipmentReport::query()->get()->map(fn(EquipmentReport $record) => [
                'name' => $record->document_name,
                'category' => Category::Report,
                'documentable_type' => EquipmentReport::class,
                'documentable_id' => $record->id,
            ])->all(),
        ];

        foreach ($documents as $data) {
            Document::updateOrCreate(
                [
                    'name' => $data['name'],
                    'documentable_type' => $data['documentable_type'],
                    'documentable_id' => $data['documentable_id'],
                ],
                [
                    'category' => $data['category'],
                ],
            );
        }
    }
}

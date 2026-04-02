<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentReport;
use App\Models\EquipmentStandard;
use App\Models\EquipmentTechnicalSpecification;
use Illuminate\Database\Seeder;

class EquipmentMetadataSeeder extends Seeder
{
    public function run(): void
    {
        $equipmentRecords = Equipment::query()->get();

        foreach ($equipmentRecords as $index => $equipment) {
            EquipmentDataSheet::updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'sheet_number' => 'DS-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                ],
                [
                    'revision' => 'A',
                    'document_date' => now()->subMonths(6 + $index)->toDateString(),
                ],
            );

            EquipmentBlueprint::updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'blueprint_number' => 'PL-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                ],
                [
                    'name' => 'Plano general ' . $equipment->name,
                    'revision' => 'B',
                    'document_date' => now()->subMonths(5 + $index)->toDateString(),
                ],
            );

            EquipmentCatalog::updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'name' => 'Catálogo multimedia ' . $equipment->name,
                ],
                [
                    'document_type' => ['fotos', 'videos', 'pdf'][$index % 3],
                ],
            );

            EquipmentTechnicalSpecification::updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'revision_name' => 'Revisión operativa ' . $equipment->name,
                ],
                [
                    'revision' => 'R' . ($index + 1),
                    'document_date' => now()->subMonths(4 + $index)->toDateString(),
                ],
            );

            EquipmentStandard::updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'name' => 'Norma técnica ' . $equipment->name,
                ],
                [
                    'revision' => '2026',
                ],
            );

            EquipmentFieldQuery::updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'document_name' => 'Consulta de campo ' . $equipment->name,
                ],
                [
                    'document_type' => 'inspección',
                    'document_date' => now()->subMonths(2 + $index)->toDateString(),
                    'issuer' => 'Departamento técnico',
                ],
            );

            EquipmentReport::updateOrCreate(
                [
                    'equipment_id' => $equipment->id,
                    'document_name' => 'Reporte de servicio ' . $equipment->name,
                ],
                [
                    'document_type' => 'mantenimiento',
                    'document_date' => now()->subMonths(1 + $index)->toDateString(),
                    'issuer' => 'Ingeniería Maquindus',
                ],
            );
        }
    }
}
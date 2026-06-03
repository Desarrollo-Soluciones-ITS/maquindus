<?php

namespace Database\Seeders;

use App\Enums\Category;
use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\EquipmentStandard;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentManual;
use App\Models\EquipmentReport;
use App\Models\EquipmentSparePart;
use App\Models\Document;
use App\Models\Part;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = [
            [
                'name' => 'Compresor Atlas',
                'model' => 'CAT-50HP',
                'serial' => 'SN123456',
                'type' => 'Compresor',
                'about' => 'Compresor centrífugo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Generador Perkins',
                'model' => 'GPK-200kW',
                'serial' => 'SN789012',
                'type' => 'Generador',
                'about' => 'Generador diésel',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        $disk = Storage::disk('local');

        foreach ($equipment as $e) {
            $equipment = Equipment::updateOrCreate(
                ['name' => $e['name']],
                $e,
            );

            $partIds = Part::query()->limit(2)->pluck('id')->all();
            if ($partIds !== []) {
                $equipment->parts()->syncWithoutDetaching($partIds);
            }

            $supplierIds = Supplier::query()->limit(2)->pluck('id')->all();
            if ($supplierIds !== []) {
                $equipment->suppliers()->syncWithoutDetaching($supplierIds);
            }

            // Crear documentos y archivos para este equipo
            $this->createEquipmentDocuments($equipment, $disk);
        }
    }

    private function createEquipmentDocuments(Equipment $equipment, $disk): void
    {
        $equipmentName = $equipment->name;

        // Definir los documentos a crear para cada equipo
        // Formato: [sección, descriptor, nombre_documento, versión, contenido, modelo_hijo (opcional)]
        $documents = [
            // Hojas de Datos
            [
                'section' => 'Hoja De Datos',
                'descriptor' => 'DS-001',
                'name' => 'Hoja de datos DS-001',
                'versions' => [
                    ['version' => 1, 'content' => "Hoja de datos DS-001 - V1\nEquipo: {$equipmentName}\nFecha: 2024-01-15\nEspecificaciones técnicas generales."],
                    ['version' => 2, 'content' => "Hoja de datos DS-001 - V2\nEquipo: {$equipmentName}\nFecha: 2024-06-20\nEspecificaciones técnicas actualizadas."],
                ],
                'modelClass' => EquipmentDataSheet::class,
                'modelData' => ['sheet_number' => 'DS-001'],
            ],
            // Planos
            [
                'section' => 'Planos',
                'descriptor' => "Plano general {$equipmentName}",
                'name' => "Plano Plano general {$equipmentName}",
                'versions' => [
                    ['version' => 1, 'content' => "Plano Plano general {$equipmentName} - V1\nVistas generales del equipo."],
                    ['version' => 2, 'content' => "Plano Plano general {$equipmentName} - V2\nVistas generales actualizadas."],
                ],
                'modelClass' => EquipmentBlueprint::class,
                'modelData' => [
                    'blueprint_number' => 'BP-' . ($equipmentName === 'Compresor Atlas' ? '001' : '002'),
                    'name' => "Plano general {$equipmentName}",
                ],
            ],
            // Catálogos
            [
                'section' => 'Catálogos',
                'descriptor' => "Catálogo multimedia {$equipmentName}",
                'name' => "Catálogo Catálogo multimedia {$equipmentName}",
                'versions' => [
                    ['version' => 1, 'content' => "Catálogo Catálogo multimedia {$equipmentName} - V1\nCatálogo comercial del equipo."],
                    ['version' => 2, 'content' => "Catálogo Catálogo multimedia {$equipmentName} - V2\nCatálogo actualizado."],
                ],
                'modelClass' => EquipmentCatalog::class,
                'modelData' => [
                    'document_type' => 'Catálogo',
                    'name' => "Catálogo multimedia {$equipmentName}",
                ],
            ],
            // Especificaciones Técnicas
            [
                'section' => 'Especificaciones Tecnicas',
                'descriptor' => "Revisión operativa {$equipmentName}",
                'name' => "Revisión Revisión operativa {$equipmentName}",
                'versions' => [
                    ['version' => 1, 'content' => "Revisión Revisión operativa {$equipmentName} - V1\nRevisión de condiciones operativas."],
                    ['version' => 2, 'content' => "Revisión Revisión operativa {$equipmentName} - V2\nRevisión actualizada."],
                ],
                'modelClass' => EquipmentTechnicalSpecification::class,
                'modelData' => ['revision_name' => "Revisión operativa {$equipmentName}"],
            ],
            // Normas
            [
                'section' => 'Normas',
                'descriptor' => "Norma técnica {$equipmentName}",
                'name' => "Norma Norma técnica {$equipmentName}",
                'versions' => [
                    ['version' => 1, 'content' => "Norma Norma técnica {$equipmentName} - V1\nNormas aplicables al equipo."],
                    ['version' => 2, 'content' => "Norma Norma técnica {$equipmentName} - V2\nNormas actualizadas."],
                ],
                'modelClass' => EquipmentStandard::class,
                'modelData' => ['name' => "Norma técnica {$equipmentName}"],
            ],
            // Consultas de Campo
            [
                'section' => 'Consultas de Campo',
                'descriptor' => "Consulta de campo {$equipmentName}",
                'name' => "Consulta de campo {$equipmentName}",
                'versions' => [
                    ['version' => 1, 'content' => "Consulta de campo {$equipmentName} - V1\nReporte de consulta técnica en campo."],
                    ['version' => 2, 'content' => "Consulta de campo {$equipmentName} - V2\nReporte actualizado."],
                ],
                'modelClass' => EquipmentFieldQuery::class,
                'modelData' => [
                    'document_name' => "Consulta de campo {$equipmentName}",
                    'document_type' => 'Consulta de campo',
                ],
            ],
            // Reportes
            [
                'section' => 'Reportes',
                'descriptor' => "Reporte de servicio {$equipmentName}",
                'name' => "Reporte de servicio {$equipmentName}",
                'versions' => [
                    ['version' => 1, 'content' => "Reporte de servicio {$equipmentName} - V1\nReporte de servicio técnico."],
                    ['version' => 2, 'content' => "Reporte de servicio {$equipmentName} - V2\nReporte actualizado."],
                ],
                'modelClass' => EquipmentReport::class,
                'modelData' => [
                    'document_name' => "Reporte de servicio {$equipmentName}",
                    'document_type' => 'Reporte de servicio',
                ],
            ],
            // Repuestos
            [
                'section' => 'Repuestos',
                'descriptor' => $equipmentName === 'Compresor Atlas' ? 'SP-001' : 'SP-002',
                'name' => "Ficha de repuesto " . ($equipmentName === 'Compresor Atlas' ? 'SP-001' : 'SP-002'),
                'versions' => [
                    ['version' => 1, 'content' => "Ficha de repuesto " . ($equipmentName === 'Compresor Atlas' ? 'SP-001' : 'SP-002') . " - V1\nFicha técnica del repuesto."],
                    ['version' => 2, 'content' => "Ficha de repuesto " . ($equipmentName === 'Compresor Atlas' ? 'SP-001' : 'SP-002') . " - V2\nFicha actualizada."],
                ],
                'modelClass' => EquipmentSparePart::class,
                'modelData' => ['part_number' => $equipmentName === 'Compresor Atlas' ? 'SP-001' : 'SP-002'],
            ],
            // Manuales
            [
                'section' => 'Manuales',
                'descriptor' => "Manual de operación {$equipmentName}",
                'name' => "Manual de operación {$equipmentName}",
                'versions' => [
                    ['version' => 1, 'content' => "Manual de operación {$equipmentName} - V1\nManual de operación y mantenimiento."],
                    ['version' => 2, 'content' => "Manual de operación {$equipmentName} - V2\nManual actualizado."],
                ],
                'modelClass' => EquipmentManual::class,
                'modelData' => ['name' => "Manual de operación {$equipmentName}"],
            ],
        ];

        foreach ($documents as $docDef) {
            $section = $docDef['section'];
            $descriptor = $docDef['descriptor'];
            $docName = $docDef['name'];
            $modelClass = $docDef['modelClass'];
            $modelData = $docDef['modelData'];

            // Crear el modelo hijo si aplica
            $childModel = null;
            if ($modelClass && $modelData) {
                $childModel = $modelClass::updateOrCreate(
                    array_merge($modelData, ['equipment_id' => $equipment->id]),
                    ['equipment_id' => $equipment->id],
                );
            }

            // Crear el documento
            $documentableType = $childModel ? get_class($childModel) : Equipment::class;
            $documentableId = $childModel ? $childModel->id : $equipment->id;

            $document = Document::updateOrCreate(
                [
                    'name' => $docName,
                    'documentable_type' => $documentableType,
                    'documentable_id' => $documentableId,
                ],
                [
                    'category' => $this->getCategoryForSection($section),
                ]
            );

            // Crear los archivos
            foreach ($docDef['versions'] as $verDef) {
                $version = $verDef['version'];
                $content = $verDef['content'];
                $filename = "{$docName} - V{$version}.txt";

                // Construir ruta
                $pathParts = ['Equipos', $equipmentName];

                // Las secciones de "Especificación técnica" van anidadas bajo Especificaciones Tecnicas/
                $specSections = ['Hoja De Datos', 'Planos', 'Catálogos', 'Manuales', 'Especificaciones Tecnicas', 'Normas'];
                if (in_array($section, $specSections)) {
                    $pathParts[] = 'Especificaciones Tecnicas';
                    // La sección "Especificaciones Tecnicas" pasa a llamarse "Revisiones" cuando está anidada
                    $nestedSection = $section === 'Especificaciones Tecnicas' ? 'Revisiones' : $section;
                    $pathParts[] = $nestedSection;
                } else {
                    $pathParts[] = $section;
                }

                if ($descriptor) {
                    $pathParts[] = $descriptor;
                }
                $pathParts[] = $filename;
                $filePath = implode('/', $pathParts);

                // Crear el archivo físico si no existe
                if (!$disk->exists($filePath)) {
                    $disk->put($filePath, $content);
                }

                // Crear el registro del archivo
                $document->files()->updateOrCreate(
                    [
                        'path' => $filePath,
                        'version' => $version,
                    ],
                    [
                        'mime' => 'Texto',
                        'file_size' => strlen($content),
                    ]
                );
            }
        }
    }

    private function getCategoryForSection(string $section): ?Category
    {
        return match ($section) {
            'Planos' => Category::Blueprint,
            'Manuales' => Category::Manual,
            'Reportes' => Category::Report,
            'Especificaciones Tecnicas' => Category::Specs,
            'Ofertas' => Category::Offer,
            'Fotos' => Category::Photo,
            default => null,
        };
    }
}

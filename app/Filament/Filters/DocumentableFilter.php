<?php

namespace App\Filament\Filters;

use App\Models\Equipment;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentManual;
use App\Models\EquipmentReport;
use App\Models\EquipmentStandard;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\Part;
use App\Models\Person;
use App\Models\Supplier;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtro "Pertenece a" de Documentos, agrupado por entidad lógica.
 *
 * Un "Equipo" puede tener documentos cuyo documentable sea el propio equipo o
 * cualquiera de sus sub-entidades (planos, hojas de datos, reportes, etc.).
 * Al elegir "Equipos" deben verse TODOS esos documentos del equipo.
 */
class DocumentableFilter
{
    private const EQUIPMENT_TYPES = [
        Equipment::class,
        EquipmentDataSheet::class,
        EquipmentBlueprint::class,
        EquipmentCatalog::class,
        EquipmentManual::class,
        EquipmentTechnicalSpecification::class,
        EquipmentStandard::class,
        EquipmentFieldQuery::class,
        EquipmentReport::class,
    ];

    public static function make(): Filter
    {
        return Filter::make('documentable')
            ->columnSpanFull()
            ->columns(2)
            ->schema([
                Select::make('documentable_type')
                    ->label('Pertenece a')
                    ->placeholder('Todos')
                    ->options([
                        'equipos' => 'Equipos',
                        'repuestos' => 'Repuestos',
                        'proveedores' => 'Proveedores',
                        'contactos' => 'Contactos',
                    ])
                    ->live(),
                Select::make('documentable_id')
                    ->searchable()
                    ->hidden(fn (Get $get) => ! $get('documentable_type'))
                    ->label('Registro')
                    ->options(function (Get $get) {
                        return match ($get('documentable_type')) {
                            'equipos' => Equipment::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all(),
                            'repuestos' => Part::query()
                                ->orderBy('part_number')
                                ->pluck('part_number', 'id')
                                ->all(),
                            'proveedores' => Supplier::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all(),
                            'contactos' => Person::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all(),
                            default => [],
                        };
                    }),
            ])
            ->query(function (Builder $query, array $data): Builder {
                $group = $data['documentable_type'] ?? null;
                $id = $data['documentable_id'] ?? null;

                if (! $group) {
                    return $query;
                }

                $types = match ($group) {
                    'equipos' => self::EQUIPMENT_TYPES,
                    'repuestos' => [Part::class],
                    'proveedores' => [Supplier::class],
                    'contactos' => [Person::class],
                    default => [],
                };

                $query->whereIn('documentable_type', $types);

                if ($id) {
                    if ($group === 'equipos') {
                        // Documentos directos del equipo + documentos de sus sub-entidades.
                        $query->where(function (Builder $q) use ($id) {
                            $q->where(function (Builder $q2) use ($id) {
                                $q2->where('documentable_type', Equipment::class)
                                    ->where('documentable_id', $id);
                            });

                            foreach (self::EQUIPMENT_TYPES as $type) {
                                if ($type === Equipment::class) {
                                    continue;
                                }
                                $table = (new $type)->getTable();
                                $q->orWhere(function (Builder $q3) use ($type, $table, $id) {
                                    $q3->where('documentable_type', $type)
                                        ->whereIn('documentable_id', function ($sub) use ($table, $id) {
                                            $sub->select('id')
                                                ->from($table)
                                                ->where('equipment_id', $id);
                                        });
                                });
                            }
                        });
                    } else {
                        $query->where('documentable_id', $id);
                    }
                }

                return $query;
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                $group = $data['documentable_type'] ?? null;
                $id = $data['documentable_id'] ?? null;

                if ($group) {
                    $label = match ($group) {
                        'equipos' => 'Equipos',
                        'repuestos' => 'Repuestos',
                        'proveedores' => 'Proveedores',
                        'contactos' => 'Contactos',
                        default => $group,
                    };
                    $indicators[] = Indicator::make("Pertenece a: {$label}")
                        ->removeField('documentable_type');
                }

                if ($group && $id) {
                    $name = match ($group) {
                        'equipos' => Equipment::find($id, ['name'])?->name,
                        'repuestos' => Part::find($id, ['part_number'])?->part_number,
                        'proveedores' => Supplier::find($id, ['name'])?->name,
                        'contactos' => Person::find($id, ['name'])?->name,
                        default => null,
                    };

                    if ($name) {
                        $indicators[] = Indicator::make("Registro: {$name}")
                            ->removeField('documentable_id');
                    }
                }

                return $indicators;
            });
    }
}
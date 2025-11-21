<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;

class DocumentsChart extends ChartWidget
{
    protected ?string $heading = 'Espacio ocupado por documentos';
    protected ?string $maxHeight = '200px';

    public function getColumnSpan(): array|int|string
    {
        return [
            'sm' => 1,
            'md' => 2,
        ];
    }

    protected function getData(): array
    {
        $types = [
            'App\Models\Equipment' => 'Equipos',
            'App\Models\Project' => 'Proyectos',
            'App\Models\Part' => 'Repuestos',
            'App\Models\Client' => 'Clientes',
            'App\Models\Supplier' => 'Proveedores',
            'App\Models\People' => 'Contactos',
        ];

        $counts = Document::whereIn('documentable_type', array_keys($types))
            ->selectRaw('documentable_type, COUNT(*) as total')
            ->groupBy('documentable_type')
            ->pluck('total', 'documentable_type')
            ->toArray();

        $labels = array_values($types);
        $data = [];

        foreach ($types as $key => $_label) {
            $data[] = isset($counts[$key]) ? (int) $counts[$key] : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Documentos',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

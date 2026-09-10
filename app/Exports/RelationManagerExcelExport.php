<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Exporta una tabla a Excel con presentación mejorada:
 * - Primera fila: título (nombre del equipo/repuesto + tabla) fusionado y centrado.
 * - Segunda fila: encabezados con fondo y fuente blanca.
 * - Cuerpo con bordes, ajuste de texto y bandas alternas.
 * - Auto-ancho de columnas con mínimo legible.
 */
class RelationManagerExcelExport implements FromArray, ShouldAutoSize, WithTitle, WithEvents
{
    public function __construct(
        private readonly string $title,
        private readonly array $headings,
        private readonly array $rows,
    ) {
    }

    public function array(): array
    {
        return [
            [$this->title],
            $this->headings,
            ...array_values($this->rows),
        ];
    }

    public function title(): string
    {
        // El nombre de hoja de Excel tiene un máximo de 31 caracteres.
        return (string) Str::of($this->title)->limit(31, '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $colCount = max(1, count($this->headings));
                $lastCol = Coordinate::stringFromColumnIndex($colCount);

                $dataCount = count($this->rows);
                $lastRow = $dataCount + 2; // fila 1 = título, fila 2 = encabezados

                // --- Título fusionado y centrado ---
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'name' => 'Calibri',
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1F4E78'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // --- Encabezados ---
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'name' => 'Calibri',
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2E75B6'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // --- Cuerpo ---
                if ($dataCount > 0) {
                    $sheet->getStyle("A3:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFB4B4B4'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                    ]);

                    // Bandas alternas para facilitar la lectura.
                    for ($row = 3; $row <= $lastRow; $row += 2) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF2F6FB'],
                            ],
                        ]);
                    }
                }

                // --- Ancho mínimo por columna (la primera no debe inflarse por el título) ---
                for ($i = 1; $i <= $colCount; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i);
                    $current = $sheet->getColumnDimension($col)->getWidth();
                    if ($i === 1) {
                        if (!$current || $current > 22) {
                            $sheet->getColumnDimension($col)->setWidth(22);
                        }
                    } elseif (!$current || $current < 16) {
                        $sheet->getColumnDimension($col)->setWidth(16);
                    }
                }
            },
        ];
    }
}
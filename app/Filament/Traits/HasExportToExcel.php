<?php

namespace App\Filament\Traits;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait para agregar botón de exportación a Excel en subtabs de equipos.
 * 
 * USO: En la subclase, agregar:
 *   use HasExportToExcel;
 *   
 *   protected static function getExportColumns(): array
 *   {
 *       return ['col1', 'col2']; // Nombres de columna en la BD
 *   }
 *   
 *   protected static function getExportLabels(): array
 *   {
 *       return ['Columna 1', 'Columna 2']; // Encabezados del Excel
 *   }
 */
trait HasExportToExcel
{
    /**
     * Crea la acción de exportación para agregar en toolbarActions.
     */
    public static function getExportAction(): Action
    {
        return Action::make('export')
            ->label('Exportar')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function ($livewire) {
                $query = $livewire->getFilteredTableQuery();
                $records = $query->get();
                $ownerName = Str::slug($livewire->getOwnerRecord()->name ?? 'registro');
                $sectionName = Str::slug((string) (static::$title ?? 'registro'));
                $fileName = "{$ownerName}-{$sectionName}.xlsx";

                $columns = static::getExportColumns();
                $labels = static::getExportLabels();

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new class($records, $columns, $labels) implements
                        \Maatwebsite\Excel\Concerns\FromCollection,
                        \Maatwebsite\Excel\Concerns\WithHeadings
                    {
                        protected $records;
                        protected $columns;
                        protected $labels;

                        public function __construct($records, $columns, $labels)
                        {
                            $this->records = $records;
                            $this->columns = $columns;
                            $this->labels = $labels;
                        }

                        public function collection()
                        {
                            return $this->records->map(function ($record) {
                                $row = [];
                                foreach ($this->columns as $col) {
                                    $value = data_get($record, $col);
                                    if ($value instanceof \Carbon\Carbon) {
                                        $value = $value->format('d/m/Y');
                                    }
                                    $row[] = $value;
                                }
                                return $row;
                            });
                        }

                        public function headings(): array
                        {
                            return $this->labels;
                        }
                    },
                    $fileName
                );
            });
    }

    /**
     * Agrega el botón exportar a la configuración del table builder.
     * Llamar desde el método table() de la subclase.
     * 
     * Ejemplo en table():
     *   $table = parent::table($table);
     *   // ... otras configuraciones
     *   return static::withExport($table);
     */
    protected static function withExport($table)
    {
        return $table->toolbarActions([
            static::getExportAction(),
        ]);
    }
}
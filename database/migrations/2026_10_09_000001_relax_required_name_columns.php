<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relaja columnas que en UI dejaban de ser obligatorias:
     * - equipment.name / parts.name (unique pero deben aceptar "" y null)
     * - people.name, documents.name, y metadata name/number
     * Mantiene integridad del resto de flujos.
     */
    public function up(): void
    {
        // Alterar unique constraints para name (equipment, parts)
        $this->dropUniqueQuietly('equipment', 'equipment_name_unique');
        $this->dropUniqueQuietly('parts', 'parts_name_unique');

        // Cambiar a nullable conservando el tipo string.
        $tableColumns = [
            'equipment' => ['name'],
            'parts' => ['name'],
            'people' => ['name'],
            'documents' => ['name'],
            'equipment_data_sheets' => ['sheet_number'],
            'equipment_blueprints' => ['blueprint_number', 'name'],
            'equipment_catalogs' => ['document_type', 'name'],
            'equipment_technical_specifications' => ['revision_name'],
            'equipment_standards' => ['name'],
            'equipment_field_queries' => ['document_name', 'document_type'],
            'equipment_reports' => ['document_name', 'document_type'],
        ];

        foreach ($tableColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $table) {
                foreach ($columns as $column) {
                    if (! Schema::hasColumn($blueprint->getTable(), $column)) {
                        continue;
                    }

                    // Preserva longitudes no estándar para evitar truncar datos existentes.
                    $length = ($table === 'documents' && $column === 'name') ? 511 : null;

                    if ($length !== null) {
                        $blueprint->string($column, $length)->nullable()->change();
                    } else {
                        $blueprint->string($column)->nullable()->change();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reescalar a NOT NULL es inseguro; no forzamos cambios destructivos.
    }

    private function dropUniqueQuietly(string $table, string $index): void
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                // SQLite no permite drop index simple sobre unique inline; se recrea tabla.
                // Se omite para no romper; la columna seguirá unique pero nullable.
                return;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropUnique($index);
            });
        } catch (Throwable) {
            // El índice puede no existir con ese nombre; se ignora.
        }
    }
};
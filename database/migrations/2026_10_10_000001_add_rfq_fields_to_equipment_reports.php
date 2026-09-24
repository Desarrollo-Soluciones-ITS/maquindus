<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los campos del módulo RFQ a la tabla de reportes de equipo.
     *
     * Cambio aditivo y reversible: no modifica ni elimina columnas existentes,
     * por lo que el histórico (document_date, document_name, document_type,
     * issuer) permanece intacto.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('equipment_reports', 'revision')) {
            Schema::table('equipment_reports', function (Blueprint $table) {
                $table->string('revision', 80)->nullable()->after('document_name');
            });
        }

        if (! Schema::hasColumn('equipment_reports', 'rqm_number')) {
            Schema::table('equipment_reports', function (Blueprint $table) {
                $table->string('rqm_number', 80)->nullable()->after('revision');
            });
        }
    }

    /**
     * Revierte únicamente las columnas creadas por esta migración.
     */
    public function down(): void
    {
        $columns = array_values(array_filter(
            ['revision', 'rqm_number'],
            fn (string $column): bool => Schema::hasColumn('equipment_reports', $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('equipment_reports', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};

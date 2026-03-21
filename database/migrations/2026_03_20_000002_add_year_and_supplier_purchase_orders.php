<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('equipment', 'year')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->unsignedSmallInteger('year')->nullable()->after('type');
            });
        }

        if (!Schema::hasTable('supplier_purchase_orders')) {
            Schema::create('supplier_purchase_orders', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('order_no', 80)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('equipment_supplier_purchase_order')) {
            Schema::create('equipment_supplier_purchase_order', function (Blueprint $table) {
                $table->uuid('equipment_id');
                $table->uuid('supplier_purchase_order_id');
                $table->primary(['equipment_id', 'supplier_purchase_order_id']);
                $table->foreign('equipment_id', 'eqspo_eq_fk')->references('id')->on('equipment')->onDelete('cascade');
                $table->foreign('supplier_purchase_order_id', 'eqspo_spo_fk')->references('id')->on('supplier_purchase_orders')->onDelete('cascade');
            });
        } else {
            if (!$this->constraintExists('equipment_supplier_purchase_order', 'eqspo_eq_fk')
                && !$this->hasForeignForColumn('equipment_supplier_purchase_order', 'equipment_id')) {
                Schema::table('equipment_supplier_purchase_order', function (Blueprint $table) {
                    $table->foreign('equipment_id', 'eqspo_eq_fk')->references('id')->on('equipment')->onDelete('cascade');
                });
            }

            if (!$this->constraintExists('equipment_supplier_purchase_order', 'eqspo_spo_fk')
                && !$this->hasForeignForColumn('equipment_supplier_purchase_order', 'supplier_purchase_order_id')) {
                Schema::table('equipment_supplier_purchase_order', function (Blueprint $table) {
                    $table->foreign('supplier_purchase_order_id', 'eqspo_spo_fk')->references('id')->on('supplier_purchase_orders')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('equipment_supplier_purchase_order')) {
            Schema::table('equipment_supplier_purchase_order', function (Blueprint $table) {
                try {
                    $table->dropForeign('eqspo_eq_fk');
                } catch (Throwable) {
                }

                try {
                    $table->dropForeign('eqspo_spo_fk');
                } catch (Throwable) {
                }
            });
        }

        Schema::dropIfExists('equipment_supplier_purchase_order');
        Schema::dropIfExists('supplier_purchase_orders');

        if (Schema::hasColumn('equipment', 'year')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('year');
            });
        }
    }

    private function constraintExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->exists();
    }

    private function hasForeignForColumn(string $table, string $column): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->deleteCustomerPermissions();
        $this->deleteCustomerSearchIndexEntries();
        $this->replaceEquipmentYearWithManufacturingDate();
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('rif')->unique();
                $table->string('name')->unique();
                $table->string('email')->unique();
                $table->string('phone')->unique();
                $table->string('about')->nullable();
                $table->string('address');
                $table->foreignUuid('country_id')->constrained();
                $table->foreignUuid('state_id')->nullable()->constrained();
                $table->foreignUuid('city_id')->nullable()->constrained();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'contact_name')) {
                    $table->string('contact_name')->nullable();
                }

                if (!Schema::hasColumn('customers', 'contact_position')) {
                    $table->string('contact_position')->nullable();
                }

                if (!Schema::hasColumn('customers', 'contact_phone')) {
                    $table->string('contact_phone')->nullable();
                }
            });
        }

        if (!Schema::hasColumn('equipment', 'year')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->unsignedSmallInteger('year')->nullable()->after('type');
            });
        }

        if (Schema::hasColumn('equipment', 'manufacturing_date')) {
            DB::table('equipment')
                ->whereNotNull('manufacturing_date')
                ->update([
                    'year' => DB::raw('YEAR(manufacturing_date)'),
                ]);

            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('manufacturing_date');
            });
        }
    }

    private function deleteCustomerPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->where('slug', 'like', 'customers.%')
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        }

        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')->whereIn('permission_id', $permissionIds)->delete();
        }

        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }

    private function deleteCustomerSearchIndexEntries(): void
    {
        try {
            $searchConnection = DB::connection('search');
            if ($searchConnection->getSchemaBuilder()->hasTable('search_index')) {
                $searchConnection->table('search_index')
                    ->where('model_type', 'App\\Models\\Customer')
                    ->delete();
            }
        } catch (\Throwable) {
        }
    }

    private function replaceEquipmentYearWithManufacturingDate(): void
    {
        if (!Schema::hasColumn('equipment', 'manufacturing_date')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->date('manufacturing_date')->nullable()->after('type');
            });
        }

        if (Schema::hasColumn('equipment', 'year')) {
            DB::statement("UPDATE equipment SET manufacturing_date = COALESCE(manufacturing_date, STR_TO_DATE(CONCAT(year, '-01-01'), '%Y-%m-%d')) WHERE year IS NOT NULL");
        }
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->deleteLegacyProjectData();
        $this->dropActivityProjectColumn();
        $this->dropProjectTables();
        $this->deleteProjectPermissions();
        $this->deleteProjectSearchIndexEntries();
    }

    public function down(): void
    {
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->text('about')->nullable();
                $table->date('start')->nullable();
                $table->date('end')->nullable();
                $table->string('status')->nullable();
                $table->uuid('customer_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('person_project')) {
            Schema::create('person_project', function (Blueprint $table) {
                $table->foreignUuid('person_id')->constrained();
                $table->foreignUuid('project_id')->constrained('projects');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('equipment_project')) {
            Schema::create('equipment_project', function (Blueprint $table) {
                $table->foreignUuid('equipment_id')->constrained();
                $table->foreignUuid('project_id')->constrained('projects');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('part_project')) {
            Schema::create('part_project', function (Blueprint $table) {
                $table->foreignUuid('part_id')->constrained('parts');
                $table->foreignUuid('project_id')->constrained('projects');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('project_purchase_order')) {
            Schema::create('project_purchase_order', function (Blueprint $table) {
                $table->uuid('project_id');
                $table->uuid('purchase_order_id');
                $table->primary(['project_id', 'purchase_order_id']);
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('activities') && !Schema::hasColumn('activities', 'project_id')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->foreignUuid('project_id')->nullable()->after('comment')->constrained('projects');
            });
        }
    }

    private function deleteLegacyProjectData(): void
    {
        if (Schema::hasTable('documents')) {
            $projectDocumentIds = DB::table('documents')
                ->where('documentable_type', 'App\\Models\\Project')
                ->pluck('id');

            if ($projectDocumentIds->isNotEmpty() && Schema::hasTable('files')) {
                DB::table('files')->whereIn('document_id', $projectDocumentIds)->delete();
            }

            DB::table('documents')->where('documentable_type', 'App\\Models\\Project')->delete();
        }

        if (Schema::hasTable('activities')) {
            if (Schema::hasTable('activity_person')) {
                DB::table('activity_person')->delete();
            }

            DB::table('activities')->delete();
        }

        $activityLogTable = config('activitylog.table_name', 'activity_log');
        if (Schema::hasTable($activityLogTable)) {
            DB::table($activityLogTable)
                ->where('subject_type', 'App\\Models\\Project')
                ->orWhere('log_name', 'Proyectos')
                ->delete();
        }
    }

    private function dropActivityProjectColumn(): void
    {
        if (!Schema::hasTable('activities') || !Schema::hasColumn('activities', 'project_id')) {
            return;
        }

        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }

    private function dropProjectTables(): void
    {
        Schema::dropIfExists('project_purchase_order');
        Schema::dropIfExists('equipment_project');
        Schema::dropIfExists('part_project');
        Schema::dropIfExists('person_project');
        Schema::dropIfExists('projects');
    }

    private function deleteProjectPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->where('slug', 'like', 'projects.%')
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        }

        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }

    private function deleteProjectSearchIndexEntries(): void
    {
        try {
            $searchConnection = DB::connection('search');
            if ($searchConnection->getSchemaBuilder()->hasTable('search_index')) {
                $searchConnection->table('search_index')
                    ->where('model_type', 'App\\Models\\Project')
                    ->delete();
            }
        } catch (\Throwable) {
        }
    }
};
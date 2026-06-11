<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('areas')) {
            return;
        }

        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('region_id');
            }
            if (!Schema::hasColumn('areas', 'visit_days')) {
                $table->json('visit_days')->nullable()->after('leader_id');
            }
            if (!Schema::hasColumn('areas', 'visit_frequency')) {
                $table->string('visit_frequency', 30)->nullable()->default('weekly')->after('visit_days');
            }
        });

        $this->addIndexIfMissing('areas', 'organization_id', 'areas_organization_id_index');
        $this->backfillAreasFromRegions();
    }

    public function down()
    {
        // Intentionally left blank to avoid removing route master-data fields.
    }

    private function backfillAreasFromRegions(): void
    {
        if (!Schema::hasTable('regions') || !Schema::hasColumn('areas', 'region_id')) {
            return;
        }

        if (Schema::hasColumn('areas', 'organization_id') && Schema::hasColumn('regions', 'organization_id')) {
            DB::table('areas')
                ->join('regions', 'areas.region_id', '=', 'regions.id')
                ->whereNull('areas.organization_id')
                ->whereNotNull('regions.organization_id')
                ->update(['areas.organization_id' => DB::raw('regions.organization_id')]);
        }

        if (Schema::hasColumn('areas', 'tenant_id') && Schema::hasColumn('regions', 'tenant_id')) {
            DB::table('areas')
                ->join('regions', 'areas.region_id', '=', 'regions.id')
                ->whereNull('areas.tenant_id')
                ->whereNotNull('regions.tenant_id')
                ->update(['areas.tenant_id' => DB::raw('regions.tenant_id')]);
        }

        if (Schema::hasColumn('areas', 'visit_frequency')) {
            DB::table('areas')
                ->whereNull('visit_frequency')
                ->update(['visit_frequency' => 'weekly']);
        }
    }

    private function addIndexIfMissing(string $tableName, string $columnName, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists && Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
                $table->index($columnName, $indexName);
            });
        }
    }
};

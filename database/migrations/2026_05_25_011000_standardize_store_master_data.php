<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('stores')) {
            return;
        }

        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'store_type')) {
                $table->string('store_type', 30)->nullable()->default('main')->after('code');
            }
            if (!Schema::hasColumn('stores', 'stock_tracking_mode')) {
                $table->string('stock_tracking_mode', 30)->nullable()->default('tracked')->after('store_type');
            }
            if (!Schema::hasColumn('stores', 'transfer_policy')) {
                $table->string('transfer_policy', 30)->nullable()->default('in_out')->after('stock_tracking_mode');
            }
            if (!Schema::hasColumn('stores', 'opening_inventory_status')) {
                $table->string('opening_inventory_status', 30)->nullable()->default('open')->after('transfer_policy');
            }
            if (!Schema::hasColumn('stores', 'opening_inventory_locked_at')) {
                $table->timestamp('opening_inventory_locked_at')->nullable()->after('opening_inventory_status');
            }
            if (!Schema::hasColumn('stores', 'opening_inventory_locked_by')) {
                $table->unsignedBigInteger('opening_inventory_locked_by')->nullable()->after('opening_inventory_locked_at');
            }
        });

        $this->defaultMissingValues();
        $this->addIndexIfMissing('stores', 'store_type', 'stores_store_type_index');
        $this->addIndexIfMissing('stores', 'opening_inventory_status', 'stores_opening_inventory_status_index');
    }

    public function down()
    {
        // Intentionally left blank to avoid removing warehouse master-data from production.
    }

    private function defaultMissingValues(): void
    {
        DB::table('stores')->whereNull('store_type')->update(['store_type' => 'main']);
        DB::table('stores')->whereNull('stock_tracking_mode')->update(['stock_tracking_mode' => 'tracked']);
        DB::table('stores')->whereNull('transfer_policy')->update(['transfer_policy' => 'in_out']);
        DB::table('stores')->whereNull('opening_inventory_status')->update(['opening_inventory_status' => 'open']);
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

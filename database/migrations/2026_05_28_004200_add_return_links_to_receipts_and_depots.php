<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('receipts')) {
            Schema::table('receipts', function (Blueprint $table) {
                if (!Schema::hasColumn('receipts', 'return_source_receipt_id')) {
                    $table->unsignedInteger('return_source_receipt_id')->nullable()->after('cancellation_reason');
                }
                if (!Schema::hasColumn('receipts', 'return_reason')) {
                    $table->text('return_reason')->nullable()->after('return_source_receipt_id');
                }
            });

            $this->addIndexIfMissing('receipts', ['return_source_receipt_id'], 'receipts_return_source_index');
        }

        if (Schema::hasTable('depots')) {
            Schema::table('depots', function (Blueprint $table) {
                if (!Schema::hasColumn('depots', 'source_depot_id')) {
                    $table->unsignedInteger('source_depot_id')->nullable()->after('receipt_id');
                }
                if (!Schema::hasColumn('depots', 'return_reason')) {
                    $table->text('return_reason')->nullable()->after('tracking_notes');
                }
            });

            $this->addIndexIfMissing('depots', ['source_depot_id'], 'depots_source_depot_index');
            $this->addIndexIfMissing('depots', ['receipt_id', 'source_depot_id'], 'depots_receipt_source_depot_index');
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep return audit links intact.
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'original_voucher_id')) {
                $table->unsignedBigInteger('original_voucher_id')->nullable()->after('source_id');
            }
            if (!Schema::hasColumn('vouchers', 'reversal_voucher_id')) {
                $table->unsignedBigInteger('reversal_voucher_id')->nullable()->after('original_voucher_id');
            }
            if (!Schema::hasColumn('vouchers', 'reversal_reason')) {
                $table->text('reversal_reason')->nullable()->after('description');
            }
            if (!Schema::hasColumn('vouchers', 'reversed_at')) {
                $table->timestamp('reversed_at')->nullable()->after('reversal_reason');
            }
            if (!Schema::hasColumn('vouchers', 'reversed_by')) {
                $table->unsignedInteger('reversed_by')->nullable()->after('reversed_at');
            }
            if (!Schema::hasColumn('vouchers', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('reversed_by');
            }
            if (!Schema::hasColumn('vouchers', 'cancelled_by')) {
                $table->unsignedInteger('cancelled_by')->nullable()->after('cancelled_at');
            }
        });

        $this->addIndexIfMissing('vouchers', ['original_voucher_id'], 'vouchers_original_voucher_index');
        $this->addIndexIfMissing('vouchers', ['reversal_voucher_id'], 'vouchers_reversal_voucher_index');
    }

    public function down(): void
    {
        // Non-destructive migration: keep reversal audit data intact.
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

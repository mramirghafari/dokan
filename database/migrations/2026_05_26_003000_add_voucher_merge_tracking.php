<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'merged_into_voucher_id')) {
                $table->unsignedBigInteger('merged_into_voucher_id')->nullable()->index()->after('reversal_voucher_id');
            }

            if (!Schema::hasColumn('vouchers', 'merged_at')) {
                $table->timestamp('merged_at')->nullable()->after('reversed_at');
            }

            if (!Schema::hasColumn('vouchers', 'merged_by')) {
                $table->unsignedInteger('merged_by')->nullable()->after('reversed_by');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive rollback: merge trace should remain available for audit.
    }
};

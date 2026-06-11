<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_portal_payments')) {
            return;
        }

        Schema::table('customer_portal_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_portal_payments', 'accounting_voucher_id')) {
                $table->unsignedBigInteger('accounting_voucher_id')->nullable()->after('reference_number');
            }

            if (!Schema::hasColumn('customer_portal_payments', 'gateway_settlement_status')) {
                $table->string('gateway_settlement_status', 40)->nullable()->after('accounting_voucher_id');
            }

            if (!Schema::hasColumn('customer_portal_payments', 'gateway_settled_at')) {
                $table->timestamp('gateway_settled_at')->nullable()->after('gateway_settlement_status');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: settlement trace is financial audit data.
    }
};

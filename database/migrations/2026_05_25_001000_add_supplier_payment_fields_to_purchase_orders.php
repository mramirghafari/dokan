<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_orders')) {
            return;
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'paid_amount')) {
                $table->decimal('paid_amount', 18, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('purchase_orders', 'payment_status')) {
                $table->string('payment_status', 30)->default('unpaid')->after('paid_amount');
            }
            if (!Schema::hasColumn('purchase_orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive migration: keep supplier settlement trace data intact.
    }
};

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
            if (!Schema::hasColumn('purchase_orders', 'procurement_source')) {
                $table->string('procurement_source', 50)->nullable()->index()->after('payment_status');
            }

            if (!Schema::hasColumn('purchase_orders', 'direct_supply_type')) {
                $table->string('direct_supply_type', 50)->nullable()->index()->after('procurement_source');
            }

            if (!Schema::hasColumn('purchase_orders', 'direct_supply_reason')) {
                $table->text('direct_supply_reason')->nullable()->after('direct_supply_type');
            }

            if (!Schema::hasColumn('purchase_orders', 'source_reference')) {
                $table->string('source_reference', 120)->nullable()->index()->after('direct_supply_reason');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive rollback: procurement trace must remain available for audit.
    }
};

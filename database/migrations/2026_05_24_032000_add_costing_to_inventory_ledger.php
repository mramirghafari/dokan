<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_movements', 'unit_cost')) {
                    $table->decimal('unit_cost', 18, 2)->default(0)->after('quantity_sub_unit');
                }
                if (!Schema::hasColumn('inventory_movements', 'total_cost')) {
                    $table->decimal('total_cost', 18, 2)->default(0)->after('unit_cost');
                }
                if (!Schema::hasColumn('inventory_movements', 'valuation_method')) {
                    $table->string('valuation_method', 40)->default('weighted_average')->after('total_cost');
                }
            });
        }

        if (Schema::hasTable('inventory_balances')) {
            Schema::table('inventory_balances', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_balances', 'unit_cost')) {
                    $table->decimal('unit_cost', 18, 2)->default(0)->after('quantity_sub_unit');
                }
                if (!Schema::hasColumn('inventory_balances', 'total_cost')) {
                    $table->decimal('total_cost', 18, 2)->default(0)->after('unit_cost');
                }
                if (!Schema::hasColumn('inventory_balances', 'last_costed_at')) {
                    $table->dateTime('last_costed_at')->nullable()->after('last_movement_at');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep inventory valuation audit data intact.
    }
};

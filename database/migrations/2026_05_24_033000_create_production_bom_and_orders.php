<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('production_formulas')) {
            Schema::create('production_formulas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('code', 80)->nullable()->index();
                $table->string('title', 190);
                $table->string('version', 40)->default('1');
                $table->decimal('base_quantity', 18, 3)->default(1);
                $table->decimal('standard_waste_percent', 8, 3)->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('production_formula_items')) {
            Schema::create('production_formula_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('production_formula_id')->index();
                $table->unsignedBigInteger('material_product_id')->index();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('waste_percent', 8, 3)->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('production_orders')) {
            Schema::create('production_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('store_id')->index();
                $table->unsignedBigInteger('production_formula_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('number', 80)->index();
                $table->string('date_fa', 20)->nullable();
                $table->date('date_en')->nullable();
                $table->string('status', 30)->default('approved')->index();
                $table->decimal('planned_quantity', 18, 3)->default(0);
                $table->decimal('actual_quantity', 18, 3)->default(0);
                $table->decimal('material_cost', 18, 2)->default(0);
                $table->decimal('finished_unit_cost', 18, 2)->default(0);
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->unsignedBigInteger('canceled_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'store_id', 'status'], 'production_orders_scope_status_index');
            });
        }

        if (!Schema::hasTable('production_order_items')) {
            Schema::create('production_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('production_order_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('warehouse_location_id')->default(0)->index();
                $table->string('line_type', 20)->index();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('unit_cost', 18, 2)->default(0);
                $table->decimal('total_cost', 18, 2)->default(0);
                $table->unsignedBigInteger('movement_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep production and costing audit data intact.
    }
};

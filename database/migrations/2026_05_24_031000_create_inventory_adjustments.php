<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_adjustments')) {
            Schema::create('inventory_adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('store_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('number', 60)->nullable()->index();
                $table->string('date_fa', 20)->nullable();
                $table->date('date_en')->nullable();
                $table->string('status', 30)->default('draft')->index();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->unsignedBigInteger('canceled_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'store_id', 'status'], 'inventory_adjustments_scope_status_index');
            });
        }

        if (!Schema::hasTable('inventory_adjustment_items')) {
            Schema::create('inventory_adjustment_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inventory_adjustment_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('warehouse_location_id')->default(0)->index();
                $table->decimal('system_quantity', 18, 3)->default(0);
                $table->decimal('counted_quantity', 18, 3)->default(0);
                $table->decimal('difference_quantity', 18, 3)->default(0);
                $table->decimal('unit_cost', 18, 2)->default(0);
                $table->decimal('amount', 18, 2)->default(0);
                $table->unsignedBigInteger('movement_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['inventory_adjustment_id', 'product_id'], 'inventory_adjustment_items_doc_product_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep inventory count and adjustment audit data intact.
    }
};

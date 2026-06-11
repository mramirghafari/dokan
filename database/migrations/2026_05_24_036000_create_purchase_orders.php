<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('supplier_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->unsignedInteger('receipt_id')->nullable();
                $table->string('order_number', 60)->nullable();
                $table->date('order_date_en')->nullable();
                $table->string('order_date_fa', 20)->nullable();
                $table->string('status', 30)->default('draft');
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedInteger('approved_by')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status'], 'purchase_orders_tenant_status_index');
                $table->index(['supplier_id'], 'purchase_orders_supplier_index');
                $table->index(['receipt_id'], 'purchase_orders_receipt_index');
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id');
                $table->unsignedInteger('product_id')->nullable();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('received_quantity', 18, 3)->default(0);
                $table->decimal('unit_price', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['purchase_order_id'], 'purchase_order_items_order_index');
                $table->index(['product_id'], 'purchase_order_items_product_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep procurement and receipt trace data intact.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id');
                $table->unsignedInteger('supplier_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->unsignedInteger('receipt_id')->nullable();
                $table->string('return_number', 60)->nullable();
                $table->date('return_date_en')->nullable();
                $table->string('return_date_fa', 20)->nullable();
                $table->string('status', 30)->default('approved');
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['purchase_order_id'], 'purchase_returns_order_index');
                $table->index(['tenant_id', 'status'], 'purchase_returns_tenant_status_index');
                $table->index(['receipt_id'], 'purchase_returns_receipt_index');
            });
        }

        if (!Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_return_id');
                $table->unsignedBigInteger('purchase_order_item_id')->nullable();
                $table->unsignedInteger('product_id')->nullable();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('unit_price', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['purchase_return_id'], 'purchase_return_items_return_index');
                $table->index(['purchase_order_item_id'], 'purchase_return_items_order_item_index');
                $table->index(['product_id'], 'purchase_return_items_product_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep purchase return, receipt, ledger and accounting trace data intact.
    }
};

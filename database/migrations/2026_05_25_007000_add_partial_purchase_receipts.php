<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_order_receipts')) {
            Schema::create('purchase_order_receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id');
                $table->unsignedInteger('receipt_id')->nullable();
                $table->string('receive_number', 80)->nullable();
                $table->date('receive_date_en')->nullable();
                $table->string('receive_date_fa', 20)->nullable();
                $table->string('status', 30)->default('approved');
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'purchase_order_id'], 'po_receipts_tenant_order_index');
                $table->index(['receipt_id'], 'po_receipts_receipt_index');
                $table->index(['receive_date_en'], 'po_receipts_date_index');
            });
        }

        if (!Schema::hasTable('purchase_order_receipt_items')) {
            Schema::create('purchase_order_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_order_receipt_id');
                $table->unsignedBigInteger('purchase_order_item_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('unit_price', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['purchase_order_receipt_id'], 'po_receipt_items_receipt_index');
                $table->index(['purchase_order_item_id'], 'po_receipt_items_order_item_index');
                $table->index(['product_id'], 'po_receipt_items_product_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receipt_items');
        Schema::dropIfExists('purchase_order_receipts');
    }
};

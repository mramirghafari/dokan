<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_requisitions')) {
            Schema::create('purchase_requisitions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->unsignedInteger('selected_supplier_id')->nullable();
                $table->unsignedBigInteger('selected_supplier_quotation_id')->nullable();
                $table->unsignedBigInteger('converted_purchase_order_id')->nullable();
                $table->string('request_number', 60)->nullable();
                $table->date('request_date_en')->nullable();
                $table->string('request_date_fa', 20)->nullable();
                $table->string('status', 30)->default('draft');
                $table->string('priority', 20)->default('normal');
                $table->text('description')->nullable();
                $table->unsignedInteger('requested_by')->nullable();
                $table->unsignedInteger('selected_by')->nullable();
                $table->timestamp('selected_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status'], 'purchase_requisitions_tenant_status_index');
                $table->index(['store_id'], 'purchase_requisitions_store_index');
                $table->index(['selected_supplier_id'], 'purchase_requisitions_supplier_index');
            });
        }

        if (!Schema::hasTable('purchase_requisition_items')) {
            Schema::create('purchase_requisition_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_requisition_id');
                $table->unsignedInteger('product_id')->nullable();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['purchase_requisition_id'], 'purchase_requisition_items_request_index');
                $table->index(['product_id'], 'purchase_requisition_items_product_index');
            });
        }

        if (!Schema::hasTable('supplier_quotations')) {
            Schema::create('supplier_quotations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_requisition_id');
                $table->unsignedInteger('supplier_id')->nullable();
                $table->string('quotation_number', 60)->nullable();
                $table->date('quotation_date_en')->nullable();
                $table->string('quotation_date_fa', 20)->nullable();
                $table->date('valid_until')->nullable();
                $table->string('status', 30)->default('draft');
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('selected_by')->nullable();
                $table->timestamp('selected_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['purchase_requisition_id'], 'supplier_quotations_request_index');
                $table->index(['supplier_id'], 'supplier_quotations_supplier_index');
                $table->index(['tenant_id', 'status'], 'supplier_quotations_tenant_status_index');
            });
        }

        if (!Schema::hasTable('supplier_quotation_items')) {
            Schema::create('supplier_quotation_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('supplier_quotation_id');
                $table->unsignedBigInteger('purchase_requisition_item_id')->nullable();
                $table->unsignedInteger('product_id')->nullable();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('unit_price', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['supplier_quotation_id'], 'supplier_quotation_items_quote_index');
                $table->index(['product_id'], 'supplier_quotation_items_product_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep procurement request and quotation trace data intact.
    }
};

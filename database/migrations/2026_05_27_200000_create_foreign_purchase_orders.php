<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('foreign_purchase_orders')) {
            Schema::create('foreign_purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id')->unique();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->unsignedBigInteger('currency_id')->nullable()->index();
                $table->string('import_number', 80)->nullable()->index();
                $table->string('proforma_number', 120)->nullable()->index();
                $table->string('contract_number', 120)->nullable()->index();
                $table->string('lc_number', 120)->nullable()->index();
                $table->string('customs_declaration_number', 120)->nullable()->index();
                $table->string('bill_of_lading_number', 120)->nullable()->index();
                $table->string('origin_country', 120)->nullable();
                $table->string('shipment_method', 80)->nullable();
                $table->string('status', 40)->default('draft')->index();
                $table->date('order_date_en')->nullable()->index();
                $table->string('order_date_fa', 20)->nullable();
                $table->date('expected_arrival_date_en')->nullable()->index();
                $table->string('expected_arrival_date_fa', 20)->nullable();
                $table->date('customs_date_en')->nullable()->index();
                $table->string('customs_date_fa', 20)->nullable();
                $table->decimal('exchange_rate', 20, 6)->default(1);
                $table->decimal('foreign_goods_amount', 20, 4)->default(0);
                $table->decimal('base_goods_amount', 20, 2)->default(0);
                $table->decimal('additional_cost_amount', 20, 2)->default(0);
                $table->decimal('customs_cost_amount', 20, 2)->default(0);
                $table->decimal('landed_cost_amount', 20, 2)->default(0);
                $table->decimal('allocated_amount', 20, 2)->default(0);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status'], 'foreign_purchase_orders_tenant_status_index');
            });
        }

        if (!Schema::hasTable('foreign_purchase_order_items')) {
            Schema::create('foreign_purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('foreign_purchase_order_id')->index();
                $table->unsignedBigInteger('purchase_order_item_id')->nullable()->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('foreign_unit_price', 20, 6)->default(0);
                $table->decimal('foreign_total_amount', 20, 4)->default(0);
                $table->decimal('base_goods_amount', 20, 2)->default(0);
                $table->decimal('allocated_cost_amount', 20, 2)->default(0);
                $table->decimal('landed_total_amount', 20, 2)->default(0);
                $table->decimal('landed_unit_cost', 20, 6)->default(0);
                $table->decimal('manual_allocation_amount', 20, 2)->nullable();
                $table->decimal('allocation_weight', 20, 6)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('foreign_purchase_order_costs')) {
            Schema::create('foreign_purchase_order_costs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('foreign_purchase_order_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->string('cost_type', 60)->default('other')->index();
                $table->string('title', 191);
                $table->date('cost_date_en')->nullable()->index();
                $table->string('cost_date_fa', 20)->nullable();
                $table->decimal('foreign_amount', 20, 4)->default(0);
                $table->decimal('exchange_rate', 20, 6)->default(1);
                $table->decimal('base_amount', 20, 2)->default(0);
                $table->string('allocation_basis', 40)->default('value')->index();
                $table->string('reference_number', 120)->nullable();
                $table->string('document_number', 120)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('foreign_purchase_order_documents')) {
            Schema::create('foreign_purchase_order_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('foreign_purchase_order_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('document_type', 60)->default('other')->index();
                $table->string('document_number', 120)->nullable()->index();
                $table->date('document_date_en')->nullable();
                $table->string('document_date_fa', 20)->nullable();
                $table->string('reference_number', 120)->nullable();
                $table->string('file_path')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep import order, customs documents, and landed cost traces intact.
    }
};

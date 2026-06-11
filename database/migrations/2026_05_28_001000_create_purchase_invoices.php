<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_invoices')) {
            Schema::create('purchase_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('purchase_order_id')->index();
                $table->string('invoice_number', 80)->index();
                $table->string('supplier_invoice_number', 120)->nullable()->index();
                $table->date('invoice_date_en')->nullable()->index();
                $table->string('invoice_date_fa', 20)->nullable();
                $table->string('status', 30)->default('approved')->index();
                $table->decimal('goods_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->decimal('price_variance_amount', 18, 2)->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->unsignedBigInteger('canceled_by')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'supplier_id', 'invoice_date_en'], 'purchase_invoice_supplier_index');
                $table->index(['purchase_order_id', 'status'], 'purchase_invoice_order_status_index');
            });
        }

        if (!Schema::hasTable('purchase_invoice_items')) {
            Schema::create('purchase_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('purchase_invoice_id')->index();
                $table->unsignedBigInteger('purchase_order_item_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('order_unit_price', 18, 2)->default(0);
                $table->decimal('invoice_unit_price', 18, 2)->default(0);
                $table->decimal('goods_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->decimal('price_variance_amount', 18, 2)->default(0);
                $table->string('match_status', 30)->default('matched')->index();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: purchase invoices are financial audit data.
    }
};

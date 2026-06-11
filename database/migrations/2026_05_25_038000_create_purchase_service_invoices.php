<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_service_invoices')) {
            Schema::create('purchase_service_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('purchase_order_id')->nullable()->index();
                $table->unsignedBigInteger('receipt_id')->nullable()->index();
                $table->string('invoice_number', 80)->index();
                $table->string('invoice_type', 40)->default('service')->index();
                $table->date('invoice_date_en')->nullable()->index();
                $table->string('invoice_date_fa', 20)->nullable();
                $table->string('status', 30)->default('approved')->index();
                $table->decimal('subtotal_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('canceled_at')->nullable();
                $table->unsignedInteger('canceled_by')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'supplier_id', 'invoice_date_en'], 'purchase_service_invoice_supplier_index');
            });
        }

        if (!Schema::hasTable('purchase_service_invoice_items')) {
            Schema::create('purchase_service_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_service_invoice_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('purchase_order_item_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->unsignedBigInteger('expense_account_id')->nullable()->index();
                $table->string('cost_type', 50)->default('service')->index();
                $table->string('allocation_type', 40)->default('expense')->index();
                $table->string('title', 191);
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: purchase service invoices are accounting audit data.
    }
};

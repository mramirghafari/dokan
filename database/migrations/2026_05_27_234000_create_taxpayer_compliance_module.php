<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('taxpayer_settings')) {
            Schema::create('taxpayer_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('title')->default('تنظیمات سامانه مودیان');
                $table->string('send_mode', 30)->default('trusted_company')->index();
                $table->string('environment', 30)->default('sandbox')->index();
                $table->string('memory_id', 120)->nullable()->index();
                $table->string('branch_tax_code', 80)->nullable();
                $table->string('economic_number', 80)->nullable();
                $table->string('seller_national_id', 80)->nullable();
                $table->string('seller_postal_code', 30)->nullable();
                $table->string('endpoint_url')->nullable();
                $table->string('trusted_company_name')->nullable();
                $table->string('certificate_alias')->nullable();
                $table->boolean('auto_send')->default(false);
                $table->boolean('is_active')->default(true)->index();
                $table->json('extra_config')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('taxpayer_item_mappings')) {
            Schema::create('taxpayer_item_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('local_type', 40)->default('product')->index();
                $table->string('local_code', 120)->nullable()->index();
                $table->string('local_title');
                $table->string('tax_item_id', 120)->index();
                $table->string('tax_item_title')->nullable();
                $table->string('measurement_unit_code', 40)->nullable();
                $table->string('invoice_pattern', 40)->default('sales')->index();
                $table->decimal('default_tax_rate', 8, 4)->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('taxpayer_invoices')) {
            Schema::create('taxpayer_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('taxpayer_setting_id')->nullable()->index();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('invoice_number', 80)->index();
                $table->string('tax_id', 120)->nullable()->index();
                $table->string('reference_number', 120)->nullable()->index();
                $table->string('source_type', 120)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('source_number', 120)->nullable();
                $table->string('invoice_subject', 40)->default('main')->index();
                $table->string('invoice_pattern', 40)->default('sales')->index();
                $table->string('invoice_type', 40)->default('type_1')->index();
                $table->date('issue_date_en')->index();
                $table->string('issue_date_fa', 20)->nullable();
                $table->string('status', 30)->default('draft')->index();
                $table->string('send_mode', 30)->nullable();
                $table->string('memory_id', 120)->nullable();
                $table->string('branch_tax_code', 80)->nullable();
                $table->string('buyer_name')->nullable();
                $table->string('buyer_economic_number', 80)->nullable();
                $table->string('buyer_national_id', 80)->nullable();
                $table->string('buyer_postal_code', 30)->nullable();
                $table->string('buyer_address')->nullable();
                $table->decimal('subtotal_amount', 18, 2)->default(0);
                $table->decimal('discount_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('total_amount', 18, 2)->default(0);
                $table->json('payload_json')->nullable();
                $table->json('response_json')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('prepared_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->unsignedInteger('retry_count')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['source_type', 'source_id'], 'taxpayer_invoices_source_index');
                $table->index(['tenant_id', 'status'], 'taxpayer_invoices_tenant_status_index');
            });
        }

        if (!Schema::hasTable('taxpayer_invoice_items')) {
            Schema::create('taxpayer_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('taxpayer_invoice_id')->index('taxpayer_items_invoice_index');
                $table->unsignedBigInteger('source_item_id')->nullable()->index('taxpayer_items_source_item_index');
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('row_number', 40)->nullable();
                $table->string('item_code', 120)->nullable();
                $table->string('item_title');
                $table->string('tax_item_id', 120)->nullable()->index();
                $table->string('measurement_unit_code', 40)->nullable();
                $table->decimal('quantity', 18, 4)->default(0);
                $table->decimal('unit_price', 18, 2)->default(0);
                $table->decimal('gross_amount', 18, 2)->default(0);
                $table->decimal('discount_amount', 18, 2)->default(0);
                $table->decimal('tax_rate', 8, 4)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('net_amount', 18, 2)->default(0);
                $table->json('extra_data')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('taxpayer_submission_logs')) {
            Schema::create('taxpayer_submission_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('taxpayer_invoice_id')->index('taxpayer_logs_invoice_index');
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->string('action', 40)->index();
                $table->string('status_before', 30)->nullable();
                $table->string('status_after', 30)->nullable();
                $table->string('reference_number', 120)->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->text('message')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep taxpayer invoice, submission and legal audit traces intact.
    }
};

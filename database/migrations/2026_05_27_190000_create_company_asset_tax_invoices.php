<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_asset_tax_invoices')) {
            return;
        }

        Schema::create('company_asset_tax_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_asset_disposal_id')->unique();
            $table->unsignedBigInteger('company_asset_id')->index();
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->unsignedInteger('tenant_id')->nullable()->index();
            $table->unsignedInteger('organization_id')->nullable()->index();
            $table->string('invoice_number', 80)->nullable()->index();
            $table->string('tax_id', 120)->nullable()->index();
            $table->string('reference_number', 120)->nullable()->index();
            $table->date('issue_date_en')->index();
            $table->string('issue_date_fa', 20)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->decimal('sale_amount', 18, 2)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('buyer_name')->nullable();
            $table->string('buyer_economic_number', 80)->nullable();
            $table->string('buyer_national_id', 80)->nullable();
            $table->string('buyer_postal_code', 30)->nullable();
            $table->string('buyer_address')->nullable();
            $table->string('asset_code', 80)->nullable();
            $table->string('asset_name')->nullable();
            $table->json('payload_json')->nullable();
            $table->json('response_json')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        // Non-destructive migration: keep fixed asset tax invoice traces intact.
    }
};

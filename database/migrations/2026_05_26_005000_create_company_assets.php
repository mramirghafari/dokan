<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_assets')) {
            return;
        }

        Schema::create('company_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tenant_id')->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->unsignedInteger('store_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('custodian_employee_id')->nullable();
            $table->unsignedInteger('asset_account_id')->nullable();
            $table->unsignedInteger('accumulated_depreciation_account_id')->nullable();
            $table->unsignedInteger('depreciation_expense_account_id')->nullable();
            $table->string('asset_code', 80)->nullable();
            $table->string('plaque_number', 80)->nullable();
            $table->string('name', 180);
            $table->string('asset_category', 60)->default('office_equipment');
            $table->string('serial_number', 120)->nullable();
            $table->string('location', 180)->nullable();
            $table->date('acquisition_date_en')->nullable();
            $table->string('acquisition_date_fa', 20)->nullable();
            $table->date('in_service_date_en')->nullable();
            $table->string('in_service_date_fa', 20)->nullable();
            $table->decimal('acquisition_cost', 18, 2)->default(0);
            $table->decimal('salvage_value', 18, 2)->default(0);
            $table->unsignedInteger('useful_life_months')->nullable();
            $table->string('depreciation_method', 40)->default('straight_line');
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->string('status', 40)->default('active');
            $table->text('description')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status'], 'company_assets_tenant_status_index');
            $table->index(['tenant_id', 'asset_category'], 'company_assets_tenant_category_index');
            $table->index(['asset_code'], 'company_assets_code_index');
            $table->index(['plaque_number'], 'company_assets_plaque_index');
            $table->index(['store_id'], 'company_assets_store_index');
            $table->index(['cost_center_id'], 'company_assets_cost_center_index');
        });
    }

    public function down(): void
    {
        // Non-destructive rollback: company asset register is financial master data.
    }
};

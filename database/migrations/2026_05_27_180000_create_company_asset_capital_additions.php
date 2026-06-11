<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('company_asset_capital_additions')) {
            Schema::create('company_asset_capital_additions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_asset_id')->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->unsignedBigInteger('event_id')->nullable()->index();
                $table->string('addition_type', 40)->default('major_repair')->index();
                $table->date('addition_date_en')->index();
                $table->string('addition_date_fa', 20)->nullable();
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('asset_cost_before', 18, 2)->default(0);
                $table->decimal('asset_cost_after', 18, 2)->default(0);
                $table->unsignedBigInteger('asset_account_id')->nullable()->index();
                $table->unsignedBigInteger('credit_account_id')->nullable()->index();
                $table->string('supplier_name')->nullable();
                $table->string('reference_number')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep capital addition audit rows and posted vouchers intact.
    }
};

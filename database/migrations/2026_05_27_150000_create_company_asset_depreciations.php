<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_asset_depreciations')) {
            return;
        }

        Schema::create('company_asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_asset_id')->index();
            $table->unsignedInteger('tenant_id')->nullable()->index();
            $table->unsignedInteger('organization_id')->nullable()->index();
            $table->date('period_start_en')->index();
            $table->date('period_end_en')->index();
            $table->string('period_start_fa', 20)->nullable();
            $table->string('period_end_fa', 20)->nullable();
            $table->decimal('depreciable_amount', 18, 2)->default(0);
            $table->decimal('period_amount', 18, 2)->default(0);
            $table->decimal('accumulated_before', 18, 2)->default(0);
            $table->decimal('accumulated_after', 18, 2)->default(0);
            $table->decimal('book_value_before', 18, 2)->default(0);
            $table->decimal('book_value_after', 18, 2)->default(0);
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->string('status', 30)->default('posted')->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_asset_id', 'period_start_en', 'period_end_en'], 'asset_depreciation_period_unique');
        });
    }

    public function down(): void
    {
        // Non-destructive migration: keep depreciation and voucher audit data intact.
    }
};

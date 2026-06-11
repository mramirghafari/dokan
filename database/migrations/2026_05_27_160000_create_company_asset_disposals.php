<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_asset_disposals')) {
            return;
        }

        Schema::create('company_asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_asset_id')->index();
            $table->unsignedInteger('tenant_id')->nullable()->index();
            $table->unsignedInteger('organization_id')->nullable()->index();
            $table->string('disposal_type', 30)->index();
            $table->date('disposal_date_en')->index();
            $table->string('disposal_date_fa', 20)->nullable();
            $table->decimal('acquisition_cost', 18, 2)->default(0);
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->decimal('book_value', 18, 2)->default(0);
            $table->decimal('proceeds_amount', 18, 2)->default(0);
            $table->decimal('gain_amount', 18, 2)->default(0);
            $table->decimal('loss_amount', 18, 2)->default(0);
            $table->unsignedBigInteger('proceeds_account_id')->nullable()->index();
            $table->unsignedBigInteger('gain_account_id')->nullable()->index();
            $table->unsignedBigInteger('loss_account_id')->nullable()->index();
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->unsignedBigInteger('event_id')->nullable()->index();
            $table->string('status_before', 30)->nullable();
            $table->string('status_after', 30)->nullable();
            $table->string('buyer_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        // Non-destructive migration: keep fixed asset disposal and voucher audit data intact.
    }
};

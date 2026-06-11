<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('revenue_centers')) {
            Schema::create('revenue_centers', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('code', 60)->nullable();
                $table->string('name', 180);
                $table->string('center_type', 50)->default('branch');
                $table->string('manager_name', 180)->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'center_type'], 'revenue_centers_tenant_type_index');
                $table->index(['tenant_id', 'store_id'], 'revenue_centers_tenant_store_index');
                $table->index(['code'], 'revenue_centers_code_index');
            });
        }

        if (Schema::hasTable('voucher_items')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_items', 'revenue_center_id')) {
                    $table->unsignedBigInteger('revenue_center_id')->nullable()->after('cost_center_id')->index();
                }
            });
        }

        if (Schema::hasTable('voucher_template_items')) {
            Schema::table('voucher_template_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_template_items', 'revenue_center_id')) {
                    $table->unsignedBigInteger('revenue_center_id')->nullable()->after('cost_center_id')->index();
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: revenue center and voucher attribution data is audit/reporting data.
    }
};

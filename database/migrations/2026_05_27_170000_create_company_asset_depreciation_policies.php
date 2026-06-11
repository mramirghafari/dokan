<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('company_asset_depreciation_policies')) {
            Schema::create('company_asset_depreciation_policies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_asset_id')->index();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->date('effective_date_en')->index();
                $table->string('effective_date_fa', 20)->nullable();
                $table->string('depreciation_method', 30)->default('straight_line');
                $table->unsignedInteger('useful_life_months')->nullable();
                $table->decimal('salvage_value', 18, 2)->default(0);
                $table->decimal('annual_rate_percent', 8, 4)->nullable();
                $table->unsignedBigInteger('accumulated_depreciation_account_id')->nullable();
                $table->unsignedBigInteger('depreciation_expense_account_id')->nullable();
                $table->string('reason')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['company_asset_id', 'effective_date_en'], 'asset_depreciation_policy_effective_unique');
                $table->index('accumulated_depreciation_account_id', 'asset_dep_policy_acc_account_idx');
                $table->index('depreciation_expense_account_id', 'asset_dep_policy_exp_account_idx');
            });
        }

        if (Schema::hasTable('company_asset_depreciation_policies')) {
            Schema::table('company_asset_depreciation_policies', function (Blueprint $table) {
                if (!$this->indexExists('company_asset_depreciation_policies', 'asset_dep_policy_acc_account_idx')) {
                    $table->index('accumulated_depreciation_account_id', 'asset_dep_policy_acc_account_idx');
                }

                if (!$this->indexExists('company_asset_depreciation_policies', 'asset_dep_policy_exp_account_idx')) {
                    $table->index('depreciation_expense_account_id', 'asset_dep_policy_exp_account_idx');
                }
            });
        }

        if (Schema::hasTable('company_asset_depreciations') && !Schema::hasColumn('company_asset_depreciations', 'policy_id')) {
            Schema::table('company_asset_depreciations', function (Blueprint $table) {
                $table->unsignedBigInteger('policy_id')->nullable()->after('company_asset_id')->index();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep asset depreciation policies and posted depreciation audit data intact.
    }

    private function indexExists(string $table, string $index): bool
    {
        return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])) > 0;
    }
};

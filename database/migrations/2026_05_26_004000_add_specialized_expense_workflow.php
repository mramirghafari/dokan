<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expense_types')) {
            Schema::table('expense_types', function (Blueprint $table) {
                if (!Schema::hasColumn('expense_types', 'workflow_type')) {
                    $table->string('workflow_type', 50)->default('standard')->index()->after('cost_behavior');
                }

                if (!Schema::hasColumn('expense_types', 'requires_approval')) {
                    $table->boolean('requires_approval')->default(false)->after('workflow_type');
                }

                if (!Schema::hasColumn('expense_types', 'capitalization_policy')) {
                    $table->string('capitalization_policy', 50)->default('expense')->index()->after('requires_approval');
                }
            });
        }

        if (!Schema::hasTable('operational_expenses')) {
            return;
        }

        Schema::table('operational_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('operational_expenses', 'specialized_kind')) {
                $table->string('specialized_kind', 50)->nullable()->index()->after('expense_type_id');
            }

            if (!Schema::hasColumn('operational_expenses', 'workflow_status')) {
                $table->string('workflow_status', 40)->default('approved')->index()->after('status');
            }

            if (!Schema::hasColumn('operational_expenses', 'allocation_target_type')) {
                $table->string('allocation_target_type', 50)->nullable()->index()->after('payment_status');
            }

            if (!Schema::hasColumn('operational_expenses', 'allocation_target_id')) {
                $table->unsignedBigInteger('allocation_target_id')->nullable()->index()->after('allocation_target_type');
            }

            if (!Schema::hasColumn('operational_expenses', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->index()->after('allocation_target_id');
            }

            if (!Schema::hasColumn('operational_expenses', 'project_code')) {
                $table->string('project_code', 100)->nullable()->index()->after('product_id');
            }

            if (!Schema::hasColumn('operational_expenses', 'contract_code')) {
                $table->string('contract_code', 120)->nullable()->index()->after('project_code');
            }

            if (!Schema::hasColumn('operational_expenses', 'workflow_note')) {
                $table->text('workflow_note')->nullable()->after('contract_code');
            }

            if (!Schema::hasColumn('operational_expenses', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('workflow_note');
            }

            if (!Schema::hasColumn('operational_expenses', 'approved_by')) {
                $table->unsignedInteger('approved_by')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('operational_expenses', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_by');
            }

            if (!Schema::hasColumn('operational_expenses', 'rejected_by')) {
                $table->unsignedInteger('rejected_by')->nullable()->after('rejected_at');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive rollback: specialized expense workflow trace is audit data.
    }
};

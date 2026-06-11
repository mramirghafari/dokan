<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_employee_performance_snapshots')) {
            Schema::create('crm_employee_performance_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->date('period_start');
                $table->date('period_end');
                $table->string('role_scope', 40)->default('mixed');
                $table->decimal('total_score', 5, 2)->default(0);
                $table->decimal('sales_score', 5, 2)->default(0);
                $table->decimal('support_score', 5, 2)->default(0);
                $table->decimal('followup_score', 5, 2)->default(0);
                $table->decimal('call_score', 5, 2)->default(0);
                $table->string('coaching_priority', 20)->default('normal');
                $table->json('metrics')->nullable();
                $table->json('strengths')->nullable();
                $table->json('risks')->nullable();
                $table->text('recommendation')->nullable();
                $table->timestamp('calculated_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['user_id', 'period_start', 'period_end', 'role_scope'], 'crm_perf_snapshot_user_period_unique');
                $table->index(['tenant_id', 'period_end', 'total_score'], 'crm_perf_snapshot_tenant_period_score_index');
                $table->index(['organization_id', 'period_end'], 'crm_perf_snapshot_org_period_index');
                $table->index(['coaching_priority', 'total_score'], 'crm_perf_snapshot_priority_score_index');
            });
        }

        if (!Schema::hasTable('crm_employee_coaching_plans')) {
            Schema::create('crm_employee_coaching_plans', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('performance_snapshot_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->unsignedBigInteger('coach_user_id')->nullable();
                $table->string('type', 40)->default('general');
                $table->string('priority', 20)->default('normal');
                $table->string('status', 30)->default('open');
                $table->string('title', 180);
                $table->string('target_metric', 80)->nullable();
                $table->decimal('target_value', 18, 2)->nullable();
                $table->timestamp('due_at')->nullable();
                $table->text('action_plan')->nullable();
                $table->text('outcome')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status', 'priority'], 'crm_coaching_tenant_status_priority_index');
                $table->index(['organization_id', 'status'], 'crm_coaching_org_status_index');
                $table->index(['user_id', 'status', 'due_at'], 'crm_coaching_user_status_due_index');
                $table->index(['coach_user_id', 'status'], 'crm_coaching_coach_status_index');
                $table->index(['performance_snapshot_id', 'status'], 'crm_coaching_snapshot_status_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep performance and coaching history intact.
    }
};

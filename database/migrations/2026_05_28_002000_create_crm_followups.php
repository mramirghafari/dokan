<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_followups')) {
            Schema::create('crm_followups', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('subject_type', 30)->default('customer');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->unsignedBigInteger('assigned_user_id')->nullable();
                $table->string('type', 40)->default('followup');
                $table->string('priority', 20)->default('normal');
                $table->string('status', 30)->default('open');
                $table->string('title', 180);
                $table->date('due_date_en')->nullable();
                $table->string('due_date_fa', 20)->nullable();
                $table->text('description')->nullable();
                $table->text('outcome')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status', 'due_date_en'], 'crm_followups_tenant_status_due_index');
                $table->index(['organization_id', 'status'], 'crm_followups_org_status_index');
                $table->index(['customer_id', 'status'], 'crm_followups_customer_status_index');
                $table->index(['employee_id', 'status'], 'crm_followups_employee_status_index');
                $table->index(['assigned_user_id', 'status'], 'crm_followups_assigned_status_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep CRM follow-up history intact.
    }
};

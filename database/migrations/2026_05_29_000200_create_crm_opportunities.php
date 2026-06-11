<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_opportunities')) {
            Schema::create('crm_opportunities', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('assigned_user_id')->nullable();
                $table->unsignedBigInteger('source_followup_id')->nullable();
                $table->string('code', 40)->nullable();
                $table->string('title', 180);
                $table->string('stage', 40)->default('new');
                $table->string('priority', 20)->default('normal');
                $table->string('status', 30)->default('open');
                $table->decimal('amount', 18, 2)->default(0);
                $table->unsignedTinyInteger('probability_percent')->default(0);
                $table->date('expected_close_date_en')->nullable();
                $table->string('expected_close_date_fa', 20)->nullable();
                $table->date('next_action_date_en')->nullable();
                $table->string('next_action_date_fa', 20)->nullable();
                $table->text('description')->nullable();
                $table->text('outcome')->nullable();
                $table->text('lost_reason')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status', 'stage'], 'crm_opportunities_tenant_status_stage_index');
                $table->index(['organization_id', 'status'], 'crm_opportunities_org_status_index');
                $table->index(['customer_id', 'status'], 'crm_opportunities_customer_status_index');
                $table->index(['assigned_user_id', 'status'], 'crm_opportunities_assigned_status_index');
                $table->index(['tenant_id', 'next_action_date_en'], 'crm_opportunities_tenant_next_action_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep CRM opportunity pipeline history intact.
    }
};

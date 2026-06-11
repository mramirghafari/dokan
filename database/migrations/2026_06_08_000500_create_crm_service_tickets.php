<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_service_tickets')) {
            Schema::create('crm_service_tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('assigned_user_id')->nullable();
                $table->string('code', 40)->nullable();
                $table->string('type', 40)->default('support');
                $table->string('channel', 40)->default('manual');
                $table->string('priority', 20)->default('normal');
                $table->string('status', 30)->default('open');
                $table->string('subject', 180);
                $table->string('contact_name', 180)->nullable();
                $table->string('contact_phone', 40)->nullable();
                $table->text('description')->nullable();
                $table->text('resolution')->nullable();
                $table->timestamp('due_at')->nullable();
                $table->timestamp('first_response_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedTinyInteger('satisfaction_score')->nullable();
                $table->text('satisfaction_note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status', 'priority'], 'crm_service_tickets_tenant_status_priority_index');
                $table->index(['organization_id', 'status'], 'crm_service_tickets_org_status_index');
                $table->index(['customer_id', 'status'], 'crm_service_tickets_customer_status_index');
                $table->index(['assigned_user_id', 'status'], 'crm_service_tickets_assignee_status_index');
                $table->index(['due_at', 'status'], 'crm_service_tickets_due_status_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep service desk history intact.
    }
};

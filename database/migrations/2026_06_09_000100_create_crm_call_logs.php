<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_call_logs')) {
            Schema::create('crm_call_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('service_ticket_id')->nullable();
                $table->unsignedBigInteger('assigned_user_id')->nullable();
                $table->string('code', 40)->nullable();
                $table->string('direction', 30)->default('outbound');
                $table->string('channel', 40)->default('phone');
                $table->string('status', 30)->default('open');
                $table->string('result', 40)->nullable();
                $table->string('priority', 20)->default('normal');
                $table->string('subject', 180);
                $table->string('phone_number', 40)->nullable();
                $table->string('contact_name', 180)->nullable();
                $table->timestamp('call_started_at')->nullable();
                $table->timestamp('call_ended_at')->nullable();
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->timestamp('next_action_at')->nullable();
                $table->unsignedTinyInteger('quality_score')->nullable();
                $table->string('recording_url', 500)->nullable();
                $table->text('notes')->nullable();
                $table->text('outcome')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status', 'direction'], 'crm_call_logs_tenant_status_direction_index');
                $table->index(['organization_id', 'status'], 'crm_call_logs_org_status_index');
                $table->index(['customer_id', 'call_started_at'], 'crm_call_logs_customer_started_index');
                $table->index(['service_ticket_id', 'status'], 'crm_call_logs_ticket_status_index');
                $table->index(['assigned_user_id', 'call_started_at'], 'crm_call_logs_assignee_started_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep call center history intact.
    }
};

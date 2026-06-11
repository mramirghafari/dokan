<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_leads')) {
            Schema::create('crm_leads', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('owner_user_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('opportunity_id')->nullable();
                $table->string('code', 40)->nullable();
                $table->string('name', 180);
                $table->string('company_name', 180)->nullable();
                $table->string('mobile', 30)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email', 160)->nullable();
                $table->string('city', 120)->nullable();
                $table->string('source', 60)->default('manual');
                $table->string('campaign', 160)->nullable();
                $table->unsignedTinyInteger('score')->default(0);
                $table->string('stage', 40)->default('new');
                $table->string('status', 30)->default('open');
                $table->string('priority', 20)->default('normal');
                $table->string('duplicate_status', 40)->default('none');
                $table->unsignedBigInteger('duplicate_customer_id')->nullable();
                $table->unsignedBigInteger('duplicate_lead_id')->nullable();
                $table->text('notes')->nullable();
                $table->text('reject_reason')->nullable();
                $table->timestamp('converted_at')->nullable();
                $table->unsignedBigInteger('converted_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status', 'stage'], 'crm_leads_tenant_status_stage_index');
                $table->index(['organization_id', 'status'], 'crm_leads_org_status_index');
                $table->index(['owner_user_id', 'status'], 'crm_leads_owner_status_index');
                $table->index(['mobile', 'tenant_id'], 'crm_leads_mobile_tenant_index');
            });
        }

        if (Schema::hasTable('crm_opportunities') && !Schema::hasColumn('crm_opportunities', 'source_lead_id')) {
            Schema::table('crm_opportunities', function (Blueprint $table) {
                $table->unsignedBigInteger('source_lead_id')->nullable()->after('source_followup_id');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep CRM lead and conversion history intact.
    }
};

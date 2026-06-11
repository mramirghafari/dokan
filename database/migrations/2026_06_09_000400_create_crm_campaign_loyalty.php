<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_campaigns')) {
            Schema::create('crm_campaigns', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('target_segment_id')->nullable()->index();
                $table->unsignedBigInteger('owner_user_id')->nullable()->index();
                $table->string('code', 80)->index();
                $table->string('title', 180);
                $table->string('channel', 40)->default('sms')->index();
                $table->string('goal', 40)->default('retention')->index();
                $table->string('status', 30)->default('draft')->index();
                $table->date('starts_at')->nullable()->index();
                $table->date('ends_at')->nullable()->index();
                $table->decimal('budget_amount', 18, 2)->default(0);
                $table->decimal('expected_revenue', 18, 2)->default(0);
                $table->decimal('actual_revenue', 18, 2)->default(0);
                $table->unsignedInteger('audience_count')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('response_count')->default(0);
                $table->unsignedInteger('conversion_count')->default(0);
                $table->string('discount_code', 80)->nullable()->index();
                $table->text('message_template')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'status'], 'crm_campaigns_scope_status_index');
                $table->index(['channel', 'goal'], 'crm_campaigns_channel_goal_index');
            });
        }

        if (!Schema::hasTable('crm_campaign_audiences')) {
            Schema::create('crm_campaign_audiences', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('crm_campaign_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('crm_lead_id')->nullable()->index();
                $table->string('status', 30)->default('planned')->index();
                $table->decimal('estimated_value', 18, 2)->default(0);
                $table->decimal('revenue_amount', 18, 2)->default(0);
                $table->unsignedInteger('loyalty_points_awarded')->default(0);
                $table->unsignedBigInteger('pishfactor_id')->nullable()->index();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamp('converted_at')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['crm_campaign_id', 'customer_id'], 'crm_campaign_audiences_campaign_customer_unique');
                $table->index(['tenant_id', 'organization_id', 'status'], 'crm_campaign_audiences_scope_status_index');
            });
        }

        if (!Schema::hasTable('customer_loyalty_accounts')) {
            Schema::create('customer_loyalty_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->unique();
                $table->string('tier', 30)->default('bronze')->index();
                $table->string('retention_status', 30)->default('new')->index();
                $table->integer('points_balance')->default(0);
                $table->integer('lifetime_points')->default(0);
                $table->decimal('lifetime_value', 18, 2)->default(0);
                $table->timestamp('last_purchase_at')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->text('benefits_note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'tier'], 'customer_loyalty_accounts_scope_tier_index');
                $table->index(['retention_status', 'last_activity_at'], 'customer_loyalty_accounts_retention_index');
            });
        }

        if (!Schema::hasTable('customer_loyalty_transactions')) {
            Schema::create('customer_loyalty_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('customer_loyalty_account_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('crm_campaign_id')->nullable()->index();
                $table->unsignedBigInteger('crm_campaign_audience_id')->nullable()->index();
                $table->unsignedBigInteger('pishfactor_id')->nullable()->index();
                $table->string('type', 30)->default('earn')->index();
                $table->integer('points')->default(0);
                $table->decimal('amount', 18, 2)->default(0);
                $table->string('reason', 180)->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'organization_id', 'type'], 'customer_loyalty_transactions_scope_type_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: campaign and loyalty history must remain available for audit and ROI reports.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_portal_accounts')) {
            Schema::create('customer_portal_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('role', 40)->default('customer');
                $table->string('access_token', 96)->unique();
                $table->string('status', 40)->default('active');
                $table->string('title', 180)->nullable();
                $table->string('contact_name', 180)->nullable();
                $table->string('contact_mobile', 80)->nullable();
                $table->string('contact_email', 180)->nullable();
                $table->json('permissions')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'status'], 'customer_portal_accounts_scope_status_idx');
                $table->index(['customer_id', 'role'], 'customer_portal_accounts_customer_role_idx');
            });
        }

        if (!Schema::hasTable('customer_portal_requests')) {
            Schema::create('customer_portal_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('customer_portal_account_id')->nullable();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('pishfactor_id')->nullable();
                $table->unsignedBigInteger('crm_service_ticket_id')->nullable();
                $table->string('type', 50)->default('support');
                $table->string('priority', 40)->default('normal');
                $table->string('status', 40)->default('new');
                $table->string('subject', 180);
                $table->text('description')->nullable();
                $table->text('response')->nullable();
                $table->decimal('requested_amount', 18, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'status'], 'customer_portal_requests_scope_status_idx');
                $table->index(['customer_id', 'type', 'status'], 'customer_portal_requests_customer_type_idx');
            });
        }

        if (!Schema::hasTable('customer_portal_announcements')) {
            Schema::create('customer_portal_announcements', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('audience_type', 40)->default('all');
                $table->string('priority', 40)->default('normal');
                $table->string('title', 180);
                $table->text('body')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'audience_type', 'is_active'], 'customer_portal_announcements_scope_idx');
            });
        }

        if (!Schema::hasTable('bi_alert_rules')) {
            Schema::create('bi_alert_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('domain', 60);
                $table->string('metric_key', 120)->nullable();
                $table->string('rule_type', 50)->default('threshold');
                $table->string('operator', 20)->nullable();
                $table->decimal('threshold_value', 18, 4)->nullable();
                $table->string('severity', 40)->default('medium');
                $table->unsignedInteger('lookback_days')->default(7);
                $table->unsignedInteger('comparison_days')->default(7);
                $table->string('title', 180);
                $table->text('suggestion')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'domain', 'is_active'], 'bi_alert_rules_scope_domain_idx');
                $table->index(['metric_key', 'rule_type'], 'bi_alert_rules_metric_type_idx');
            });
        }

        if (!Schema::hasTable('bi_insight_alerts')) {
            Schema::create('bi_insight_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('bi_alert_rule_id')->nullable();
                $table->date('summary_date');
                $table->string('domain', 60);
                $table->string('metric_key', 120);
                $table->string('alert_type', 50);
                $table->string('severity', 40)->default('medium');
                $table->string('status', 40)->default('open');
                $table->string('title', 180);
                $table->text('message')->nullable();
                $table->decimal('current_value', 18, 4)->default(0);
                $table->decimal('baseline_value', 18, 4)->nullable();
                $table->decimal('deviation_percent', 10, 2)->nullable();
                $table->text('suggestion')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('detected_at')->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->unsignedBigInteger('acknowledged_by')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->timestamps();

                $table->unique(['summary_date', 'tenant_id', 'organization_id', 'domain', 'metric_key', 'alert_type'], 'bi_insight_alerts_daily_unique');
                $table->index(['tenant_id', 'organization_id', 'status', 'severity'], 'bi_insight_alerts_scope_status_idx');
            });
        }

        if (!Schema::hasTable('bi_metric_forecasts')) {
            Schema::create('bi_metric_forecasts', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->date('forecast_date');
                $table->string('domain', 60);
                $table->string('metric_key', 120);
                $table->unsignedInteger('horizon_days')->default(7);
                $table->string('method', 60)->default('moving_average');
                $table->decimal('actual_value', 18, 4)->nullable();
                $table->decimal('forecast_value', 18, 4)->default(0);
                $table->decimal('lower_bound', 18, 4)->nullable();
                $table->decimal('upper_bound', 18, 4)->nullable();
                $table->decimal('confidence_score', 5, 2)->default(0);
                $table->string('trend_direction', 40)->default('flat');
                $table->json('metadata')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->unique(['forecast_date', 'tenant_id', 'organization_id', 'domain', 'metric_key'], 'bi_metric_forecasts_daily_unique');
                $table->index(['tenant_id', 'organization_id', 'domain'], 'bi_metric_forecasts_scope_domain_idx');
            });
        }

        $this->seedDefaultAlertRules();
    }

    public function down(): void
    {
        // Non-destructive migration: portal accounts, requests, alerts and forecasts are business records.
    }

    private function seedDefaultAlertRules(): void
    {
        if (!Schema::hasTable('bi_alert_rules')) {
            return;
        }

        $now = now();
        foreach ($this->defaultRules($now) as $rule) {
            DB::table('bi_alert_rules')->updateOrInsert([
                'tenant_id' => null,
                'organization_id' => null,
                'domain' => $rule['domain'],
                'metric_key' => $rule['metric_key'],
                'rule_type' => $rule['rule_type'],
            ], $rule);
        }
    }

    private function defaultRules($now): array
    {
        return [
            ['tenant_id' => null, 'organization_id' => null, 'domain' => 'sales', 'metric_key' => 'sales_gross_amount', 'rule_type' => 'drop', 'operator' => null, 'threshold_value' => 25, 'severity' => 'high', 'lookback_days' => 7, 'comparison_days' => 7, 'title' => 'افت فروش نسبت به میانگین', 'suggestion' => 'کانال فروش، کمپین های فعال، موجودی کالاهای پرفروش و وضعیت تیم فروش بررسی شود.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['tenant_id' => null, 'organization_id' => null, 'domain' => 'crm', 'metric_key' => 'crm_open_service_tickets', 'rule_type' => 'spike', 'operator' => null, 'threshold_value' => 30, 'severity' => 'medium', 'lookback_days' => 7, 'comparison_days' => 7, 'title' => 'افزایش تیکت های باز CRM', 'suggestion' => 'SLA، ارجاع کارشناس و علت های پرتکرار تیکت ها بررسی شود.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['tenant_id' => null, 'organization_id' => null, 'domain' => 'inventory', 'metric_key' => 'inventory_low_stock_items', 'rule_type' => 'threshold', 'operator' => 'gte', 'threshold_value' => 1, 'severity' => 'medium', 'lookback_days' => 7, 'comparison_days' => 7, 'title' => 'کمبود موجودی قابل اقدام', 'suggestion' => 'برای اقلام زیر نقطه سفارش، خرید یا انتقال داخلی بررسی شود.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
    }
};

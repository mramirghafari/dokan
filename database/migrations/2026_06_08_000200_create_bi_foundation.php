<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bi_metric_definitions')) {
            Schema::create('bi_metric_definitions', function (Blueprint $table) {
                $table->id();
                $table->string('metric_key', 100)->unique();
                $table->string('title', 180);
                $table->string('domain', 60);
                $table->string('unit', 40)->default('number');
                $table->string('refresh_frequency', 40)->default('daily');
                $table->string('owner_role', 80)->nullable();
                $table->string('permission_key', 120)->nullable();
                $table->text('formula')->nullable();
                $table->text('source')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['domain', 'is_active'], 'bi_metric_definitions_domain_active_index');
            });
        }

        if (!Schema::hasTable('bi_daily_summaries')) {
            Schema::create('bi_daily_summaries', function (Blueprint $table) {
                $table->id();
                $table->date('summary_date');
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('domain', 60);
                $table->string('metric_key', 100);
                $table->string('dimension_type', 80)->nullable();
                $table->unsignedBigInteger('dimension_id')->nullable();
                $table->decimal('value', 24, 4)->default(0);
                $table->decimal('comparison_value', 24, 4)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('refreshed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'organization_id', 'summary_date'], 'bi_daily_summaries_scope_date_index');
                $table->index(['domain', 'metric_key'], 'bi_daily_summaries_metric_index');
                $table->unique(['summary_date', 'tenant_id', 'organization_id', 'domain', 'metric_key', 'dimension_type', 'dimension_id'], 'bi_daily_summaries_unique_scope');
            });
        }

        if (!Schema::hasTable('bi_refresh_logs')) {
            Schema::create('bi_refresh_logs', function (Blueprint $table) {
                $table->id();
                $table->string('dataset_key', 100);
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('status', 30)->default('success');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->unsignedInteger('rows_count')->default(0);
                $table->text('message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['dataset_key', 'status', 'finished_at'], 'bi_refresh_logs_dataset_status_index');
                $table->index(['tenant_id', 'organization_id', 'finished_at'], 'bi_refresh_logs_scope_index');
            });
        }

        $now = now();
        foreach ($this->defaultMetrics($now) as $metric) {
            DB::table('bi_metric_definitions')->updateOrInsert(['metric_key' => $metric['metric_key']], $metric);
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep BI summaries and refresh history intact.
    }

    private function defaultMetrics($now): array
    {
        return [
            ['metric_key' => 'crm_open_cards', 'title' => 'کارت های باز CRM', 'domain' => 'crm', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'sales_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'COUNT(crm_sales_board_cards WHERE status IN open,in_progress)', 'source' => 'crm_sales_board_cards', 'description' => 'تعداد کارت های عملیاتی باز در کاریز فروش.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'crm_overdue_followups', 'title' => 'پیگیری های معوق CRM', 'domain' => 'crm', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'sales_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'COUNT(crm_followups WHERE due_date < today AND status open)', 'source' => 'crm_followups', 'description' => 'پیگیری های باز که از موعد عبور کرده اند.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'crm_weighted_pipeline', 'title' => 'ارزش وزنی قیف CRM', 'domain' => 'crm', 'unit' => 'rial', 'refresh_frequency' => 'daily', 'owner_role' => 'sales_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'SUM(amount * probability_percent / 100)', 'source' => 'crm_sales_board_cards, crm_opportunities', 'description' => 'ارزش احتمالی فروش باز برای forecast.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'crm_win_rate', 'title' => 'نرخ برد فرصت', 'domain' => 'crm', 'unit' => 'percent', 'refresh_frequency' => 'daily', 'owner_role' => 'sales_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'won / (won + lost) in current month', 'source' => 'crm_opportunities', 'description' => 'نسبت فرصت های برده شده به فرصت های بسته شده.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'finance_cash_forecast', 'title' => 'پیش بینی نقدینگی', 'domain' => 'finance', 'unit' => 'rial', 'refresh_frequency' => 'daily', 'owner_role' => 'cfo', 'permission_key' => 'bi.dataset.view', 'formula' => 'cash forecast service net events', 'source' => 'treasury, purchase, sales receivable', 'description' => 'شاخص راهبر برای داشبورد مالی و خزانه.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'inventory_low_stock', 'title' => 'کالاهای زیر نقطه سفارش', 'domain' => 'inventory', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'warehouse_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'available_quantity <= reorder_point', 'source' => 'inventory_balances, products', 'description' => 'شاخص هشدار کمبود برای داشبورد عملیات.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
    }
};

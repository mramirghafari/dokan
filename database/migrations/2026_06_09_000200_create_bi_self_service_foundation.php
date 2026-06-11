<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bi_dataset_definitions')) {
            Schema::create('bi_dataset_definitions', function (Blueprint $table) {
                $table->id();
                $table->string('dataset_key', 100)->unique();
                $table->string('title', 180);
                $table->string('domain', 60);
                $table->string('source_type', 60)->default('bi_summary');
                $table->text('description')->nullable();
                $table->json('dimensions')->nullable();
                $table->json('measures')->nullable();
                $table->json('filters')->nullable();
                $table->json('fixed_filters')->nullable();
                $table->json('default_dimensions')->nullable();
                $table->json('default_measures')->nullable();
                $table->json('default_sort')->nullable();
                $table->string('permission_key', 120)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['domain', 'is_active'], 'bi_dataset_definitions_domain_active_index');
            });
        }

        if (!Schema::hasTable('bi_report_templates')) {
            Schema::create('bi_report_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('title', 180);
                $table->string('dataset_key', 100);
                $table->json('dimensions')->nullable();
                $table->json('measures')->nullable();
                $table->json('filters')->nullable();
                $table->string('chart_type', 40)->default('table');
                $table->string('visibility', 40)->default('private');
                $table->unsignedBigInteger('shared_role_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'dataset_key'], 'bi_report_templates_scope_dataset_index');
                $table->index(['created_by', 'is_active'], 'bi_report_templates_creator_active_index');
            });
        }

        if (!Schema::hasTable('bi_report_runs')) {
            Schema::create('bi_report_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('template_id')->nullable();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('dataset_key', 100);
                $table->unsignedBigInteger('requested_by')->nullable();
                $table->string('status', 30)->default('success');
                $table->unsignedInteger('row_count')->default(0);
                $table->json('filters')->nullable();
                $table->json('output_summary')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();

                $table->index(['dataset_key', 'status', 'finished_at'], 'bi_report_runs_dataset_status_index');
                $table->index(['tenant_id', 'organization_id', 'finished_at'], 'bi_report_runs_scope_index');
            });
        }

        $now = now();
        foreach ($this->defaultDatasets($now) as $dataset) {
            DB::table('bi_dataset_definitions')->updateOrInsert(['dataset_key' => $dataset['dataset_key']], $dataset);
        }

        foreach ($this->additionalMetrics($now) as $metric) {
            DB::table('bi_metric_definitions')->updateOrInsert(['metric_key' => $metric['metric_key']], $metric);
        }
    }

    public function down(): void
    {
        // Non-destructive migration: report templates, audit runs and BI dataset contracts are kept.
    }

    private function defaultDatasets($now): array
    {
        $dimensions = [
            ['key' => 'summary_date', 'label' => 'تاریخ', 'column' => 'summary_date', 'type' => 'date'],
            ['key' => 'domain', 'label' => 'دامنه', 'column' => 'domain', 'type' => 'string'],
            ['key' => 'metric_key', 'label' => 'شاخص', 'column' => 'metric_key', 'type' => 'string'],
            ['key' => 'dimension_type', 'label' => 'نوع بعد', 'column' => 'dimension_type', 'type' => 'string'],
            ['key' => 'dimension_id', 'label' => 'شناسه بعد', 'column' => 'dimension_id', 'type' => 'number'],
            ['key' => 'organization_id', 'label' => 'سازمان', 'column' => 'organization_id', 'type' => 'number'],
        ];

        $measures = [
            ['key' => 'value_sum', 'label' => 'جمع مقدار', 'column' => 'value', 'aggregate' => 'sum', 'unit' => 'number'],
            ['key' => 'value_avg', 'label' => 'میانگین مقدار', 'column' => 'value', 'aggregate' => 'avg', 'unit' => 'number'],
            ['key' => 'value_max', 'label' => 'بیشترین مقدار', 'column' => 'value', 'aggregate' => 'max', 'unit' => 'number'],
            ['key' => 'rows_count', 'label' => 'تعداد ردیف', 'column' => 'id', 'aggregate' => 'count', 'unit' => 'count'],
        ];

        $filters = [
            ['key' => 'date_from', 'label' => 'از تاریخ', 'column' => 'summary_date', 'operator' => '>=', 'type' => 'date'],
            ['key' => 'date_to', 'label' => 'تا تاریخ', 'column' => 'summary_date', 'operator' => '<=', 'type' => 'date'],
            ['key' => 'domain', 'label' => 'دامنه', 'column' => 'domain', 'operator' => '=', 'type' => 'string'],
            ['key' => 'metric_key', 'label' => 'شاخص', 'column' => 'metric_key', 'operator' => 'in', 'type' => 'string'],
            ['key' => 'dimension_type', 'label' => 'نوع بعد', 'column' => 'dimension_type', 'operator' => '=', 'type' => 'string'],
        ];

        $datasets = [
            ['dataset_key' => 'bi_daily_metrics', 'title' => 'همه شاخص های روزانه BI', 'domain' => 'enterprise', 'description' => 'Fact عمومی data mart روی جدول bi_daily_summaries برای تحلیل همه دامنه ها.'],
            ['dataset_key' => 'crm_daily_metrics', 'title' => 'شاخص های روزانه CRM', 'domain' => 'crm', 'description' => 'شاخص های کاریز، پیگیری، تیکت و مرکز تماس CRM.', 'fixed_filters' => ['domain' => 'crm']],
            ['dataset_key' => 'sales_daily_metrics', 'title' => 'شاخص های روزانه فروش', 'domain' => 'sales', 'description' => 'تعداد و مبلغ سفارش/فاکتور فروش از fact روزانه.', 'fixed_filters' => ['domain' => 'sales']],
            ['dataset_key' => 'finance_daily_metrics', 'title' => 'شاخص های روزانه مالی', 'domain' => 'finance', 'description' => 'سندهای حسابداری و جمع بدهکار/اعتبار برای نمای مالی.', 'fixed_filters' => ['domain' => 'finance']],
            ['dataset_key' => 'inventory_daily_metrics', 'title' => 'شاخص های روزانه انبار', 'domain' => 'inventory', 'description' => 'ارزش موجودی، کسری و snapshot اقلام انبار.', 'fixed_filters' => ['domain' => 'inventory']],
        ];

        return array_map(function (array $dataset) use ($dimensions, $measures, $filters, $now) {
            return [
                'dataset_key' => $dataset['dataset_key'],
                'title' => $dataset['title'],
                'domain' => $dataset['domain'],
                'source_type' => 'bi_summary',
                'description' => $dataset['description'],
                'dimensions' => json_encode($dimensions, JSON_UNESCAPED_UNICODE),
                'measures' => json_encode($measures, JSON_UNESCAPED_UNICODE),
                'filters' => json_encode($filters, JSON_UNESCAPED_UNICODE),
                'fixed_filters' => json_encode($dataset['fixed_filters'] ?? [], JSON_UNESCAPED_UNICODE),
                'default_dimensions' => json_encode(['summary_date', 'metric_key'], JSON_UNESCAPED_UNICODE),
                'default_measures' => json_encode(['value_sum'], JSON_UNESCAPED_UNICODE),
                'default_sort' => json_encode(['column' => 'summary_date', 'direction' => 'desc'], JSON_UNESCAPED_UNICODE),
                'permission_key' => 'bi.dataset.view',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $datasets);
    }

    private function additionalMetrics($now): array
    {
        return [
            ['metric_key' => 'sales_order_count', 'title' => 'تعداد سفارش فروش', 'domain' => 'sales', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'sales_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'COUNT(pishfactors by day)', 'source' => 'pishfactors', 'description' => 'تعداد سفارش ها و پیش فاکتورهای فروش روزانه.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'sales_gross_amount', 'title' => 'مبلغ ناخالص فروش', 'domain' => 'sales', 'unit' => 'rial', 'refresh_frequency' => 'daily', 'owner_role' => 'sales_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'SUM(pishfactors.fullPrice)', 'source' => 'pishfactors', 'description' => 'جمع مبلغ ناخالص سفارش های فروش روزانه.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'finance_voucher_count', 'title' => 'تعداد اسناد حسابداری', 'domain' => 'finance', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'cfo', 'permission_key' => 'bi.dataset.view', 'formula' => 'COUNT(vouchers by day)', 'source' => 'vouchers', 'description' => 'تعداد اسناد مالی ثبت شده در روز.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'finance_debit_total', 'title' => 'جمع گردش بدهکار', 'domain' => 'finance', 'unit' => 'rial', 'refresh_frequency' => 'daily', 'owner_role' => 'cfo', 'permission_key' => 'bi.dataset.view', 'formula' => 'SUM(vouchers.total_debit or amount)', 'source' => 'vouchers', 'description' => 'جمع گردش بدهکار سندهای مالی روزانه.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'inventory_stock_value', 'title' => 'ارزش موجودی انبار', 'domain' => 'inventory', 'unit' => 'rial', 'refresh_frequency' => 'daily', 'owner_role' => 'warehouse_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'SUM(inventory_balances.total_cost or quantity * unit_cost)', 'source' => 'inventory_balances', 'description' => 'ارزش snapshot موجودی در data mart.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'inventory_low_stock_items', 'title' => 'اقلام زیر حداقل موجودی', 'domain' => 'inventory', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'warehouse_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'COUNT(quantity - reserved_quantity <= minimum_quantity)', 'source' => 'inventory_balances', 'description' => 'تعداد اقلامی که زیر نقطه سفارش هستند.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'crm_open_service_tickets', 'title' => 'تیکت های باز خدمات', 'domain' => 'crm', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'crm_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'COUNT(crm_service_tickets open statuses)', 'source' => 'crm_service_tickets', 'description' => 'تعداد تیکت های باز و در حال رسیدگی CRM.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['metric_key' => 'crm_calls_today', 'title' => 'تماس های ثبت شده امروز', 'domain' => 'crm', 'unit' => 'count', 'refresh_frequency' => 'daily', 'owner_role' => 'crm_manager', 'permission_key' => 'bi.dataset.view', 'formula' => 'COUNT(crm_call_logs by day)', 'source' => 'crm_call_logs', 'description' => 'تعداد تماس های روز مرکز تماس CRM.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
    }
};

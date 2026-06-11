<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ($this->datasets($now) as $dataset) {
            DB::table('bi_dataset_definitions')->updateOrInsert(['dataset_key' => $dataset['dataset_key']], $dataset);
        }

        foreach ($this->metrics($now) as $metric) {
            DB::table('bi_metric_definitions')->updateOrInsert(['metric_key' => $metric['metric_key']], $metric);
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep BI catalog extensions for audit continuity.
    }

    private function datasets($now): array
    {
        $domains = [
            'purchase' => 'شاخص های روزانه خرید و تامین',
            'production' => 'شاخص های روزانه تولید',
            'treasury' => 'شاخص های خزانه و چک',
            'payroll' => 'شاخص های حقوق و دستمزد',
            'assets' => 'شاخص های دارایی ثابت',
            'distribution' => 'شاخص های پخش و موبایل',
            'ecommerce' => 'شاخص های فروشگاه اینترنتی',
            'contracting' => 'شاخص های پیمانکاری و پروژه',
        ];

        return collect($domains)->map(function ($title, $domain) use ($now) {
            return [
                'dataset_key' => $domain . '_daily_metrics',
                'title' => $title,
                'domain' => $domain,
                'source_type' => 'bi_summary',
                'description' => 'Dataset امن self-service برای دامنه ' . $domain . ' از factهای bi_daily_summaries.',
                'dimensions' => json_encode($this->dimensions(), JSON_UNESCAPED_UNICODE),
                'measures' => json_encode($this->measures(), JSON_UNESCAPED_UNICODE),
                'filters' => json_encode($this->filters(), JSON_UNESCAPED_UNICODE),
                'fixed_filters' => json_encode(['domain' => $domain], JSON_UNESCAPED_UNICODE),
                'default_dimensions' => json_encode(['summary_date', 'metric_key'], JSON_UNESCAPED_UNICODE),
                'default_measures' => json_encode(['value_sum'], JSON_UNESCAPED_UNICODE),
                'default_sort' => json_encode(['column' => 'summary_date', 'direction' => 'desc'], JSON_UNESCAPED_UNICODE),
                'permission_key' => 'bi.dataset.view',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->all();
    }

    private function metrics($now): array
    {
        $metrics = [
            ['purchase_order_count', 'تعداد سفارش خرید', 'purchase', 'count', 'purchase_orders', 'COUNT(purchase_orders by day)'],
            ['purchase_order_amount', 'مبلغ سفارش خرید', 'purchase', 'rial', 'purchase_orders.total_amount', 'SUM(purchase_orders.total_amount)'],
            ['production_order_count', 'تعداد دستور تولید', 'production', 'count', 'production_orders', 'COUNT(production_orders by day)'],
            ['production_material_cost', 'هزینه مواد تولید', 'production', 'rial', 'production_orders.material_cost', 'SUM(production_orders.material_cost)'],
            ['treasury_instrument_count', 'تعداد اسناد خزانه باز', 'treasury', 'count', 'treasury_instruments', 'COUNT(open treasury_instruments)'],
            ['treasury_instrument_amount', 'مبلغ اسناد خزانه باز', 'treasury', 'rial', 'treasury_instruments.amount', 'SUM(open treasury_instruments.amount)'],
            ['payroll_run_count', 'تعداد لیست حقوق', 'payroll', 'count', 'payroll_runs', 'COUNT(payroll_runs by day)'],
            ['payroll_net_pay_amount', 'خالص پرداختنی حقوق', 'payroll', 'rial', 'payroll_runs.net_pay_amount', 'SUM(payroll_runs.net_pay_amount)'],
            ['asset_active_count', 'تعداد دارایی فعال', 'assets', 'count', 'company_assets', 'COUNT(active company_assets)'],
            ['asset_acquisition_cost', 'بهای تحصیل دارایی فعال', 'assets', 'rial', 'company_assets.acquisition_cost', 'SUM(company_assets.acquisition_cost)'],
            ['distribution_mobile_order_count', 'تعداد سفارش موبایل پخش', 'distribution', 'count', 'distribution_mobile_orders', 'COUNT(distribution_mobile_orders by day)'],
            ['distribution_net_amount', 'خالص سفارش موبایل پخش', 'distribution', 'rial', 'distribution_mobile_orders.net_amount', 'SUM(distribution_mobile_orders.net_amount)'],
            ['ecommerce_order_count', 'تعداد سفارش فروشگاه اینترنتی', 'ecommerce', 'count', 'ecommerce_order_mappings', 'COUNT(ecommerce_order_mappings by day)'],
            ['ecommerce_net_amount', 'خالص سفارش فروشگاه اینترنتی', 'ecommerce', 'rial', 'ecommerce_order_mappings.net_amount', 'SUM(ecommerce_order_mappings.net_amount)'],
            ['contracting_project_count', 'تعداد پروژه فعال پیمانکاری', 'contracting', 'count', 'contracting_projects', 'COUNT(active contracting_projects)'],
            ['contracting_contract_amount', 'مبلغ قراردادهای فعال', 'contracting', 'rial', 'contracting_projects.contract_amount', 'SUM(contracting_projects.contract_amount)'],
        ];

        return collect($metrics)->map(function ($metric) use ($now) {
            return [
                'metric_key' => $metric[0],
                'title' => $metric[1],
                'domain' => $metric[2],
                'unit' => $metric[3],
                'refresh_frequency' => 'daily',
                'owner_role' => 'manager',
                'permission_key' => 'bi.dataset.view',
                'formula' => $metric[5],
                'source' => $metric[4],
                'description' => 'شاخص data mart برای گزارش ساز self-service دامنه ' . $metric[2] . '.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();
    }

    private function dimensions(): array
    {
        return [
            ['key' => 'summary_date', 'label' => 'تاریخ', 'column' => 'summary_date', 'type' => 'date'],
            ['key' => 'domain', 'label' => 'دامنه', 'column' => 'domain', 'type' => 'string'],
            ['key' => 'metric_key', 'label' => 'شاخص', 'column' => 'metric_key', 'type' => 'string'],
            ['key' => 'dimension_type', 'label' => 'نوع بعد', 'column' => 'dimension_type', 'type' => 'string'],
            ['key' => 'dimension_id', 'label' => 'شناسه بعد', 'column' => 'dimension_id', 'type' => 'number'],
            ['key' => 'organization_id', 'label' => 'سازمان', 'column' => 'organization_id', 'type' => 'number'],
        ];
    }

    private function measures(): array
    {
        return [
            ['key' => 'value_sum', 'label' => 'جمع مقدار', 'column' => 'value', 'aggregate' => 'sum', 'unit' => 'number'],
            ['key' => 'value_avg', 'label' => 'میانگین مقدار', 'column' => 'value', 'aggregate' => 'avg', 'unit' => 'number'],
            ['key' => 'value_max', 'label' => 'بیشترین مقدار', 'column' => 'value', 'aggregate' => 'max', 'unit' => 'number'],
            ['key' => 'rows_count', 'label' => 'تعداد ردیف', 'column' => 'id', 'aggregate' => 'count', 'unit' => 'count'],
        ];
    }

    private function filters(): array
    {
        return [
            ['key' => 'date_from', 'label' => 'از تاریخ', 'column' => 'summary_date', 'operator' => '>=', 'type' => 'date'],
            ['key' => 'date_to', 'label' => 'تا تاریخ', 'column' => 'summary_date', 'operator' => '<=', 'type' => 'date'],
            ['key' => 'domain', 'label' => 'دامنه', 'column' => 'domain', 'operator' => '=', 'type' => 'string'],
            ['key' => 'metric_key', 'label' => 'شاخص', 'column' => 'metric_key', 'operator' => 'in', 'type' => 'string'],
            ['key' => 'dimension_type', 'label' => 'نوع بعد', 'column' => 'dimension_type', 'operator' => '=', 'type' => 'string'],
        ];
    }
};

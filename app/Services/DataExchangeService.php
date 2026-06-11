<?php

namespace App\Services;

use App\Jobs\ExportReportJob;
use App\Jobs\ImportCrmLeadsJob;
use App\Jobs\ImportCustomersJob;
use App\Models\DataExchangeRun;
use App\Models\User;

class DataExchangeService
{
    public function manifest(): array
    {
        return [
            'products' => ['title' => 'کالاها', 'key' => 'products', 'columns' => ['code', 'title', 'category_id', 'unit', 'barcode', 'isActive']],
            'customers' => ['title' => 'مشتریان', 'key' => 'customers', 'columns' => ['name', 'mobile', 'code', 'city_id', 'region_id', 'area', 'isActive']],
            'opening_stock' => ['title' => 'موجودی اول دوره', 'key' => 'opening_stock', 'columns' => ['store_id', 'product_id', 'quantity', 'unit_cost', 'batch_number', 'serial_number']],
            'company_assets' => ['title' => 'دارایی ثابت', 'key' => 'company_assets', 'columns' => ['asset_code', 'title', 'asset_class_id', 'store_id', 'acquisition_cost', 'acquisition_date_en']],
            'payroll' => ['title' => 'حقوق و دستمزد', 'key' => 'payroll', 'columns' => ['employee_id', 'period', 'base_salary', 'work_days', 'overtime_hours', 'tax_amount', 'insurance_amount']],
            'bank_statement' => ['title' => 'صورتحساب بانک', 'key' => 'bank_statement', 'columns' => ['transaction_date', 'description', 'debit', 'credit', 'tracking_number']],
            'taxpayer_items' => ['title' => 'شناسه مالیاتی کالا/خدمت', 'key' => 'taxpayer_items', 'columns' => ['product_id', 'tax_stuff_id', 'tax_unit_id', 'vat_rate']],
            'crm_leads' => ['title' => 'سرنخ CRM', 'key' => 'crm_leads', 'columns' => ['name', 'mobile', 'phone', 'email', 'company_name', 'city', 'source', 'campaign', 'score', 'priority', 'notes']],
        ];
    }

    public function start(string $direction, string $entityType, array $context = []): DataExchangeRun
    {
        return DataExchangeRun::create([
            'tenant_id' => $context['tenant_id'] ?? null,
            'organization_id' => $context['organization_id'] ?? null,
            'store_id' => $context['store_id'] ?? null,
            'user_id' => $context['user_id'] ?? auth()->id(),
            'direction' => $direction,
            'entity_type' => $entityType,
            'file_name' => $context['file_name'] ?? null,
            'status' => 'processing',
            'options_json' => $context['options'] ?? [],
            'started_at' => now(),
        ]);
    }

    public function finish(DataExchangeRun $run, int $totalRows, int $successRows, int $failedRows = 0, array $summary = []): DataExchangeRun
    {
        $run->update([
            'status' => $failedRows > 0 ? 'completed_with_errors' : 'completed',
            'total_rows' => $totalRows,
            'success_rows' => $successRows,
            'failed_rows' => $failedRows,
            'summary_json' => $summary,
            'finished_at' => now(),
        ]);

        return $run->fresh() ?: $run;
    }

    public function fail(DataExchangeRun $run, string $message, array $summary = []): DataExchangeRun
    {
        $run->update([
            'status' => 'failed',
            'error_message' => $message,
            'summary_json' => $summary,
            'finished_at' => now(),
        ]);

        return $run->fresh() ?: $run;
    }

    public function dispatchCustomerImport(User $user, string $storagePath, array $options = []): DataExchangeRun
    {
        $context = app(TenantContextService::class)->fromUser($user);

        $run = $this->start('import', 'customers', [
            'tenant_id' => $context['tenant_id'],
            'organization_id' => $context['organization_id'],
            'user_id' => $user->id,
            'file_name' => basename($storagePath),
            'options' => $options,
        ]);

        ImportCustomersJob::dispatch($run->id, $user->id, $storagePath, $options);

        return $run;
    }

    public function dispatchLeadImport(User $user, string $storagePath, array $options = []): DataExchangeRun
    {
        $context = app(TenantContextService::class)->fromUser($user);

        $run = $this->start('import', 'crm_leads', [
            'tenant_id' => $context['tenant_id'],
            'organization_id' => $context['organization_id'],
            'user_id' => $user->id,
            'file_name' => basename($storagePath),
            'options' => $options,
        ]);

        ImportCrmLeadsJob::dispatch($run->id, $user->id, $storagePath, $options);

        return $run;
    }

    public function dispatchReportExport(User $user, array $input, string $format = 'csv'): DataExchangeRun
    {
        $context = app(TenantContextService::class)->fromUser($user);

        $run = $this->start('export', 'bi_report', [
            'tenant_id' => $context['tenant_id'],
            'organization_id' => $context['organization_id'],
            'user_id' => $user->id,
            'options' => [
                'dataset_key' => $input['dataset_key'] ?? null,
                'format' => $format,
            ],
        ]);

        ExportReportJob::dispatch($run->id, $user->id, $input, $format);

        return $run;
    }

    public function shouldQueueReportExport(int $estimatedRows): bool
    {
        return $estimatedRows >= (int) config('erp_scale.queue.export_row_threshold', 2000);
    }
}

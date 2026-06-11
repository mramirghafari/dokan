<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('erp_scale_audit_snapshots')) {
            Schema::create('erp_scale_audit_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('scope_label', 120)->default('global');
                $table->unsignedTinyInteger('readiness_score')->default(100);
                $table->string('risk_level', 30)->default('low');
                $table->json('summary')->nullable();
                $table->json('checks')->nullable();
                $table->json('table_profiles')->nullable();
                $table->json('recommendations')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->unsignedBigInteger('generated_by')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'organization_id', 'generated_at'], 'erp_scale_audit_scope_generated_idx');
                $table->index(['risk_level', 'readiness_score'], 'erp_scale_audit_risk_score_idx');
            });
        }

        foreach ($this->indexDefinitions() as $definition) {
            $this->addIndexIfPossible($definition['table'], $definition['columns'], $definition['name']);
        }
    }

    public function down(): void
    {
        // Non-destructive: scale audit snapshots and performance indexes are operational evidence.
    }

    private function indexDefinitions(): array
    {
        return [
            ['table' => 'customers', 'columns' => ['tenant_id', 'organization_id', 'status', 'updated_at'], 'name' => 'customers_scope_status_updated_idx'],
            ['table' => 'customers', 'columns' => ['tenant_id', 'organization_id', 'mobile'], 'name' => 'customers_scope_mobile_idx'],
            ['table' => 'products', 'columns' => ['tenant_id', 'organization_id', 'store_id', 'isActive'], 'name' => 'products_scope_store_active_idx'],
            ['table' => 'products', 'columns' => ['tenant_id', 'organization_id', 'sku'], 'name' => 'products_scope_sku_idx'],
            ['table' => 'pishfactors', 'columns' => ['tenant_id', 'organization_id', 'customer_id', 'status'], 'name' => 'pishfactors_scope_customer_status_idx'],
            ['table' => 'pishfactors', 'columns' => ['tenant_id', 'organization_id', 'created_at'], 'name' => 'pishfactors_scope_created_idx'],
            ['table' => 'pish_factor_items', 'columns' => ['tenant_id', 'organization_id', 'product_id'], 'name' => 'pf_items_scope_product_idx'],
            ['table' => 'vouchers', 'columns' => ['tenant_id', 'organization_id', 'voucher_date_en'], 'name' => 'vouchers_scope_date_idx'],
            ['table' => 'voucher_items', 'columns' => ['tenant_id', 'organization_id', 'account_id'], 'name' => 'voucher_items_scope_account_idx'],
            ['table' => 'receipts', 'columns' => ['tenant_id', 'organization_id', 'store_id', 'status'], 'name' => 'receipts_scope_store_status_idx'],
            ['table' => 'depots', 'columns' => ['tenant_id', 'organization_id', 'product_id'], 'name' => 'depots_scope_product_idx'],
            ['table' => 'inventory_movements', 'columns' => ['tenant_id', 'organization_id', 'store_id', 'product_id'], 'name' => 'inv_movements_scope_store_product_idx'],
            ['table' => 'inventory_movements', 'columns' => ['tenant_id', 'organization_id', 'movement_date'], 'name' => 'inv_movements_scope_date_idx'],
            ['table' => 'inventory_balances', 'columns' => ['tenant_id', 'organization_id', 'store_id', 'product_id'], 'name' => 'inv_balances_scope_store_product_idx'],
            ['table' => 'users', 'columns' => ['tenant_id', 'organization_id', 'isActive'], 'name' => 'users_scope_active_idx'],
            ['table' => 'notifs', 'columns' => ['tenant_id', 'organization_id', 'user_id', 'status'], 'name' => 'notifs_scope_user_status_idx'],
            ['table' => 'bi_daily_summaries', 'columns' => ['tenant_id', 'organization_id', 'summary_date', 'domain'], 'name' => 'bi_summaries_scope_date_domain_idx'],
            ['table' => 'crm_followups', 'columns' => ['tenant_id', 'organization_id', 'status', 'due_date_en'], 'name' => 'crm_followups_scope_status_due_idx'],
            ['table' => 'crm_opportunities', 'columns' => ['tenant_id', 'organization_id', 'status', 'next_action_date_en'], 'name' => 'crm_opps_scope_status_next_idx'],
            ['table' => 'crm_service_tickets', 'columns' => ['tenant_id', 'organization_id', 'status', 'due_at'], 'name' => 'crm_tickets_scope_status_due_idx'],
            ['table' => 'crm_integration_sync_logs', 'columns' => ['tenant_id', 'organization_id', 'status', 'created_at'], 'name' => 'crm_sync_logs_scope_status_created_idx'],
        ];
    }

    private function addIndexIfPossible(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        $availableColumns = array_values(array_filter($columns, fn($column) => Schema::hasColumn($table, $column)));
        if (count($availableColumns) < 2) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($availableColumns, $name) {
            $tableBlueprint->index($availableColumns, $name);
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }
};

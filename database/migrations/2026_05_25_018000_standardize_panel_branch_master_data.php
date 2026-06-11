<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendTenants();
        $this->extendOrganizations();
        $this->defaultValues();
    }

    public function down()
    {
        // Non-destructive migration: keep panel/branch master-data intact.
    }

    private function extendTenants(): void
    {
        if (!Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'panel_status')) {
                $table->string('panel_status', 30)->nullable()->default('active')->after('status');
            }
            if (!Schema::hasColumn('tenants', 'panel_type')) {
                $table->string('panel_type', 30)->nullable()->default('main')->after('panel_status');
            }
            if (!Schema::hasColumn('tenants', 'default_currency_id')) {
                $table->unsignedBigInteger('default_currency_id')->nullable()->after('currency_type');
            }
            if (!Schema::hasColumn('tenants', 'default_fiscal_year_id')) {
                $table->unsignedBigInteger('default_fiscal_year_id')->nullable()->after('default_currency_id');
            }
        });

        $this->addIndexIfMissing('tenants', 'panel_status', 'tenants_panel_status_index');
        $this->addIndexIfMissing('tenants', 'panel_type', 'tenants_panel_type_index');
    }

    private function extendOrganizations(): void
    {
        if (!Schema::hasTable('organizations')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'branch_code')) {
                $table->string('branch_code', 60)->nullable()->after('id');
            }
            if (!Schema::hasColumn('organizations', 'branch_type')) {
                $table->string('branch_type', 30)->nullable()->default('sales_branch')->after('type');
            }
            if (!Schema::hasColumn('organizations', 'branch_status')) {
                $table->string('branch_status', 30)->nullable()->default('active')->after('branch_type');
            }
            if (!Schema::hasColumn('organizations', 'is_headquarters')) {
                $table->boolean('is_headquarters')->default(false)->after('branch_status');
            }
            if (!Schema::hasColumn('organizations', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('title');
            }
            if (!Schema::hasColumn('organizations', 'economic_number')) {
                $table->string('economic_number', 50)->nullable()->after('legal_name');
            }
            if (!Schema::hasColumn('organizations', 'national_id')) {
                $table->string('national_id', 50)->nullable()->after('economic_number');
            }
            if (!Schema::hasColumn('organizations', 'phone')) {
                $table->string('phone', 50)->nullable()->after('national_id');
            }
            if (!Schema::hasColumn('organizations', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });

        $this->addIndexIfMissing('organizations', 'branch_code', 'organizations_branch_code_index');
        $this->addIndexIfMissing('organizations', 'branch_type', 'organizations_branch_type_index');
        $this->addIndexIfMissing('organizations', 'branch_status', 'organizations_branch_status_index');
    }

    private function defaultValues(): void
    {
        if (Schema::hasTable('tenants')) {
            DB::table('tenants')->whereNull('panel_status')->update(['panel_status' => 'active']);
            DB::table('tenants')->whereNull('panel_type')->update(['panel_type' => 'main']);

            if (Schema::hasTable('currencies') && Schema::hasColumn('tenants', 'default_currency_id')) {
                DB::table('tenants')->orderBy('id')->chunkById(200, function ($tenants) {
                    foreach ($tenants as $tenant) {
                        $currencyId = DB::table('currencies')->where('tenant_id', $tenant->id)->where('is_default', 1)->value('id');
                        $fiscalYearId = Schema::hasTable('fiscal_years') ? DB::table('fiscal_years')->where('tenant_id', $tenant->id)->where('is_default', 1)->value('id') : null;
                        DB::table('tenants')->where('id', $tenant->id)->update(['default_currency_id' => $currencyId, 'default_fiscal_year_id' => $fiscalYearId]);
                    }
                });
            }
        }

        if (Schema::hasTable('organizations')) {
            DB::table('organizations')->orderBy('id')->chunkById(200, function ($organizations) {
                foreach ($organizations as $organization) {
                    DB::table('organizations')->where('id', $organization->id)->update([
                        'branch_code' => $organization->branch_code ?: 'BR-' . str_pad((string) $organization->id, 4, '0', STR_PAD_LEFT),
                        'branch_type' => $organization->branch_type ?: 'sales_branch',
                        'branch_status' => $organization->isActive ? 'active' : 'inactive',
                        'is_headquarters' => $organization->id === 1,
                        'legal_name' => $organization->legal_name ?: $organization->title,
                    ]);
                }
            });
        }
    }

    private function addIndexIfMissing(string $tableName, string $columnName, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists && Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
                $table->index($columnName, $indexName);
            });
        }
    }
};

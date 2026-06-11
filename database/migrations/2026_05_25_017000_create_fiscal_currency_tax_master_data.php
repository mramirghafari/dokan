<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->createCurrencies();
        $this->createFiscalYears();
        $this->createTaxRates();
        $this->seedDefaults();
    }

    public function down()
    {
        // Non-destructive migration: keep fiscal/currency/tax master-data intact.
    }

    private function createCurrencies(): void
    {
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->string('code', 10)->index();
                $table->string('title');
                $table->string('symbol', 20)->nullable();
                $table->unsignedTinyInteger('decimal_places')->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('isActive')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'code'], 'currencies_tenant_code_unique');
            });
        }
    }

    private function createFiscalYears(): void
    {
        if (!Schema::hasTable('fiscal_years')) {
            Schema::create('fiscal_years', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->string('title');
                $table->date('starts_at');
                $table->date('ends_at');
                $table->string('status', 30)->default('open')->index();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status'], 'fiscal_years_tenant_status_index');
            });
        }
    }

    private function createTaxRates(): void
    {
        if (!Schema::hasTable('tax_rates')) {
            Schema::create('tax_rates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->string('code', 60)->index();
                $table->string('title');
                $table->decimal('rate', 8, 4)->default(0);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('isActive')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'isActive'], 'tax_rates_tenant_active_index');
            });
        }
    }

    private function seedDefaults(): void
    {
        $tenants = $this->tenants();

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->tenant_id ?? $tenant->id ?? null;
            $currencyCode = $this->currencyCode($tenant->currency_type ?? null);
            DB::table('currencies')->updateOrInsert(
                ['tenant_id' => $tenantId, 'code' => $currencyCode],
                ['title' => $currencyCode === 'IRR' ? 'ریال' : 'تومان', 'symbol' => $currencyCode === 'IRR' ? 'ریال' : 'تومان', 'decimal_places' => 0, 'is_default' => 1, 'isActive' => 1, 'created_at' => now(), 'updated_at' => now()]
            );

            [$start, $end] = $this->fiscalRange($tenant->fiscal_year_start ?? null, $tenant->fiscal_year_end ?? null);
            DB::table('fiscal_years')->updateOrInsert(
                ['tenant_id' => $tenantId, 'title' => 'سال مالی ' . substr($start, 0, 4)],
                ['starts_at' => $start, 'ends_at' => $end, 'status' => 'open', 'is_default' => 1, 'created_at' => now(), 'updated_at' => now()]
            );

            DB::table('tax_rates')->updateOrInsert(
                ['tenant_id' => $tenantId, 'code' => 'vat_default'],
                ['title' => 'مالیات ارزش افزوده پیش فرض', 'rate' => 0, 'valid_from' => $start, 'is_default' => 1, 'isActive' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function tenants()
    {
        if (!Schema::hasTable('tenants')) {
            return collect([(object) ['id' => null, 'tenant_id' => null, 'currency_type' => null, 'fiscal_year_start' => null, 'fiscal_year_end' => null]]);
        }

        $columns = ['id'];
        foreach (['tenant_id', 'currency_type', 'fiscal_year_start', 'fiscal_year_end'] as $column) {
            if (Schema::hasColumn('tenants', $column)) {
                $columns[] = $column;
            }
        }

        return DB::table('tenants')->select($columns)->get();
    }

    private function currencyCode($value): string
    {
        return in_array((string) $value, ['IRR', 'rial', 'ریال', '1'], true) ? 'IRR' : 'IRT';
    }

    private function fiscalRange($start, $end): array
    {
        $start = $start ?: now()->startOfYear()->toDateString();
        $end = $end ?: now()->endOfYear()->toDateString();

        return [$start, $end];
    }
};

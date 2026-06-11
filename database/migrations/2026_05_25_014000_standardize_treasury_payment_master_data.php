<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->createPaymentMethods();
        $this->extendAccounts();
        $this->extendPaymentTerminals();
        $this->seedPaymentMethods();
        $this->backfillTreasuryAccounts();
        $this->backfillPaymentTerminals();
    }

    public function down()
    {
        // Non-destructive migration: keep treasury/payment master-data intact.
    }

    private function createPaymentMethods(): void
    {
        if (Schema::hasTable('payment_methods')) {
            return;
        }

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedTinyInteger('legacy_code')->nullable()->index();
            $table->string('code', 60)->index();
            $table->string('title');
            $table->string('method_type', 30)->index();
            $table->boolean('requires_terminal')->default(false);
            $table->boolean('requires_due_date')->default(false);
            $table->boolean('requires_bank_name')->default(false);
            $table->unsignedBigInteger('default_account_id')->nullable()->index();
            $table->integer('sort_order')->default(0);
            $table->boolean('isActive')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'organization_id', 'method_type'], 'payment_methods_scope_type_index');
        });
    }

    private function extendAccounts(): void
    {
        if (!Schema::hasTable('accounts')) {
            return;
        }

        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'treasury_type')) {
                $table->string('treasury_type', 30)->nullable()->after('type');
            }
            if (!Schema::hasColumn('accounts', 'bank_name')) {
                $table->string('bank_name', 120)->nullable()->after('branch');
            }
            if (!Schema::hasColumn('accounts', 'opening_balance')) {
                $table->decimal('opening_balance', 18, 2)->default(0)->after('currency_type');
            }
            if (!Schema::hasColumn('accounts', 'is_treasury')) {
                $table->boolean('is_treasury')->default(false)->after('is_system');
            }
        });

        $this->addIndexIfMissing('accounts', 'treasury_type', 'accounts_treasury_type_index');
        $this->addIndexIfMissing('accounts', 'is_treasury', 'accounts_is_treasury_index');
    }

    private function extendPaymentTerminals(): void
    {
        if (!Schema::hasTable('payment_terminals')) {
            return;
        }

        Schema::table('payment_terminals', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_terminals', 'terminal_kind')) {
                $table->string('terminal_kind', 30)->nullable()->after('terminal_type');
            }
            if (!Schema::hasColumn('payment_terminals', 'settlement_account_id')) {
                $table->unsignedBigInteger('settlement_account_id')->nullable()->after('account_id');
            }
            if (!Schema::hasColumn('payment_terminals', 'terminal_status')) {
                $table->string('terminal_status', 30)->default('active')->after('terminal_number');
            }
            if (!Schema::hasColumn('payment_terminals', 'settlement_cycle')) {
                $table->string('settlement_cycle', 30)->nullable()->default('daily')->after('terminal_status');
            }
        });

        $this->addIndexIfMissing('payment_terminals', 'terminal_kind', 'payment_terminals_terminal_kind_index');
        $this->addIndexIfMissing('payment_terminals', 'settlement_account_id', 'payment_terminals_settlement_account_id_index');
        $this->addIndexIfMissing('payment_terminals', 'terminal_status', 'payment_terminals_terminal_status_index');
    }

    private function seedPaymentMethods(): void
    {
        $methods = [
            [1, 'cash', 'نقدی', 'cash', false, false, false, 10],
            [2, 'cheque', 'چک', 'cheque', false, true, true, 20],
            [3, 'bank_transfer', 'بانک / کارت به کارت', 'bank_transfer', false, false, true, 30],
            [4, 'terminal', 'کارتخوان / درگاه', 'terminal', true, false, false, 40],
        ];

        foreach ($methods as [$legacyCode, $code, $title, $type, $requiresTerminal, $requiresDueDate, $requiresBank, $sortOrder]) {
            DB::table('payment_methods')->updateOrInsert(
                ['tenant_id' => null, 'organization_id' => null, 'code' => $code],
                [
                    'legacy_code' => $legacyCode,
                    'title' => $title,
                    'method_type' => $type,
                    'requires_terminal' => $requiresTerminal,
                    'requires_due_date' => $requiresDueDate,
                    'requires_bank_name' => $requiresBank,
                    'sort_order' => $sortOrder,
                    'isActive' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function backfillTreasuryAccounts(): void
    {
        if (!Schema::hasTable('accounts')) {
            return;
        }

        DB::table('accounts')->where('type', 1)->update(['treasury_type' => 'bank', 'is_treasury' => 1]);
        DB::table('accounts')->where('type', 2)->update(['treasury_type' => 'cash', 'is_treasury' => 1]);
        DB::table('accounts')->whereNull('opening_balance')->update(['opening_balance' => 0]);
    }

    private function backfillPaymentTerminals(): void
    {
        if (!Schema::hasTable('payment_terminals')) {
            return;
        }

        DB::table('payment_terminals')->orderBy('id')->chunkById(500, function ($terminals) {
            foreach ($terminals as $terminal) {
                $isActive = property_exists($terminal, 'is_active') ? (bool) $terminal->is_active : true;
                $settlementCycle = property_exists($terminal, 'settlement_cycle') ? $terminal->settlement_cycle : null;

                DB::table('payment_terminals')->where('id', $terminal->id)->update([
                    'terminal_kind' => $this->terminalKind($terminal->terminal_type),
                    'settlement_account_id' => $terminal->account_id,
                    'terminal_status' => $isActive ? 'active' : 'inactive',
                    'settlement_cycle' => $settlementCycle ?: 'daily',
                ]);
            }
        });
    }

    private function terminalKind($legacyType): string
    {
        return match ((int) $legacyType) {
            2 => 'gateway',
            3 => 'ussd',
            default => 'pos',
        };
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

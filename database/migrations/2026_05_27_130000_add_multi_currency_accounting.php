<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('currency_id')->index();
                $table->date('rate_date')->index();
                $table->decimal('rate', 20, 6)->default(1);
                $table->string('source', 60)->nullable();
                $table->string('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'currency_id', 'rate_date'], 'exchange_rates_tenant_currency_date_unique');
            });
        }

        if (Schema::hasTable('voucher_items')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_items', 'currency_id')) {
                    $table->unsignedBigInteger('currency_id')->nullable()->after('account_id')->index();
                }

                if (!Schema::hasColumn('voucher_items', 'foreign_debit_amount')) {
                    $table->decimal('foreign_debit_amount', 20, 4)->nullable()->after('credit_amount');
                }

                if (!Schema::hasColumn('voucher_items', 'foreign_credit_amount')) {
                    $table->decimal('foreign_credit_amount', 20, 4)->nullable()->after('foreign_debit_amount');
                }

                if (!Schema::hasColumn('voucher_items', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 20, 6)->nullable()->after('foreign_credit_amount');
                }
            });
        }
    }

    public function down()
    {
        // Non-destructive migration: keep exchange rates and voucher currency fields intact.
    }
};

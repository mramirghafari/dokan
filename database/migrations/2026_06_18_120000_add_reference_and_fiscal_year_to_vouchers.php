<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceAndFiscalYearToVouchers extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'reference_number')) {
                $table->string('reference_number', 60)->nullable()->after('voucher_number');
            }

            if (!Schema::hasColumn('vouchers', 'fiscal_year_id')) {
                $table->unsignedBigInteger('fiscal_year_id')->nullable()->after('fiscal_year')->index();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'reference_number')) {
                $table->dropColumn('reference_number');
            }

            if (Schema::hasColumn('vouchers', 'fiscal_year_id')) {
                $table->dropColumn('fiscal_year_id');
            }
        });
    }
}

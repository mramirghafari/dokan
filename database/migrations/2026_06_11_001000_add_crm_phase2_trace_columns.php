<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crm_sales_board_cards') && !Schema::hasColumn('crm_sales_board_cards', 'pishfactor_id')) {
            Schema::table('crm_sales_board_cards', function (Blueprint $table) {
                $table->unsignedBigInteger('pishfactor_id')->nullable()->after('opportunity_id');
                $table->string('lost_reason', 500)->nullable()->after('status');
            });
        }

        if (Schema::hasTable('pishfactors') && !Schema::hasColumn('pishfactors', 'crm_sales_board_card_id')) {
            Schema::table('pishfactors', function (Blueprint $table) {
                $table->unsignedBigInteger('crm_sales_board_card_id')->nullable()->after('task_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_sales_board_cards')) {
            Schema::table('crm_sales_board_cards', function (Blueprint $table) {
                if (Schema::hasColumn('crm_sales_board_cards', 'pishfactor_id')) {
                    $table->dropColumn('pishfactor_id');
                }
                if (Schema::hasColumn('crm_sales_board_cards', 'lost_reason')) {
                    $table->dropColumn('lost_reason');
                }
            });
        }

        if (Schema::hasTable('pishfactors') && Schema::hasColumn('pishfactors', 'crm_sales_board_card_id')) {
            Schema::table('pishfactors', function (Blueprint $table) {
                $table->dropColumn('crm_sales_board_card_id');
            });
        }
    }
};

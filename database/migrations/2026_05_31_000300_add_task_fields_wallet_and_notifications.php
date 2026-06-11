<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crm_sales_board_cards')) {
            Schema::table('crm_sales_board_cards', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_sales_board_cards', 'assigned_user_ids')) {
                    $table->json('assigned_user_ids')->nullable()->after('assigned_user_id');
                }

                if (!Schema::hasColumn('crm_sales_board_cards', 'estimate_minutes')) {
                    $table->unsignedInteger('estimate_minutes')->nullable()->after('priority');
                }

                if (!Schema::hasColumn('crm_sales_board_cards', 'started_at')) {
                    $table->dateTime('started_at')->nullable()->after('moved_at');
                }

                if (!Schema::hasColumn('crm_sales_board_cards', 'ended_at')) {
                    $table->dateTime('ended_at')->nullable()->after('started_at');
                }

                if (!Schema::hasColumn('crm_sales_board_cards', 'activity_logs')) {
                    $table->json('activity_logs')->nullable()->after('source_filter');
                }
            });
        }

        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (!Schema::hasColumn('tenants', 'wallet_balance')) {
                    $table->decimal('wallet_balance', 18, 2)->default(0)->after('subscription_ends_at');
                }

                if (!Schema::hasColumn('tenants', 'sms_unit_price_toman')) {
                    $table->decimal('sms_unit_price_toman', 12, 2)->default(0)->after('wallet_balance');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep task timing, notification and wallet history intact.
    }
};

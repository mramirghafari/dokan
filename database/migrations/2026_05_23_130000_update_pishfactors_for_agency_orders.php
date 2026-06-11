<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pishfactors')) {
            DB::statement('ALTER TABLE pishfactors MODIFY customer_id BIGINT UNSIGNED NULL');

            Schema::table('pishfactors', function (Blueprint $table) {
                if (!Schema::hasColumn('pishfactors', 'is_agency_order')) {
                    $table->boolean('is_agency_order')->default(false)->after('customer_id');
                }

                if (!Schema::hasColumn('pishfactors', 'agency_user_id')) {
                    $table->unsignedBigInteger('agency_user_id')->nullable()->after('is_agency_order');
                    $table->foreign('agency_user_id')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pishfactors')) {
            Schema::table('pishfactors', function (Blueprint $table) {
                if (Schema::hasColumn('pishfactors', 'agency_user_id')) {
                    $table->dropForeign(['agency_user_id']);
                    $table->dropColumn('agency_user_id');
                }

                if (Schema::hasColumn('pishfactors', 'is_agency_order')) {
                    $table->dropColumn('is_agency_order');
                }
            });

            DB::statement('UPDATE pishfactors SET customer_id = 0 WHERE customer_id IS NULL');
            DB::statement('ALTER TABLE pishfactors MODIFY customer_id BIGINT UNSIGNED NOT NULL');
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('logs')) {
            return;
        }

        if (Schema::hasColumn('logs', 'user_id')) {
            Schema::table('logs', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            DB::statement('ALTER TABLE `logs` MODIFY `user_id` BIGINT UNSIGNED NULL');
            Schema::table('logs', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users');
            });
        }

        if (Schema::hasColumn('logs', 'action')) {
            DB::statement(
                "ALTER TABLE `logs` MODIFY COLUMN `action` ENUM('create','update','delete','restore','forceDelete','login','logout','failed_login') NOT NULL"
            );
        }
    }

    public function down(): void
    {
        // Non-destructive: audit evidence should remain intact.
    }
};

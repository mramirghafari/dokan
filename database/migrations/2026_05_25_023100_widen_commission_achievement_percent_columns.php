<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('targets') && Schema::hasColumn('targets', 'achievement_threshold_percent')) {
            DB::statement('ALTER TABLE `targets` MODIFY `achievement_threshold_percent` DECIMAL(15,2) NOT NULL DEFAULT 100');
        }

        if (Schema::hasTable('commission_plan_tiers')) {
            if (Schema::hasColumn('commission_plan_tiers', 'from_achievement_percent')) {
                DB::statement('ALTER TABLE `commission_plan_tiers` MODIFY `from_achievement_percent` DECIMAL(15,2) NOT NULL DEFAULT 0');
            }
            if (Schema::hasColumn('commission_plan_tiers', 'to_achievement_percent')) {
                DB::statement('ALTER TABLE `commission_plan_tiers` MODIFY `to_achievement_percent` DECIMAL(15,2) NULL');
            }
        }

        if (Schema::hasTable('commission_settlements') && Schema::hasColumn('commission_settlements', 'achievement_percent')) {
            DB::statement('ALTER TABLE `commission_settlements` MODIFY `achievement_percent` DECIMAL(15,2) NOT NULL DEFAULT 0');
        }
    }

    public function down()
    {
        // Non-destructive migration: do not shrink stored achievement percentages.
    }
};

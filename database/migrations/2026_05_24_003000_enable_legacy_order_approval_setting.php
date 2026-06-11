<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')
            ->whereNull('tenant_id')
            ->where('title', 'feature_manager_order_approval')
            ->where('value', 'no')
            ->update([
                'value' => 'yes',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        // Non-destructive by design: panel settings may already be edited in production.
    }
};

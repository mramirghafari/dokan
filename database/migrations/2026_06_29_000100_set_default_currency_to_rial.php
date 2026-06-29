<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where(function ($query) {
                    $query->where('key', 'currency_type')->orWhere('title', 'currency_type');
                })
                ->where('value', 'toman')
                ->update(['value' => 'rial', 'updated_at' => now()]);
        }

        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'currency_type')) {
            DB::table('tenants')
                ->whereIn('currency_type', ['toman', '1', 1])
                ->update(['currency_type' => 'rial', 'updated_at' => now()]);
        }

        if (Schema::hasTable('organizations') && Schema::hasColumn('organizations', 'currency_type')) {
            DB::table('organizations')
                ->where('currency_type', 1)
                ->update(['currency_type' => 2, 'updated_at' => now()]);
        }

        if (Schema::hasTable('factor_makers') && Schema::hasColumn('factor_makers', 'currency_type')) {
            DB::table('factor_makers')
                ->where('currency_type', 1)
                ->update(['currency_type' => 2, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Non-destructive: currency preference may already be edited in production.
    }
};

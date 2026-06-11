<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')
            ->whereIn('title', [
                'stores',
                'categories',
                'cities',
                'regions',
                'areas',
            ])
            ->update([
                'isActive' => 1,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Non-destructive rollback: keep restored menu permissions active.
    }
};

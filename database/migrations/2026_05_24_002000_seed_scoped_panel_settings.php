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

        $now = now();

        foreach ((array) config('panel_settings.definitions', []) as $title => $definition) {
            $exists = DB::table('settings')
                ->whereNull('tenant_id')
                ->where('title', $title)
                ->exists();

            if ($exists) {
                continue;
            }

            $defaultValue = $definition['default'] ?? null;

            DB::table('settings')->insert([
                'tenant_id' => null,
                'title' => $title,
                'value' => is_array($defaultValue) ? json_encode($defaultValue) : $defaultValue,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        // Non-destructive by design: panel settings may already be edited in production.
    }
};

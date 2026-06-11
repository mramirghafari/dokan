<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('features')) {
            return;
        }

        $moduleIds = Schema::hasTable('modules') ? DB::table('modules')->pluck('id', 'key')->toArray() : [];
        $groupToModule = [
            'sales_workflow' => 'sales',
            'logistics' => 'delivery',
            'warehouse' => 'warehouse',
            'management' => 'targets',
            'finance' => 'accounting',
            'approval' => 'security',
            'visibility' => 'security',
        ];

        $sort = 10;
        foreach ((array) config('panel_settings.definitions', []) as $key => $definition) {
            if (!str_starts_with($key, 'feature_')) {
                continue;
            }

            $moduleKey = $groupToModule[$definition['group'] ?? 'sales_workflow'] ?? 'sales';

            DB::table('features')->updateOrInsert(
                ['key' => $key],
                [
                    'module_id' => $moduleIds[$moduleKey] ?? null,
                    'title' => $definition['label'] ?? $key,
                    'description' => null,
                    'type' => $definition['type'] ?? 'boolean',
                    'default_value' => $definition['default'] ?? null,
                    'is_active' => true,
                    'sort_order' => $sort,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $sort += 10;
        }
    }

    public function down(): void
    {
        // Non-destructive: feature definitions and panel settings may already be in use.
    }
};

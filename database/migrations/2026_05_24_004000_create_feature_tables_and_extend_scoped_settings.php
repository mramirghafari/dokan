<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendSettingsTable();
        $this->createModulesTable();
        $this->createFeaturesTable();
        $this->createTenantFeaturesTable();
        $this->createOrganizationFeaturesTable();
        $this->seedModulesAndFeatures();
        $this->syncExistingSettings();
    }

    public function down()
    {
        // Non-destructive by design: these tables become configuration history after rollout.
    }

    private function extendSettingsTable(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'organization_id')) {
                $table->unsignedInteger('organization_id')->nullable()->after('tenant_id');
            }
            if (!Schema::hasColumn('settings', 'store_id')) {
                $table->unsignedInteger('store_id')->nullable()->after('organization_id');
            }
            if (!Schema::hasColumn('settings', 'user_id')) {
                $table->unsignedInteger('user_id')->nullable()->after('store_id');
            }
            if (!Schema::hasColumn('settings', 'scope')) {
                $table->string('scope', 30)->default('global')->after('user_id');
            }
            if (!Schema::hasColumn('settings', 'category')) {
                $table->string('category', 60)->default('general')->after('scope');
            }
            if (!Schema::hasColumn('settings', 'key')) {
                $table->string('key')->nullable()->after('category');
            }
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type', 30)->default('string')->after('value');
            }
            if (!Schema::hasColumn('settings', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('type');
            }
            if (!Schema::hasColumn('settings', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('settings', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable()->after('created_by');
            }
        });

        $this->addIndexIfMissing('settings', ['scope', 'tenant_id', 'organization_id', 'store_id', 'user_id'], 'settings_scope_owner_index');
        $this->addIndexIfMissing('settings', ['key'], 'settings_key_index');

        DB::table('settings')
            ->whereNull('key')
            ->update(['key' => DB::raw('title')]);

        DB::table('settings')
            ->whereNull('scope')
            ->orWhere('scope', '')
            ->update(['scope' => DB::raw('CASE WHEN tenant_id IS NULL THEN "global" ELSE "tenant" END')]);
    }

    private function createModulesTable(): void
    {
        if (Schema::hasTable('modules')) {
            return;
        }

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    private function createFeaturesTable(): void
    {
        if (Schema::hasTable('features')) {
            return;
        }

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->string('key')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 30)->default('boolean');
            $table->text('default_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('module_id');
        });
    }

    private function createTenantFeaturesTable(): void
    {
        if (Schema::hasTable('tenant_features')) {
            return;
        }

        Schema::create('tenant_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tenant_id');
            $table->unsignedBigInteger('feature_id');
            $table->string('key');
            $table->boolean('is_enabled')->default(false);
            $table->text('value')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_id'], 'tenant_features_tenant_feature_unique');
            $table->index(['tenant_id', 'key'], 'tenant_features_tenant_key_index');
        });
    }

    private function createOrganizationFeaturesTable(): void
    {
        if (Schema::hasTable('organization_features')) {
            return;
        }

        Schema::create('organization_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('organization_id');
            $table->unsignedBigInteger('feature_id');
            $table->string('key');
            $table->boolean('is_enabled')->default(false);
            $table->text('value')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'feature_id'], 'organization_features_org_feature_unique');
            $table->index(['organization_id', 'key'], 'organization_features_org_key_index');
        });
    }

    private function seedModulesAndFeatures(): void
    {
        if (!Schema::hasTable('modules') || !Schema::hasTable('features')) {
            return;
        }

        $modules = [
            'sales' => ['title' => 'فروش', 'sort_order' => 10],
            'accounting' => ['title' => 'حسابداری و خزانه', 'sort_order' => 20],
            'warehouse' => ['title' => 'انبار و کالا', 'sort_order' => 30],
            'delivery' => ['title' => 'پخش و لجستیک', 'sort_order' => 40],
            'crm' => ['title' => 'مشتریان و ارتباطات', 'sort_order' => 50],
            'hr' => ['title' => 'کارکنان', 'sort_order' => 60],
            'targets' => ['title' => 'تارگت و عملکرد', 'sort_order' => 70],
            'security' => ['title' => 'امنیت و دسترسی', 'sort_order' => 80],
            'reporting' => ['title' => 'گزارش ها', 'sort_order' => 90],
            'mobile_api' => ['title' => 'API و موبایل', 'sort_order' => 100],
        ];

        foreach ($modules as $key => $module) {
            DB::table('modules')->updateOrInsert(
                ['key' => $key],
                [
                    'title' => $module['title'],
                    'description' => $module['description'] ?? null,
                    'is_active' => true,
                    'sort_order' => $module['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $moduleIds = DB::table('modules')->pluck('id', 'key')->toArray();
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $sort += 10;
        }
    }

    private function syncExistingSettings(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        foreach ((array) config('panel_settings.definitions', []) as $key => $definition) {
            DB::table('settings')
                ->where(function ($query) use ($key) {
                    $query->where('title', $key)->orWhere('key', $key);
                })
                ->update([
                    'key' => $key,
                    'category' => $definition['group'] ?? 'general',
                    'type' => $definition['type'] ?? 'string',
                    'scope' => DB::raw('CASE WHEN tenant_id IS NULL THEN "global" ELSE "tenant" END'),
                    'updated_at' => now(),
                ]);
        }

        if (!Schema::hasTable('features') || !Schema::hasTable('tenant_features')) {
            return;
        }

        $features = DB::table('features')->pluck('id', 'key');

        DB::table('settings')
            ->whereNotNull('tenant_id')
            ->whereIn('key', $features->keys()->all())
            ->orderBy('id')
            ->chunkById(500, function ($settings) use ($features) {
                foreach ($settings as $setting) {
                    $featureId = $features[$setting->key] ?? null;

                    if (!$featureId) {
                        continue;
                    }

                    DB::table('tenant_features')->updateOrInsert(
                        [
                            'tenant_id' => (int) $setting->tenant_id,
                            'feature_id' => (int) $featureId,
                        ],
                        [
                            'key' => $setting->key,
                            'is_enabled' => $setting->value === 'yes',
                            'value' => $setting->value,
                            'is_locked' => (bool) ($setting->is_locked ?? false),
                            'created_by' => $setting->created_by ?? null,
                            'updated_by' => $setting->updated_by ?? null,
                            'created_at' => $setting->created_at ?? now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if ($exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }
};

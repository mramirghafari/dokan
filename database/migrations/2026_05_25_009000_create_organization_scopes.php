<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('organization_scopes')) {
            Schema::create('organization_scopes', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->index();
                $table->string('scopeable_type');
                $table->unsignedBigInteger('scopeable_id');
                $table->boolean('is_primary')->default(false);
                $table->string('source', 50)->default('legacy_organization_id');
                $table->timestamps();

                $table->unique(['organization_id', 'scopeable_type', 'scopeable_id'], 'org_scopes_unique_owner');
                $table->index(['scopeable_type', 'scopeable_id'], 'org_scopes_owner_index');
            });
        }

        $this->backfill('users', App\Models\User::class);
        $this->backfill('stores', App\Models\Store::class);
        $this->backfill('products', App\Models\Product::class);
    }

    public function down()
    {
        // Intentionally left blank to preserve normalized organization visibility data.
    }

    private function backfill(string $tableName, string $modelClass): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'organization_id')) {
            return;
        }

        DB::table($tableName)
            ->select('id', 'organization_id', 'tenant_id')
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($modelClass) {
                foreach ($rows as $row) {
                    $organizationIds = $this->existingOrganizationIds($this->extractOrganizationIds($row->organization_id));

                    foreach ($organizationIds as $index => $organizationId) {
                        DB::table('organization_scopes')->insertOrIgnore([
                            'tenant_id' => $row->tenant_id ?: $this->tenantIdForOrganization($organizationId),
                            'organization_id' => $organizationId,
                            'scopeable_type' => $modelClass,
                            'scopeable_id' => $row->id,
                            'is_primary' => $index === 0,
                            'source' => 'legacy_organization_id',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    private function existingOrganizationIds(array $organizationIds): array
    {
        if (empty($organizationIds) || !Schema::hasTable('organizations')) {
            return [];
        }

        return DB::table('organizations')
            ->whereIn('id', $organizationIds)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    private function tenantIdForOrganization(int $organizationId): ?int
    {
        if (!Schema::hasTable('organizations')) {
            return null;
        }

        if (Schema::hasColumn('organizations', 'tenant_id')) {
            $tenantId = DB::table('organizations')->where('id', $organizationId)->value('tenant_id');
            if ($tenantId) {
                return (int) $tenantId;
            }
        }

        if (Schema::hasColumn('organizations', 'tenants_id')) {
            $tenantId = DB::table('organizations')->where('id', $organizationId)->value('tenants_id');
            if ($tenantId) {
                return (int) $tenantId;
            }
        }

        return null;
    }

    private function extractOrganizationIds($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? $decoded : [$raw];
        $ids = [];

        foreach ($values as $value) {
            if (is_array($value)) {
                foreach ($this->extractOrganizationIds(json_encode($value)) as $nestedId) {
                    $ids[] = $nestedId;
                }
                continue;
            }

            if (is_numeric($value)) {
                $ids[] = (int) $value;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }
};

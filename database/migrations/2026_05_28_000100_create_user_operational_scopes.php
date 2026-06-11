<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->createUserScopesTable();
        $this->backfillUserScopes();
    }

    public function down()
    {
        // Non-destructive rollback: keep user scope data to preserve operational access history.
    }

    private function createUserScopesTable(): void
    {
        if (Schema::hasTable('user_scopes')) {
            return;
        }

        Schema::create('user_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('scope_type')->index();
            $table->unsignedBigInteger('scope_id')->default(0)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'scope_type', 'scope_id'], 'user_scopes_unique_scope');
            $table->index(['tenant_id', 'scope_type', 'scope_id'], 'user_scopes_tenant_scope_index');
        });
    }

    private function backfillUserScopes(): void
    {
        if (!Schema::hasTable('user_scopes') || !Schema::hasTable('users') || !Schema::hasColumn('users', 'organization_id')) {
            return;
        }

        DB::table('users')
            ->whereNotNull('organization_id')
            ->orderBy('id')
            ->get(['id', 'organization_id', 'tenant_id', 'tenants_id'])
            ->each(function ($user) {
                $organizationIds = $this->parseScopeIds($user->organization_id);

                foreach ($organizationIds as $organizationId) {
                    DB::table('user_scopes')->updateOrInsert(
                        [
                            'user_id' => $user->id,
                            'scope_type' => 'organization',
                            'scope_id' => $organizationId,
                        ],
                        [
                            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });
    }

    private function parseScopeIds($raw): array
    {
        if ($raw === null || $raw === '' || $raw === '0') {
            return [];
        }

        $decoded = json_decode((string) $raw, true);
        $values = is_array($decoded) ? $decoded : [$raw];

        return collect($values)
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }
};

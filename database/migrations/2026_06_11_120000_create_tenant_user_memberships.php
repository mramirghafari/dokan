<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_user_memberships')) {
            Schema::create('tenant_user_memberships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->boolean('is_admin')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'tenant_id'], 'tenant_user_memberships_user_tenant_unique');
            });
        }

        if (!Schema::hasTable('users')) {
            return;
        }

        $users = DB::table('users')
            ->select('id', 'tenant_id', 'tenants_id', 'organization_id', 'isAdmin', 'isActive')
            ->where(function ($query) {
                $query->whereNotNull('tenant_id')
                    ->where('tenant_id', '>', 0)
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('tenants_id')
                            ->where('tenants_id', '>', 0);
                    });
            })
            ->get();

        foreach ($users as $user) {
            $tenantId = (int) ($user->tenant_id ?: $user->tenants_id ?: 0);

            if ($tenantId <= 0) {
                continue;
            }

            $organizationId = $this->resolveOrganizationId($user->organization_id);

            $exists = DB::table('tenant_user_memberships')
                ->where('user_id', $user->id)
                ->where('tenant_id', $tenantId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('tenant_user_memberships')->insert([
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'is_admin' => (int) ($user->isAdmin ?? 0) === 1,
                'is_active' => (int) ($user->isActive ?? 1) === 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_user_memberships');
    }

    private function resolveOrganizationId($organizationId): ?int
    {
        if (empty($organizationId)) {
            return null;
        }

        $decoded = json_decode((string) $organizationId, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return (int) $organizationId ?: null;
    }
};

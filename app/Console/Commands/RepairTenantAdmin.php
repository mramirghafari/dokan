<?php

namespace App\Console\Commands;

use App\Models\Tenants;
use App\Models\User;
use App\Services\NewTenantProvisioner;
use Illuminate\Console\Command;

class RepairTenantAdmin extends Command
{
    protected $signature = 'panel:repair-admin {username : Admin username to repair}';

    protected $description = 'Assign panel_manager role and onboarding settings for an existing panel admin';

    public function handle(NewTenantProvisioner $provisioner): int
    {
        $user = User::query()->where('username', $this->argument('username'))->first();

        if (!$user) {
            $this->error('کاربر یافت نشد.');

            return self::FAILURE;
        }

        $tenantId = $user->tenant_id ?: $user->tenants_id;

        if (!$tenantId) {
            $this->error('پنل مرتبط با کاربر یافت نشد.');

            return self::FAILURE;
        }

        $tenant = Tenants::query()->find($tenantId);

        if (!$tenant) {
            $this->error('پنل یافت نشد.');

            return self::FAILURE;
        }

        $result = $provisioner->repair($tenant, $user);

        $this->info('پنل «' . $tenant->name . '» برای کاربر «' . $user->username . '» تعمیر شد.');
        $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}

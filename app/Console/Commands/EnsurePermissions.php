<?php

namespace App\Console\Commands;

use App\Services\PermissionBootstrapService;
use App\Services\RoohiTradePanelProvisioner;
use Illuminate\Console\Command;

class EnsurePermissions extends Command
{
    protected $signature = 'permissions:ensure';

    protected $description = 'Seed default permission catalog and sync admin roles';

    public function handle(PermissionBootstrapService $bootstrap): int
    {
        $created = $bootstrap->ensureCatalog();
        $bootstrap->syncGlobalAdminRole();

        $this->info("Permissions ensured. Created: {$created}");

        return self::SUCCESS;
    }
}

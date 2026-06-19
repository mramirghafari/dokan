<?php

use App\Services\PermissionBootstrapService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
            return;
        }

        app(PermissionBootstrapService::class)->ensureCatalog();
        app(PermissionBootstrapService::class)->syncGlobalAdminRole();
    }

    public function down(): void
    {
        // Non-destructive: keep seeded permissions.
    }
};

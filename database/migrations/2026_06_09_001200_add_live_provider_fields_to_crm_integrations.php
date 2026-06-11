<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crm_integration_connections')) {
            Schema::table('crm_integration_connections', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_integration_connections', 'credentials')) {
                    $table->longText('credentials')->nullable()->after('settings');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: provider credentials may be needed for active live integrations.
    }
};

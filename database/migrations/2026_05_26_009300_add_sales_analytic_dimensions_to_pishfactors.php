<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pishfactors')) {
            return;
        }

        Schema::table('pishfactors', function (Blueprint $table) {
            if (!Schema::hasColumn('pishfactors', 'revenue_center_id')) {
                $table->unsignedBigInteger('revenue_center_id')->nullable()->index()->after('price_list_id');
            }

            if (!Schema::hasColumn('pishfactors', 'project_code')) {
                $table->string('project_code', 100)->nullable()->index()->after('revenue_center_id');
            }

            if (!Schema::hasColumn('pishfactors', 'contract_code')) {
                $table->string('contract_code', 120)->nullable()->index()->after('project_code');
            }

            if (!Schema::hasColumn('pishfactors', 'route_code')) {
                $table->string('route_code', 120)->nullable()->index()->after('contract_code');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive rollback: sales analytic traces must stay available for reports.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('accounts')) {
            return;
        }

        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'asset_class')) {
                $table->string('asset_class', 20)->nullable()->after('account_category');
            }
            if (!Schema::hasColumn('accounts', 'asset_type')) {
                $table->string('asset_type', 40)->nullable()->after('asset_class');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('accounts')) {
            return;
        }

        Schema::table('accounts', function (Blueprint $table) {
            foreach (['asset_class', 'asset_type'] as $column) {
                if (Schema::hasColumn('accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('units')) {
            return;
        }

        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'usage_scope')) {
                $table->string('usage_scope', 20)->default('product')->after('unit_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('units') || !Schema::hasColumn('units', 'usage_scope')) {
            return;
        }

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('usage_scope');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('factor_makers')) {
            return;
        }

        Schema::table('factor_makers', function (Blueprint $table) {
            if (!Schema::hasColumn('factor_makers', 'business_profile')) {
                $table->string('business_profile', 40)->nullable()->default('distribution')->after('pr_type');
            }
            if (!Schema::hasColumn('factor_makers', 'line_layout')) {
                $table->json('line_layout')->nullable()->after('business_profile');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('factor_makers')) {
            return;
        }

        Schema::table('factor_makers', function (Blueprint $table) {
            if (Schema::hasColumn('factor_makers', 'line_layout')) {
                $table->dropColumn('line_layout');
            }
            if (Schema::hasColumn('factor_makers', 'business_profile')) {
                $table->dropColumn('business_profile');
            }
        });
    }
};

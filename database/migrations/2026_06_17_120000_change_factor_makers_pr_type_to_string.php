<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('factor_makers') || !Schema::hasColumn('factor_makers', 'pr_type')) {
            return;
        }

        // مقادیر قدیمی boolean را به کلید رشته‌ای جدید نگاشت کن، قبل از تغییر نوع ستون.
        $legacyValues = DB::table('factor_makers')->whereNotNull('pr_type')->pluck('pr_type', 'id');

        Schema::table('factor_makers', function (Blueprint $table) {
            $table->string('pr_type', 40)->nullable()->default('non_refrigerated')->change();
        });

        foreach ($legacyValues as $id => $value) {
            $key = ((string) $value === '1') ? 'refrigerated' : 'non_refrigerated';
            DB::table('factor_makers')->where('id', $id)->update(['pr_type' => $key]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('factor_makers') || !Schema::hasColumn('factor_makers', 'pr_type')) {
            return;
        }

        DB::table('factor_makers')
            ->where('pr_type', 'refrigerated')
            ->update(['pr_type' => 1]);

        DB::table('factor_makers')
            ->where('pr_type', '!=', 'refrigerated')
            ->update(['pr_type' => 0]);

        Schema::table('factor_makers', function (Blueprint $table) {
            $table->boolean('pr_type')->nullable()->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (!Schema::hasColumn('tenants', 'subscription_type')) {
                    $table->string('subscription_type', 30)->nullable()->after('status');
                }
                if (!Schema::hasColumn('tenants', 'subscription_started_at')) {
                    $table->date('subscription_started_at')->nullable()->after('subscription_type');
                }
                if (!Schema::hasColumn('tenants', 'subscription_ends_at')) {
                    $table->date('subscription_ends_at')->nullable()->after('subscription_started_at');
                }
            });
        }

        if (Schema::hasTable('users')) {
            try {
                DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
            } catch (Throwable $exception) {
                report($exception);
            }

            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'national_code')) {
                    $table->string('national_code', 20)->nullable()->after('personalID');
                }
                if (!Schema::hasColumn('users', 'address')) {
                    $table->text('address')->nullable()->after('national_code');
                }
                if (!Schema::hasColumn('users', 'postal_code')) {
                    $table->string('postal_code', 20)->nullable()->after('address');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                if (Schema::hasColumn('tenants', 'subscription_ends_at')) {
                    $table->dropColumn('subscription_ends_at');
                }
                if (Schema::hasColumn('tenants', 'subscription_started_at')) {
                    $table->dropColumn('subscription_started_at');
                }
                if (Schema::hasColumn('tenants', 'subscription_type')) {
                    $table->dropColumn('subscription_type');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'postal_code')) {
                    $table->dropColumn('postal_code');
                }
                if (Schema::hasColumn('users', 'address')) {
                    $table->dropColumn('address');
                }
                if (Schema::hasColumn('users', 'national_code')) {
                    $table->dropColumn('national_code');
                }
            });
        }
    }
};

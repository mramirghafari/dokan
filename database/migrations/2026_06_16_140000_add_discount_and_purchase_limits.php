<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'max_discount_amount')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('max_discount_amount', 18, 2)->nullable()->after('discount');
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'max_purchase_amount')) {
                    $table->decimal('max_purchase_amount', 18, 2)->nullable()->after('customer_code');
                }
                if (!Schema::hasColumn('customers', 'max_discount_amount')) {
                    $table->decimal('max_discount_amount', 18, 2)->nullable()->after('max_purchase_amount');
                }
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'max_discount_percent')) {
                    $table->decimal('max_discount_percent', 8, 2)->nullable()->after('description');
                }
                if (!Schema::hasColumn('roles', 'max_discount_amount')) {
                    $table->decimal('max_discount_amount', 18, 2)->nullable()->after('max_discount_percent');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'max_discount_amount')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('max_discount_amount');
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $columns = array_filter([
                    Schema::hasColumn('customers', 'max_discount_amount') ? 'max_discount_amount' : null,
                    Schema::hasColumn('customers', 'max_purchase_amount') ? 'max_purchase_amount' : null,
                ]);
                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $columns = array_filter([
                    Schema::hasColumn('roles', 'max_discount_amount') ? 'max_discount_amount' : null,
                    Schema::hasColumn('roles', 'max_discount_percent') ? 'max_discount_percent' : null,
                ]);
                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};

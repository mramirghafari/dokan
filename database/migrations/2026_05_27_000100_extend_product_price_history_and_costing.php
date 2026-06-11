<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'purchase_price')) {
                    $table->decimal('purchase_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('products', 'cost_price')) {
                    $table->decimal('cost_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('products', 'representative_price')) {
                    $table->decimal('representative_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('products', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('products', 'consumer_price')) {
                    $table->decimal('consumer_price', 18, 2)->nullable();
                }
            });
        }

        if (Schema::hasTable('price_logs')) {
            Schema::table('price_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('price_logs', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->nullable()->index();
                }

                if (!Schema::hasColumn('price_logs', 'organization_id')) {
                    $table->string('organization_id')->nullable()->index();
                }

                if (!Schema::hasColumn('price_logs', 'sale_price')) {
                    $table->decimal('sale_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('price_logs', 'purchase_price')) {
                    $table->decimal('purchase_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('price_logs', 'cost_price')) {
                    $table->decimal('cost_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('price_logs', 'representative_price')) {
                    $table->decimal('representative_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('price_logs', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('price_logs', 'consumer_price')) {
                    $table->decimal('consumer_price', 18, 2)->nullable();
                }

                if (!Schema::hasColumn('price_logs', 'change_source')) {
                    $table->string('change_source', 60)->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: price history and costing fields are audit data.
    }
};

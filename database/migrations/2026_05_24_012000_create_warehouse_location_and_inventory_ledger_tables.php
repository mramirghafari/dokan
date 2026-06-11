<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warehouse_locations')) {
            Schema::create('warehouse_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('type', 30)->default('rack');
                $table->string('code', 80);
                $table->string('title', 190);
                $table->string('path', 500)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'store_id', 'code'], 'warehouse_locations_tenant_store_code_unique');
                $table->index(['tenant_id', 'store_id', 'type', 'is_active'], 'warehouse_locations_lookup_index');
                $table->index(['store_id', 'parent_id'], 'warehouse_locations_parent_index');
            });
        }

        if (!Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('warehouse_location_id')->default(0);
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('receipt_id')->nullable();
                $table->unsignedBigInteger('transfer_id')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('source_type', 120)->nullable();
                $table->string('movement_type', 40);
                $table->string('direction', 10);
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('quantity_sub_unit', 18, 3)->default(0);
                $table->string('unit', 40)->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->dateTime('occurred_at')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'store_id', 'product_id', 'occurred_at'], 'inventory_movements_fast_cardex_index');
                $table->index(['tenant_id', 'store_id', 'warehouse_location_id', 'product_id'], 'inventory_movements_location_index');
                $table->index(['receipt_id'], 'inventory_movements_receipt_index');
                $table->index(['transfer_id'], 'inventory_movements_transfer_index');
                $table->index(['source_type', 'source_id'], 'inventory_movements_source_index');
            });
        }

        if (!Schema::hasTable('inventory_balances')) {
            Schema::create('inventory_balances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('warehouse_location_id')->default(0);
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 18, 3)->default(0);
                $table->decimal('quantity_sub_unit', 18, 3)->default(0);
                $table->decimal('reserved_quantity', 18, 3)->default(0);
                $table->decimal('minimum_quantity', 18, 3)->nullable();
                $table->decimal('maximum_quantity', 18, 3)->nullable();
                $table->dateTime('last_movement_at')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'store_id', 'warehouse_location_id', 'product_id'], 'inventory_balances_stock_unique');
                $table->index(['tenant_id', 'store_id', 'product_id'], 'inventory_balances_product_lookup_index');
                $table->index(['tenant_id', 'store_id', 'warehouse_location_id'], 'inventory_balances_location_lookup_index');
            });
        }

        Schema::table('depots', function (Blueprint $table) {
            if (!Schema::hasColumn('depots', 'warehouse_location_id')) {
                $table->unsignedBigInteger('warehouse_location_id')->default(0)->after('store_id');
                $table->index(['store_id', 'warehouse_location_id'], 'depots_store_location_index');
            }
        });

        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('tenant_id');
                $table->index(['tenant_id', 'organization_id', 'store_id'], 'receipts_tenant_org_store_index');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive by design: warehouse ledger data must not be dropped in production.
    }
};

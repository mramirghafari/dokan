<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_inventory_reservations')) {
            return;
        }

        Schema::table('sales_inventory_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_inventory_reservations', 'batch_no')) {
                $table->string('batch_no', 120)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'lot_no')) {
                $table->string('lot_no', 120)->nullable()->after('batch_no');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'serial_no')) {
                $table->string('serial_no', 160)->nullable()->after('lot_no');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'manufactured_at')) {
                $table->date('manufactured_at')->nullable()->after('serial_no');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('manufactured_at');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'color')) {
                $table->string('color', 80)->nullable()->after('expiry_date');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'size')) {
                $table->string('size', 80)->nullable()->after('color');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'quality_grade')) {
                $table->string('quality_grade', 80)->nullable()->after('size');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'available_quantity_snapshot')) {
                $table->decimal('available_quantity_snapshot', 18, 3)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'shortage_quantity')) {
                $table->decimal('shortage_quantity', 18, 3)->default(0)->after('available_quantity_snapshot');
            }
            if (!Schema::hasColumn('sales_inventory_reservations', 'release_reason')) {
                $table->string('release_reason', 120)->nullable()->after('released_at');
            }
        });

        Schema::table('sales_inventory_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('sales_inventory_reservations', 'batch_no')) {
                $table->index(['tenant_id', 'store_id', 'product_id', 'batch_no', 'status'], 'sales_reservations_batch_status_index');
            }
            if (Schema::hasColumn('sales_inventory_reservations', 'serial_no')) {
                $table->index(['tenant_id', 'product_id', 'serial_no', 'status'], 'sales_reservations_serial_status_index');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: reservation audit data must be kept.
    }
};

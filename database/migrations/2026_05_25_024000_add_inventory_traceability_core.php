<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendProducts();
        $this->extendInventoryMovements();
        $this->extendDepotRows();
        $this->extendSalesRows();
        $this->createTraceBalances();
    }

    public function down(): void
    {
        // Non-destructive migration: keep traceability and audit history intact.
    }

    private function extendProducts(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'traceability_mode')) {
                $table->string('traceability_mode')->default('none')->after('stock_tracking_mode');
            }
            if (!Schema::hasColumn('products', 'requires_expiry_tracking')) {
                $table->boolean('requires_expiry_tracking')->default(false)->after('traceability_mode');
            }
            if (!Schema::hasColumn('products', 'requires_serial_tracking')) {
                $table->boolean('requires_serial_tracking')->default(false)->after('requires_expiry_tracking');
            }
        });
    }

    private function extendInventoryMovements(): void
    {
        if (!Schema::hasTable('inventory_movements')) {
            return;
        }

        Schema::table('inventory_movements', function (Blueprint $table) {
            $this->addTraceColumns($table, 'description');

            if (!Schema::hasColumn('inventory_movements', 'trace_status')) {
                $table->string('trace_status')->default('available')->after('tracking_notes');
            }
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_movements', 'batch_no')) {
                $table->index(['tenant_id', 'store_id', 'product_id', 'batch_no'], 'inventory_movements_batch_lookup_index');
            }
            if (Schema::hasColumn('inventory_movements', 'serial_no')) {
                $table->index(['tenant_id', 'product_id', 'serial_no'], 'inventory_movements_serial_lookup_index');
            }
            if (Schema::hasColumn('inventory_movements', 'expiry_date')) {
                $table->index(['tenant_id', 'product_id', 'expiry_date'], 'inventory_movements_expiry_lookup_index');
            }
        });
    }

    private function extendDepotRows(): void
    {
        if (!Schema::hasTable('depots')) {
            return;
        }

        Schema::table('depots', function (Blueprint $table) {
            $this->addTraceColumns($table, 'status');
        });
    }

    private function extendSalesRows(): void
    {
        if (!Schema::hasTable('pish_factor_items')) {
            return;
        }

        Schema::table('pish_factor_items', function (Blueprint $table) {
            $this->addTraceColumns($table, 'reserved_quantity');
        });
    }

    private function createTraceBalances(): void
    {
        if (Schema::hasTable('inventory_trace_balances')) {
            return;
        }

        Schema::create('inventory_trace_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->index();
            $table->unsignedBigInteger('warehouse_location_id')->default(0)->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('batch_no', 120)->nullable()->index();
            $table->string('lot_no', 120)->nullable()->index();
            $table->string('serial_no', 160)->nullable()->index();
            $table->date('manufactured_at')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->string('color', 80)->nullable();
            $table->string('size', 80)->nullable();
            $table->string('quality_grade', 80)->nullable();
            $table->decimal('weight', 18, 3)->nullable();
            $table->string('trace_status')->default('available')->index();
            $table->decimal('quantity', 18, 3)->default(0);
            $table->decimal('quantity_sub_unit', 18, 3)->default(0);
            $table->decimal('reserved_quantity', 18, 3)->default(0);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->dateTime('last_movement_at')->nullable();
            $table->text('tracking_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'store_id', 'warehouse_location_id', 'product_id'], 'inventory_trace_stock_lookup_index');
            $table->index(['tenant_id', 'product_id', 'batch_no', 'expiry_date'], 'inventory_trace_batch_expiry_index');
        });
    }

    private function addTraceColumns(Blueprint $table, string $afterColumn): void
    {
        $tableName = $table->getTable();

        if (!Schema::hasColumn($tableName, 'batch_no')) {
            $table->string('batch_no', 120)->nullable()->after($afterColumn);
        }
        if (!Schema::hasColumn($tableName, 'lot_no')) {
            $table->string('lot_no', 120)->nullable()->after('batch_no');
        }
        if (!Schema::hasColumn($tableName, 'serial_no')) {
            $table->string('serial_no', 160)->nullable()->after('lot_no');
        }
        if (!Schema::hasColumn($tableName, 'manufactured_at')) {
            $table->date('manufactured_at')->nullable()->after('serial_no');
        }
        if (!Schema::hasColumn($tableName, 'expiry_date')) {
            $table->date('expiry_date')->nullable()->after('manufactured_at');
        }
        if (!Schema::hasColumn($tableName, 'color')) {
            $table->string('color', 80)->nullable()->after('expiry_date');
        }
        if (!Schema::hasColumn($tableName, 'size')) {
            $table->string('size', 80)->nullable()->after('color');
        }
        if (!Schema::hasColumn($tableName, 'quality_grade')) {
            $table->string('quality_grade', 80)->nullable()->after('size');
        }
        if (!Schema::hasColumn($tableName, 'weight')) {
            $table->decimal('weight', 18, 3)->nullable()->after('quality_grade');
        }
        if (!Schema::hasColumn($tableName, 'tracking_notes')) {
            $table->text('tracking_notes')->nullable()->after('weight');
        }
    }
};

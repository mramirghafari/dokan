<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendPishfactors();
        $this->extendPishFactorItems();
        $this->createSalesWorkflowEvents();
        $this->createSalesInventoryReservations();
        $this->backfillSalesWorkflowState();
    }

    public function down()
    {
        // Non-destructive migration: keep sales workflow trace and reservations intact.
    }

    private function extendPishfactors(): void
    {
        if (!Schema::hasTable('pishfactors')) {
            return;
        }

        Schema::table('pishfactors', function (Blueprint $table) {
            if (!Schema::hasColumn('pishfactors', 'sales_document_type')) {
                $table->string('sales_document_type', 30)->default('sales_order')->after('invoiceID');
            }
            if (!Schema::hasColumn('pishfactors', 'sales_status')) {
                $table->string('sales_status', 30)->default('draft')->after('sales_document_type');
            }
            if (!Schema::hasColumn('pishfactors', 'approval_status')) {
                $table->string('approval_status', 30)->default('draft')->after('sales_status');
            }
            if (!Schema::hasColumn('pishfactors', 'approval_level')) {
                $table->unsignedTinyInteger('approval_level')->default(0)->after('approval_status');
            }
            if (!Schema::hasColumn('pishfactors', 'approval_requested_at')) {
                $table->timestamp('approval_requested_at')->nullable()->after('approval_level');
            }
            if (!Schema::hasColumn('pishfactors', 'approval_requested_by')) {
                $table->unsignedBigInteger('approval_requested_by')->nullable()->after('approval_requested_at');
            }
            if (!Schema::hasColumn('pishfactors', 'approval_reviewed_at')) {
                $table->timestamp('approval_reviewed_at')->nullable()->after('approval_requested_by');
            }
            if (!Schema::hasColumn('pishfactors', 'approval_reviewed_by')) {
                $table->unsignedBigInteger('approval_reviewed_by')->nullable()->after('approval_reviewed_at');
            }
            if (!Schema::hasColumn('pishfactors', 'approval_note')) {
                $table->text('approval_note')->nullable()->after('approval_reviewed_by');
            }
            if (!Schema::hasColumn('pishfactors', 'credit_status')) {
                $table->string('credit_status', 30)->default('not_checked')->after('approval_note');
            }
            if (!Schema::hasColumn('pishfactors', 'credit_limit_snapshot')) {
                $table->decimal('credit_limit_snapshot', 18, 2)->default(0)->after('credit_status');
            }
            if (!Schema::hasColumn('pishfactors', 'customer_balance_snapshot')) {
                $table->decimal('customer_balance_snapshot', 18, 2)->default(0)->after('credit_limit_snapshot');
            }
            if (!Schema::hasColumn('pishfactors', 'reserve_status')) {
                $table->string('reserve_status', 30)->default('not_reserved')->after('customer_balance_snapshot');
            }
            if (!Schema::hasColumn('pishfactors', 'reserved_at')) {
                $table->timestamp('reserved_at')->nullable()->after('reserve_status');
            }
            if (!Schema::hasColumn('pishfactors', 'warehouse_issue_status')) {
                $table->string('warehouse_issue_status', 30)->default('pending')->after('reserved_at');
            }
            if (!Schema::hasColumn('pishfactors', 'settlement_status')) {
                $table->string('settlement_status', 30)->default('unsettled')->after('warehouse_issue_status');
            }
            if (!Schema::hasColumn('pishfactors', 'delivery_status')) {
                $table->string('delivery_status', 30)->default('not_ready')->after('settlement_status');
            }
            if (!Schema::hasColumn('pishfactors', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            }
            if (!Schema::hasColumn('pishfactors', 'price_list_id')) {
                $table->unsignedBigInteger('price_list_id')->nullable()->after('delivered_at');
            }
        });

        $this->addIndexIfMissing('pishfactors', ['tenant_id', 'sales_status'], 'pishfactors_tenant_sales_status_index');
        $this->addIndexIfMissing('pishfactors', ['tenant_id', 'approval_status'], 'pishfactors_tenant_approval_status_index');
        $this->addIndexIfMissing('pishfactors', ['tenant_id', 'delivery_status'], 'pishfactors_tenant_delivery_status_index');
    }

    private function extendPishFactorItems(): void
    {
        if (!Schema::hasTable('pish_factor_items')) {
            return;
        }

        Schema::table('pish_factor_items', function (Blueprint $table) {
            if (!Schema::hasColumn('pish_factor_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('pr_id');
            }
            if (!Schema::hasColumn('pish_factor_items', 'warehouse_location_id')) {
                $table->unsignedBigInteger('warehouse_location_id')->default(0)->after('unit_id');
            }
            if (!Schema::hasColumn('pish_factor_items', 'tax_rate_id')) {
                $table->unsignedBigInteger('tax_rate_id')->nullable()->after('warehouse_location_id');
            }
            if (!Schema::hasColumn('pish_factor_items', 'discount_amount')) {
                $table->decimal('discount_amount', 18, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('pish_factor_items', 'tax_amount')) {
                $table->decimal('tax_amount', 18, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('pish_factor_items', 'line_total')) {
                $table->decimal('line_total', 18, 2)->default(0)->after('tax_amount');
            }
            if (!Schema::hasColumn('pish_factor_items', 'reserved_quantity')) {
                $table->decimal('reserved_quantity', 18, 3)->default(0)->after('line_total');
            }
        });
    }

    private function createSalesWorkflowEvents(): void
    {
        if (!Schema::hasTable('sales_workflow_events')) {
            Schema::create('sales_workflow_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedInteger('pishfactor_id')->index();
                $table->string('event_type', 40)->index();
                $table->string('from_status', 30)->nullable();
                $table->string('to_status', 30)->nullable();
                $table->decimal('order_amount', 18, 2)->default(0);
                $table->string('credit_status', 30)->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    private function createSalesInventoryReservations(): void
    {
        if (!Schema::hasTable('sales_inventory_reservations')) {
            Schema::create('sales_inventory_reservations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedInteger('pishfactor_id')->index();
                $table->unsignedInteger('pish_factor_item_id')->nullable()->index();
                $table->unsignedBigInteger('store_id')->default(0)->index();
                $table->unsignedBigInteger('warehouse_location_id')->default(0);
                $table->unsignedBigInteger('product_id')->index();
                $table->decimal('quantity', 18, 3)->default(0);
                $table->string('status', 30)->default('reserved')->index();
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'store_id', 'product_id', 'status'], 'sales_reservations_stock_status_index');
            });
        }
    }

    private function backfillSalesWorkflowState(): void
    {
        if (!Schema::hasTable('pishfactors') || !Schema::hasColumn('pishfactors', 'sales_status')) {
            return;
        }

        DB::table('pishfactors')->orderBy('id')->chunkById(200, function ($factors) {
            foreach ($factors as $factor) {
                $status = (int) $factor->status;
                $step = (int) $factor->step;
                $salesStatus = match (true) {
                    $status === 3 => 'rejected',
                    $status === 4 => 'delivered',
                    $status === 1 && $step >= 2 => 'warehouse_ready',
                    $status === 1 => 'approved',
                    default => 'draft',
                };

                DB::table('pishfactors')->where('id', $factor->id)->update([
                    'sales_status' => $salesStatus,
                    'approval_status' => in_array($salesStatus, ['approved', 'warehouse_ready', 'delivered'], true) ? 'approved' : ($salesStatus === 'rejected' ? 'rejected' : 'draft'),
                    'warehouse_issue_status' => $step >= 2 ? 'issued' : 'pending',
                    'delivery_status' => $step >= 4 || $status === 4 ? 'delivered' : ($step >= 2 ? 'ready' : 'not_ready'),
                    'settlement_status' => in_array((int) $factor->payment_type, [1, 2, 4], true) ? 'settled' : 'unsettled',
                ]);
            }
        });
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }
};

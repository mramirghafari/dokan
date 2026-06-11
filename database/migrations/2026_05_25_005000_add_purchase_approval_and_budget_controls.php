<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'approval_status')) {
                    $table->string('approval_status', 30)->default('draft')->after('status');
                }
                if (!Schema::hasColumn('purchase_orders', 'approval_level')) {
                    $table->unsignedTinyInteger('approval_level')->default(0)->after('approval_status');
                }
                if (!Schema::hasColumn('purchase_orders', 'approval_requested_at')) {
                    $table->timestamp('approval_requested_at')->nullable()->after('approval_level');
                }
                if (!Schema::hasColumn('purchase_orders', 'approval_requested_by')) {
                    $table->unsignedInteger('approval_requested_by')->nullable()->after('approval_requested_at');
                }
                if (!Schema::hasColumn('purchase_orders', 'approval_reviewed_at')) {
                    $table->timestamp('approval_reviewed_at')->nullable()->after('approval_requested_by');
                }
                if (!Schema::hasColumn('purchase_orders', 'approval_reviewed_by')) {
                    $table->unsignedInteger('approval_reviewed_by')->nullable()->after('approval_reviewed_at');
                }
                if (!Schema::hasColumn('purchase_orders', 'approval_note')) {
                    $table->text('approval_note')->nullable()->after('approval_reviewed_by');
                }
                if (!Schema::hasColumn('purchase_orders', 'budget_status')) {
                    $table->string('budget_status', 30)->default('not_checked')->after('approval_note');
                }
                if (!Schema::hasColumn('purchase_orders', 'budget_period')) {
                    $table->string('budget_period', 7)->nullable()->after('budget_status');
                }
                if (!Schema::hasColumn('purchase_orders', 'budget_amount')) {
                    $table->decimal('budget_amount', 18, 2)->nullable()->after('budget_period');
                }
                if (!Schema::hasColumn('purchase_orders', 'budget_consumed_amount')) {
                    $table->decimal('budget_consumed_amount', 18, 2)->default(0)->after('budget_amount');
                }
                if (!Schema::hasColumn('purchase_orders', 'budget_remaining_amount')) {
                    $table->decimal('budget_remaining_amount', 18, 2)->default(0)->after('budget_consumed_amount');
                }
            });

            DB::table('purchase_orders')
                ->whereNull('approval_status')
                ->orWhere('approval_status', '')
                ->update(['approval_status' => DB::raw("CASE WHEN status IN ('approved', 'received') THEN 'approved' ELSE 'draft' END")]);

            DB::table('purchase_orders')
                ->whereIn('status', ['approved', 'received'])
                ->where('approval_status', 'draft')
                ->update(['approval_status' => 'approved']);
        }

        if (!Schema::hasTable('purchase_budgets')) {
            Schema::create('purchase_budgets', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedInteger('store_id')->nullable();
                $table->string('period', 7);
                $table->decimal('budget_amount', 18, 2)->default(0);
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'store_id', 'period'], 'purchase_budgets_scope_index');
            });
        }

        if (!Schema::hasTable('purchase_approval_events')) {
            Schema::create('purchase_approval_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('purchase_order_id');
                $table->string('event_type', 40);
                $table->string('from_status', 30)->nullable();
                $table->string('to_status', 30)->nullable();
                $table->decimal('order_amount', 18, 2)->default(0);
                $table->decimal('budget_amount', 18, 2)->nullable();
                $table->decimal('budget_consumed_amount', 18, 2)->default(0);
                $table->string('budget_status', 30)->default('not_checked');
                $table->text('description')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['purchase_order_id'], 'purchase_approval_events_order_index');
                $table->index(['tenant_id', 'event_type'], 'purchase_approval_events_tenant_type_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep approval and budget trace data intact.
    }
};

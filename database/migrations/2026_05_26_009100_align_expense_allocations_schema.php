<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operational_expenses')) {
            Schema::table('operational_expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('operational_expenses', 'allocation_basis')) {
                    $table->string('allocation_basis', 40)->nullable()->index()->after('allocation_target_id');
                }

                if (!Schema::hasColumn('operational_expenses', 'allocation_note')) {
                    $table->text('allocation_note')->nullable()->after('workflow_note');
                }
            });
        }

        if (Schema::hasTable('expense_allocations')) {
            Schema::table('expense_allocations', function (Blueprint $table) {
                if (!Schema::hasColumn('expense_allocations', 'voucher_id')) {
                    $table->unsignedBigInteger('voucher_id')->nullable()->index()->after('operational_expense_id');
                }

                if (!Schema::hasColumn('expense_allocations', 'voucher_item_id')) {
                    $table->unsignedBigInteger('voucher_item_id')->nullable()->index()->after('voucher_id');
                }

                if (!Schema::hasColumn('expense_allocations', 'cost_center_id')) {
                    $table->unsignedBigInteger('cost_center_id')->nullable()->index()->after('voucher_item_id');
                }

                if (!Schema::hasColumn('expense_allocations', 'expense_type_id')) {
                    $table->unsignedBigInteger('expense_type_id')->nullable()->index()->after('cost_center_id');
                }

                if (!Schema::hasColumn('expense_allocations', 'allocation_target_type')) {
                    $table->string('allocation_target_type', 50)->nullable()->index()->after('allocation_basis');
                }

                if (!Schema::hasColumn('expense_allocations', 'allocation_target_id')) {
                    $table->unsignedBigInteger('allocation_target_id')->nullable()->index()->after('allocation_target_type');
                }

                if (!Schema::hasColumn('expense_allocations', 'target_type')) {
                    $table->string('target_type', 50)->nullable()->index()->after('allocation_target_id');
                }

                if (!Schema::hasColumn('expense_allocations', 'target_id')) {
                    $table->unsignedBigInteger('target_id')->nullable()->index()->after('target_type');
                }

                if (!Schema::hasColumn('expense_allocations', 'basis_quantity')) {
                    $table->decimal('basis_quantity', 18, 4)->nullable()->after('contract_code');
                }

                if (!Schema::hasColumn('expense_allocations', 'basis_value')) {
                    $table->decimal('basis_value', 18, 4)->nullable()->after('basis_quantity');
                }

                if (!Schema::hasColumn('expense_allocations', 'allocated_amount')) {
                    $table->decimal('allocated_amount', 18, 2)->default(0)->after('allocation_percent');
                }

                if (!Schema::hasColumn('expense_allocations', 'amount')) {
                    $table->decimal('amount', 18, 2)->default(0)->after('allocated_amount');
                }
            });

            if (Schema::hasColumn('expense_allocations', 'target_type') && Schema::hasColumn('expense_allocations', 'allocation_target_type')) {
                DB::table('expense_allocations')
                    ->whereNull('allocation_target_type')
                    ->update(['allocation_target_type' => DB::raw('target_type')]);
            }

            if (Schema::hasColumn('expense_allocations', 'target_id') && Schema::hasColumn('expense_allocations', 'allocation_target_id')) {
                DB::table('expense_allocations')
                    ->whereNull('allocation_target_id')
                    ->update(['allocation_target_id' => DB::raw('target_id')]);
            }

            if (Schema::hasColumn('expense_allocations', 'basis_value') && Schema::hasColumn('expense_allocations', 'basis_quantity')) {
                DB::table('expense_allocations')
                    ->whereNull('basis_quantity')
                    ->update(['basis_quantity' => DB::raw('basis_value')]);
            }

            if (Schema::hasColumn('expense_allocations', 'amount') && Schema::hasColumn('expense_allocations', 'allocated_amount')) {
                DB::table('expense_allocations')
                    ->where('allocated_amount', 0)
                    ->update(['allocated_amount' => DB::raw('amount')]);
            }
        }

        if (Schema::hasTable('voucher_items')) {
            Schema::table('voucher_items', function (Blueprint $table) {
                if (!Schema::hasColumn('voucher_items', 'expense_allocation_id')) {
                    $table->unsignedBigInteger('expense_allocation_id')->nullable()->index()->after('expense_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: keep allocation history and added trace columns.
    }
};

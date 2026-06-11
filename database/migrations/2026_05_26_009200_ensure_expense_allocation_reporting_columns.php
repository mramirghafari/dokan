<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expense_allocations')) {
            return;
        }

        Schema::table('expense_allocations', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_allocations', 'target_type')) {
                $table->string('target_type', 50)->nullable()->index()->after('allocation_target_id');
            }

            if (!Schema::hasColumn('expense_allocations', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable()->index()->after('target_type');
            }

            if (!Schema::hasColumn('expense_allocations', 'basis_value')) {
                $table->decimal('basis_value', 18, 4)->nullable()->after('basis_quantity');
            }

            if (!Schema::hasColumn('expense_allocations', 'amount')) {
                $table->decimal('amount', 18, 2)->default(0)->after('allocated_amount');
            }
        });

        if (Schema::hasColumn('expense_allocations', 'allocation_target_type') && Schema::hasColumn('expense_allocations', 'target_type')) {
            DB::table('expense_allocations')
                ->whereNull('target_type')
                ->update(['target_type' => DB::raw('allocation_target_type')]);
        }

        if (Schema::hasColumn('expense_allocations', 'allocation_target_id') && Schema::hasColumn('expense_allocations', 'target_id')) {
            DB::table('expense_allocations')
                ->whereNull('target_id')
                ->update(['target_id' => DB::raw('allocation_target_id')]);
        }

        if (Schema::hasColumn('expense_allocations', 'basis_quantity') && Schema::hasColumn('expense_allocations', 'basis_value')) {
            DB::table('expense_allocations')
                ->whereNull('basis_value')
                ->update(['basis_value' => DB::raw('basis_quantity')]);
        }

        if (Schema::hasColumn('expense_allocations', 'allocated_amount') && Schema::hasColumn('expense_allocations', 'amount')) {
            DB::table('expense_allocations')
                ->where('amount', 0)
                ->update(['amount' => DB::raw('allocated_amount')]);
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: keep report compatibility columns and allocation history.
    }
};

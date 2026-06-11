<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendVoucherItems('voucher_items');
        $this->extendVoucherItems('voucher_template_items');
    }

    private function extendVoucherItems(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->index()->after('project_code');
            }

            if (!Schema::hasColumn($tableName, 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->index()->after('product_id');
            }

            if (!Schema::hasColumn($tableName, 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->index()->after('customer_id');
            }

            if (!Schema::hasColumn($tableName, 'contract_code')) {
                $table->string('contract_code', 120)->nullable()->index()->after('employee_id');
            }

            if (!Schema::hasColumn($tableName, 'route_code')) {
                $table->string('route_code', 120)->nullable()->index()->after('contract_code');
            }

            if (!Schema::hasColumn($tableName, 'analytic_note')) {
                $table->string('analytic_note', 191)->nullable()->after('route_code');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive rollback: analytic trace is audit data and must remain available.
    }
};

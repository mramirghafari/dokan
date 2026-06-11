<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('voucher_templates')) {
            Schema::create('voucher_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('source_voucher_id')->nullable()->index();
                $table->string('name', 191);
                $table->string('frequency', 40)->default('on_demand')->index();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('voucher_template_items')) {
            Schema::create('voucher_template_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voucher_template_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('account_id')->nullable()->index();
                $table->unsignedBigInteger('cost_center_id')->nullable()->index();
                $table->unsignedBigInteger('expense_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('project_code', 100)->nullable()->index();
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('debit_amount', 18, 2)->default(0);
                $table->decimal('credit_amount', 18, 2)->default(0);
                $table->integer('method')->default(0);
                $table->unsignedBigInteger('payment_terminal_id')->nullable()->index();
                $table->string('issuing_bank', 191)->nullable();
                $table->date('due_date')->nullable();
                $table->string('cheque_photo', 191)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: operators may already depend on saved templates.
    }
};

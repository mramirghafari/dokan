<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('treasury_cheque_books')) {
            Schema::create('treasury_cheque_books', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('account_id')->index();
                $table->string('book_number', 80)->nullable()->index();
                $table->string('cheque_prefix', 40)->nullable();
                $table->unsignedBigInteger('first_leaf_number');
                $table->unsignedBigInteger('last_leaf_number');
                $table->unsignedBigInteger('next_leaf_number')->nullable()->index();
                $table->unsignedInteger('leaf_count')->default(0);
                $table->unsignedInteger('warning_threshold')->default(5);
                $table->string('bank_name')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('status', 30)->default('active')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'account_id', 'status'], 'treasury_cheque_books_scope_index');
            });
        }

        if (!Schema::hasTable('treasury_cheque_leaves')) {
            Schema::create('treasury_cheque_leaves', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('treasury_cheque_book_id')->index();
                $table->unsignedBigInteger('account_id')->index();
                $table->unsignedBigInteger('treasury_instrument_id')->nullable()->index();
                $table->unsignedBigInteger('payee_account_id')->nullable()->index();
                $table->string('leaf_number', 100)->index();
                $table->string('status', 30)->default('available')->index();
                $table->date('issued_date')->nullable()->index();
                $table->date('due_date')->nullable()->index();
                $table->decimal('amount', 18, 2)->default(0);
                $table->string('payee_name')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['treasury_cheque_book_id', 'status'], 'treasury_cheque_leaves_book_status_index');
                $table->index(['tenant_id', 'status', 'due_date'], 'treasury_cheque_leaves_alert_index');
            });
        }

        if (Schema::hasTable('treasury_instruments')) {
            Schema::table('treasury_instruments', function (Blueprint $table) {
                if (!Schema::hasColumn('treasury_instruments', 'treasury_cheque_book_id')) {
                    $table->unsignedBigInteger('treasury_cheque_book_id')->nullable()->index()->after('voucher_item_id');
                }

                if (!Schema::hasColumn('treasury_instruments', 'treasury_cheque_leaf_id')) {
                    $table->unsignedBigInteger('treasury_cheque_leaf_id')->nullable()->index()->after('treasury_cheque_book_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep cheque book, leaf and issued cheque history intact.
    }
};

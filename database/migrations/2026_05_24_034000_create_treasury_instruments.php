<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('treasury_instruments')) {
            Schema::create('treasury_instruments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('voucher_id')->index();
                $table->unsignedBigInteger('voucher_item_id')->nullable()->index();
                $table->unsignedBigInteger('counter_account_id')->nullable()->index();
                $table->string('instrument_type', 30)->default('cheque')->index();
                $table->string('direction', 20)->index();
                $table->string('status', 30)->index();
                $table->decimal('amount', 18, 2)->default(0);
                $table->string('issuing_bank')->nullable();
                $table->string('cheque_number', 100)->nullable()->index();
                $table->date('due_date')->nullable()->index();
                $table->date('status_date')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'instrument_type', 'status'], 'treasury_instruments_scope_status_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep cheque and treasury lifecycle history intact.
    }
};

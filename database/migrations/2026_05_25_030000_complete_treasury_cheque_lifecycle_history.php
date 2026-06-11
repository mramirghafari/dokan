<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('treasury_instruments')) {
            Schema::table('treasury_instruments', function (Blueprint $table) {
                if (!Schema::hasColumn('treasury_instruments', 'current_holder_account_id')) {
                    $table->unsignedBigInteger('current_holder_account_id')->nullable()->index()->after('counter_account_id');
                }

                if (!Schema::hasColumn('treasury_instruments', 'current_holder_name')) {
                    $table->string('current_holder_name')->nullable()->after('status');
                }

                if (!Schema::hasColumn('treasury_instruments', 'last_status_note')) {
                    $table->text('last_status_note')->nullable()->after('status_date');
                }

                if (!Schema::hasColumn('treasury_instruments', 'last_status_changed_at')) {
                    $table->timestamp('last_status_changed_at')->nullable()->after('last_status_note');
                }
            });
        }

        if (!Schema::hasTable('treasury_instrument_histories')) {
            Schema::create('treasury_instrument_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('treasury_instrument_id')->index();
                $table->string('previous_status', 30)->nullable();
                $table->string('new_status', 30)->index();
                $table->date('action_date')->nullable()->index();
                $table->decimal('amount', 18, 2)->default(0);
                $table->unsignedBigInteger('settlement_account_id')->nullable()->index();
                $table->unsignedBigInteger('holder_account_id')->nullable()->index();
                $table->string('holder_name')->nullable();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['treasury_instrument_id', 'new_status'], 'treasury_history_instrument_status_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: preserve cheque lifecycle and holder history.
    }
};

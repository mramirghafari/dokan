<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_portal_payments')) {
            Schema::create('customer_portal_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('customer_portal_account_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('pishfactor_id')->nullable();
                $table->unsignedBigInteger('customer_portal_request_id')->nullable();
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('payable_amount', 18, 2)->default(0);
                $table->string('status', 40)->default('initiated');
                $table->string('payment_method', 60)->default('bank_transfer');
                $table->string('gateway_provider', 80)->nullable();
                $table->string('authority', 160)->nullable();
                $table->string('reference_number', 160)->nullable();
                $table->text('proof_text')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'status'], 'customer_portal_payments_scope_status_idx');
                $table->index(['customer_id', 'status', 'requested_at'], 'customer_portal_payments_customer_status_idx');
                $table->index(['customer_portal_account_id', 'status'], 'customer_portal_payments_account_status_idx');
                $table->index(['pishfactor_id', 'status'], 'customer_portal_payments_order_status_idx');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: portal payment records are operational audit data.
    }
};

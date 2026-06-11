<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_list_scope_summaries')) {
            return;
        }

        Schema::create('customer_list_scope_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tenant_id')->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->string('scope_type', 40);
            $table->string('scope_key', 120);
            $table->unsignedBigInteger('total_customers')->default(0);
            $table->unsignedBigInteger('customers_with_purchase')->default(0);
            $table->unsignedBigInteger('restricted_customers')->default(0);
            $table->unsignedBigInteger('banned_customers')->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'organization_id', 'scope_type', 'scope_key'], 'customer_list_scope_unique');
            $table->index(['scope_type', 'computed_at'], 'customer_list_scope_type_computed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_list_scope_summaries');
    }
};

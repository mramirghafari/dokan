<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ecommerce_channels')) {
            Schema::create('ecommerce_channels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('code', 80)->index();
                $table->string('title', 191);
                $table->string('platform', 60)->default('custom')->index();
                $table->string('base_url')->nullable();
                $table->string('api_token_hash', 191)->nullable();
                $table->string('price_policy', 40)->default('consumer')->index();
                $table->unsignedBigInteger('default_store_id')->nullable()->index();
                $table->unsignedBigInteger('default_visitor_id')->nullable()->index();
                $table->unsignedBigInteger('default_leader_id')->nullable()->index();
                $table->unsignedTinyInteger('default_payment_method')->default(3);
                $table->string('order_status_policy', 40)->default('draft')->index();
                $table->boolean('auto_create_customer')->default(true);
                $table->boolean('auto_reserve_inventory')->default(false);
                $table->boolean('is_active')->default(true)->index();
                $table->json('settings_json')->nullable();
                $table->timestamp('last_sync_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'code'], 'ecommerce_channels_tenant_code_unique');
            });
        }

        if (!Schema::hasTable('ecommerce_product_mappings')) {
            Schema::create('ecommerce_product_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ecommerce_channel_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('external_product_id', 120)->index();
                $table->string('external_variant_id', 120)->nullable()->index();
                $table->string('external_sku', 120)->nullable()->index();
                $table->string('sync_direction', 30)->default('both')->index();
                $table->decimal('price_override', 18, 2)->nullable();
                $table->decimal('stock_buffer', 18, 4)->default(0);
                $table->boolean('sync_price')->default(true);
                $table->boolean('sync_stock')->default(true);
                $table->boolean('is_active')->default(true)->index();
                $table->json('last_export_payload')->nullable();
                $table->json('last_import_payload')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['ecommerce_channel_id', 'external_product_id', 'external_variant_id'], 'ecommerce_product_external_unique');
                $table->unique(['ecommerce_channel_id', 'product_id'], 'ecommerce_product_local_unique');
            });
        }

        if (!Schema::hasTable('ecommerce_order_mappings')) {
            Schema::create('ecommerce_order_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ecommerce_channel_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedInteger('pishfactor_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->string('external_order_id', 120)->index();
                $table->string('external_order_number', 120)->nullable()->index();
                $table->string('external_customer_id', 120)->nullable()->index();
                $table->string('order_status', 40)->default('received')->index();
                $table->string('payment_status', 40)->default('unknown')->index();
                $table->string('delivery_status', 40)->default('pending')->index();
                $table->decimal('gross_amount', 18, 2)->default(0);
                $table->decimal('discount_amount', 18, 2)->default(0);
                $table->decimal('shipping_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('net_amount', 18, 2)->default(0);
                $table->json('payload_json')->nullable();
                $table->json('response_json')->nullable();
                $table->string('sync_status', 30)->default('processed')->index();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->string('conflict_reason', 500)->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['ecommerce_channel_id', 'external_order_id'], 'ecommerce_order_external_unique');
            });
        }

        if (!Schema::hasTable('ecommerce_sync_logs')) {
            Schema::create('ecommerce_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ecommerce_channel_id')->nullable()->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('direction', 20)->default('inbound')->index();
                $table->string('entity_type', 60)->index();
                $table->string('entity_key', 120)->nullable()->index();
                $table->unsignedBigInteger('entity_id')->nullable()->index();
                $table->string('action', 40)->default('sync')->index();
                $table->string('status', 30)->default('pending')->index();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->string('message', 500)->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep ecommerce sync trace, mappings and imported orders intact.
    }
};

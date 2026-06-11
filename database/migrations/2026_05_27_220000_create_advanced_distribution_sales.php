<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distribution_visit_plans')) {
            Schema::create('distribution_visit_plans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('visitor_id')->index();
                $table->string('plan_number', 80)->nullable()->index();
                $table->string('title', 191)->nullable();
                $table->string('route_code', 80)->nullable()->index();
                $table->unsignedBigInteger('area_id')->nullable()->index();
                $table->unsignedBigInteger('region_id')->nullable()->index();
                $table->date('planned_date_en')->nullable()->index();
                $table->string('planned_date_fa', 20)->nullable();
                $table->string('sales_mode', 30)->default('hot_sale')->index();
                $table->string('status', 30)->default('planned')->index();
                $table->unsignedInteger('planned_customers_count')->default(0);
                $table->unsignedInteger('visited_count')->default(0);
                $table->unsignedInteger('ordered_count')->default(0);
                $table->unsignedInteger('no_order_count')->default(0);
                $table->decimal('collected_amount', 18, 2)->default(0);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'visitor_id', 'planned_date_en'], 'distribution_visit_plan_scope_index');
            });
        }

        if (!Schema::hasTable('distribution_visit_stops')) {
            Schema::create('distribution_visit_stops', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('distribution_visit_plan_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedInteger('pishfactor_id')->nullable()->index();
                $table->unsignedInteger('sequence')->default(1);
                $table->string('visit_status', 30)->default('planned')->index();
                $table->timestamp('planned_at')->nullable();
                $table->timestamp('checked_in_at')->nullable();
                $table->timestamp('checked_out_at')->nullable();
                $table->decimal('collection_amount', 18, 2)->default(0);
                $table->string('no_order_reason', 500)->nullable();
                $table->decimal('lat', 12, 8)->nullable();
                $table->decimal('lng', 12, 8)->nullable();
                $table->string('signature_path')->nullable();
                $table->string('client_uid', 120)->nullable()->index();
                $table->json('extra_json')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['distribution_visit_plan_id', 'customer_id'], 'distribution_visit_plan_customer_unique');
            });
        }

        if (!Schema::hasTable('distribution_promotions')) {
            Schema::create('distribution_promotions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('code', 80)->index();
                $table->string('title', 191);
                $table->string('promotion_type', 40)->default('discount_percent')->index();
                $table->date('starts_at')->nullable()->index();
                $table->date('ends_at')->nullable()->index();
                $table->unsignedBigInteger('customer_segment_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->decimal('min_order_amount', 18, 2)->default(0);
                $table->decimal('discount_percent', 8, 4)->default(0);
                $table->decimal('discount_amount', 18, 2)->default(0);
                $table->unsignedBigInteger('gift_product_id')->nullable()->index();
                $table->decimal('gift_quantity', 18, 3)->default(0);
                $table->unsignedInteger('max_uses')->default(0);
                $table->unsignedInteger('used_count')->default(0);
                $table->string('status', 30)->default('active')->index();
                $table->json('rules_json')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'status', 'starts_at', 'ends_at'], 'distribution_promotions_active_index');
            });
        }

        if (!Schema::hasTable('distribution_mobile_orders')) {
            Schema::create('distribution_mobile_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedInteger('pishfactor_id')->index();
                $table->unsignedBigInteger('distribution_visit_plan_id')->nullable()->index();
                $table->unsignedBigInteger('distribution_visit_stop_id')->nullable()->index();
                $table->unsignedBigInteger('visitor_id')->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->string('client_order_uid', 120)->nullable()->index();
                $table->string('order_type', 30)->default('hot_sale')->index();
                $table->string('sale_mode', 30)->default('field')->index();
                $table->unsignedTinyInteger('payment_method')->default(3);
                $table->decimal('gross_amount', 18, 2)->default(0);
                $table->decimal('discount_amount', 18, 2)->default(0);
                $table->decimal('tax_amount', 18, 2)->default(0);
                $table->decimal('net_amount', 18, 2)->default(0);
                $table->decimal('location_lat', 12, 8)->nullable();
                $table->decimal('location_lng', 12, 8)->nullable();
                $table->json('offline_payload_json')->nullable();
                $table->timestamp('offline_created_at')->nullable();
                $table->string('sync_status', 30)->default('synced')->index();
                $table->timestamp('synced_at')->nullable();
                $table->string('conflict_reason', 500)->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'client_order_uid'], 'distribution_mobile_order_client_unique');
            });
        }

        if (!Schema::hasTable('distribution_sync_queues')) {
            Schema::create('distribution_sync_queues', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('client_uid', 120)->index();
                $table->string('entity_type', 60)->index();
                $table->unsignedBigInteger('entity_id')->nullable()->index();
                $table->string('action', 40)->default('upsert')->index();
                $table->json('payload_json')->nullable();
                $table->string('status', 30)->default('pending')->index();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->string('conflict_reason', 500)->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['user_id', 'client_uid'], 'distribution_sync_user_client_unique');
            });
        }

        if (Schema::hasTable('pishfactors')) {
            Schema::table('pishfactors', function (Blueprint $table) {
                if (!Schema::hasColumn('pishfactors', 'mobile_order_uid')) {
                    $table->string('mobile_order_uid', 120)->nullable()->after('invoiceID')->index();
                }

                if (!Schema::hasColumn('pishfactors', 'distribution_order_type')) {
                    $table->string('distribution_order_type', 30)->default('hot_sale')->after('mobile_order_uid')->index();
                }

                if (!Schema::hasColumn('pishfactors', 'sale_mode')) {
                    $table->string('sale_mode', 30)->default('field')->after('distribution_order_type')->index();
                }

                if (!Schema::hasColumn('pishfactors', 'visit_stop_id')) {
                    $table->unsignedBigInteger('visit_stop_id')->nullable()->after('sale_mode')->index();
                }

                if (!Schema::hasColumn('pishfactors', 'promotion_discount_amount')) {
                    $table->decimal('promotion_discount_amount', 18, 2)->default(0)->after('visit_stop_id');
                }

                if (!Schema::hasColumn('pishfactors', 'offline_created_at')) {
                    $table->timestamp('offline_created_at')->nullable()->after('promotion_discount_amount');
                }

                if (!Schema::hasColumn('pishfactors', 'sync_status')) {
                    $table->string('sync_status', 30)->default('synced')->after('offline_created_at')->index();
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep advanced distribution, mobile order and sync trace data intact.
    }
};

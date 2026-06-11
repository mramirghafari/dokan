<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->boolean('customer_group_status')->default(false);
                $table->boolean('status')->default(true);
                $this->legacyTimestamps($table, true);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->nullable();
                $table->string('name')->nullable();
                $table->boolean('level')->nullable();
                $table->string('type')->nullable();
                $table->boolean('nature');
                $table->string('account_number')->nullable();
                $table->string('card_number')->nullable();
                $table->string('iban')->nullable();
                $table->string('branch')->nullable();
                $table->string('currency_type')->nullable();
                $table->boolean('isActive')->default(true);
                $table->integer('parent_id')->nullable();
                $table->string('organization_id')->nullable();
                $table->integer('tenants_id');
                $table->integer('created_by');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('organization_id');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 300);
                $table->string('northEast_lat', 50)->nullable();
                $table->string('northEast_lang', 50)->nullable();
                $table->string('southWest_lat', 50)->nullable();
                $table->string('southWest_lang', 50)->nullable();
                $table->integer('city_id')->nullable();
                $table->string('leader_id', 25)->nullable();
                $table->integer('organization_id')->nullable();
                $table->integer('store_id')->nullable();
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 300);
                $table->integer('region_id');
                $table->integer('leader_id')->nullable();
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 350);
                $table->string('national_id', 12);
                $table->string('economic_number', 15)->nullable();
                $table->string('phone', 12);
                $table->string('mobile', 12)->nullable();
                $table->string('tablo', 500)->nullable();
                $table->string('senf', 500)->nullable();
                $table->string('channel', 500)->nullable();
                $table->string('customer_code', 20)->nullable();
                $table->boolean('status')->default(true);
                $table->integer('region_id');
                $table->integer('area');
                $table->string('mapcode', 15)->nullable();
                $table->text('address');
                $table->string('store_address', 500)->nullable();
                $table->string('shop_lat', 50)->nullable();
                $table->string('shop_lng', 50)->nullable();
                $table->string('store_lat', 50)->nullable();
                $table->string('store_lng', 50)->nullable();
                $table->integer('organization_id')->nullable();
                $table->integer('leader_id')->nullable();
                $table->integer('created_by');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->boolean('type');
                $table->integer('store_id');
                $table->integer('to_store_id')->nullable();
                $table->integer('number')->nullable();
                $table->string('date_fa', 25)->nullable();
                $table->dateTime('date_en')->nullable();
                $table->string('sender', 500)->nullable();
                $table->string('moeen', 500)->nullable();
                $table->string('driver', 500)->nullable();
                $table->text('tozihat')->nullable();
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('depots')) {
            Schema::create('depots', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('pr_id');
                $table->integer('receipt_id');
                $table->integer('entity');
                $table->string('entity_sub_unit', 10)->default('0');
                $table->integer('orderLimit')->nullable();
                $table->string('price', 10)->nullable();
                $table->integer('discount')->nullable()->default(0);
                $table->integer('tax')->default(0);
                $table->string('fee_masraf', 10)->nullable();
                $table->tinyInteger('status')->default(0);
                $table->integer('store_id');
                $table->integer('brand_id')->nullable();
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('pishfactors')) {
            Schema::create('pishfactors', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->integer('visitor_id');
                $table->integer('sarparast_id');
                $table->integer('updated_by')->nullable();
                $table->integer('driver_id')->nullable();
                $table->boolean('payment_type')->nullable();
                $table->boolean('check_status')->nullable();
                $table->string('recive_date', 30)->nullable();
                $table->dateTime('recive_date_en')->nullable();
                $table->integer('invoiceID');
                $table->boolean('status')->default(false);
                $table->boolean('step')->default(false);
                $table->string('pat_price', 15)->nullable();
                $table->string('fullPrice', 20)->nullable();
                $table->text('tozihat')->nullable();
                $table->integer('organization_id')->nullable();
                $table->integer('tenants_id')->nullable();
                $table->integer('task_id')->nullable();
                $table->integer('area_id')->default(0);
                $table->integer('region_id')->default(0);
                $table->integer('city_id')->nullable();
                $table->integer('shipment_id')->nullable();
                $table->string('create_lat', 20)->nullable();
                $table->string('create_lng', 25)->nullable();
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('pish_factor_items')) {
            Schema::create('pish_factor_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('pishfactor_id');
                $table->integer('pr_id');
                $table->string('pack', 20);
                $table->string('tedad', 20);
                $table->string('price', 10)->nullable();
                $table->integer('discount')->nullable();
            });
        }

        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('factor_id');
                $table->integer('account_id')->nullable();
                $table->boolean('voucher_type');
                $table->string('voucher_number');
                $table->string('voucher_date_fa')->nullable();
                $table->dateTime('voucher_date_en')->nullable();
                $table->string('amount')->nullable();
                $table->tinyInteger('method');
                $table->text('description');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('voucher_items')) {
            Schema::create('voucher_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('voucher_id');
                $table->string('amount')->nullable();
                $table->integer('account_id')->nullable();
                $table->boolean('method');
                $table->integer('payment_terminal_id')->nullable();
                $table->string('issuing_bank')->nullable();
                $table->string('due_date')->nullable();
                $table->string('cheque_photo')->nullable();
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('payment_terminals')) {
            Schema::create('payment_terminals', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('account_id');
                $table->boolean('terminal_type');
                $table->string('provider_name');
                $table->string('terminal_number');
                $table->string('title');
                $table->string('description', 550)->nullable();
                $table->tinyInteger('type')->nullable();
                $table->boolean('isActive')->default(true);
                $table->integer('tenants_id')->nullable();
                $table->string('organization_id')->nullable();
                $table->integer('created_by');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('targets')) {
            Schema::create('targets', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('leader_id');
                $table->string('target_price', 15);
                $table->integer('orders_count')->nullable();
                $table->string('min_order_price', 15)->nullable();
                $table->string('start_date_fa', 20)->nullable();
                $table->dateTime('start_date_en')->nullable();
                $table->string('end_date_fa', 20)->nullable();
                $table->dateTime('end_date_en')->nullable();
                $table->boolean('status')->default(true);
                $table->string('organization_id');
                $this->legacyTimestamps($table);
                $table->integer('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('target_products')) {
            Schema::create('target_products', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('target_id');
                $table->integer('pr_id');
                $table->integer('order_count')->nullable();
                $table->string('order_price', 15)->nullable();
                $table->boolean('status');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('leader_id');
                $table->integer('user_id');
                $table->integer('area_id');
                $table->string('date', 25)->nullable();
                $table->string('start_date', 50)->nullable();
                $table->string('end_date', 50)->nullable();
                $table->string('senf', 300)->nullable();
                $table->string('channel', 300)->nullable();
                $table->integer('min_sale_item')->nullable();
                $table->integer('min_sale_price')->nullable();
                $table->integer('min_sale_item_price')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('organization_id')->default(0);
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('cargos')) {
            Schema::create('cargos', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('driver_id');
                $table->integer('cartons')->default(0);
                $table->string('weight', 5)->default('0');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->increments('id');
                $table->string('organization_id');
                $table->integer('owner_id');
                $table->integer('driver_id');
                $table->string('number', 500);
                $table->text('tozihat')->nullable();
                $table->string('date_fa', 15);
                $table->dateTime('date_en');
                $table->string('hours', 10)->nullable();
                $table->integer('mabda');
                $table->string('origin_lat', 55)->nullable();
                $table->string('origin_lang', 55)->nullable();
                $table->boolean('status')->default(false);
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('shipment_routes')) {
            Schema::create('shipment_routes', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('shipment_id');
                $table->string('factor_id', 10)->nullable();
                $table->integer('route_index');
                $table->string('origin_lat', 55);
                $table->string('origin_lng', 55);
                $table->string('destination_lat', 55);
                $table->string('destination_lng', 55);
                $table->boolean('status');
                $table->string('extra_info', 1500);
                $this->legacyTimestamps($table);
            });
        }

        if (!Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('unit')->nullable();
                $table->string('sub_unit')->nullable();
                $table->string('price', 15)->nullable();
                $table->string('entity', 10)->nullable();
                $table->integer('entity_sub_unit')->nullable();
                $table->string('pack_items', 10)->nullable();
                $table->string('pack_weight', 10)->nullable();
                $table->string('pack_weight_unit')->nullable();
                $table->integer('material_store_id');
                $table->integer('organization_id');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('material_stores')) {
            Schema::create('material_stores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->boolean('isActive')->default(true);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('material_store_role')) {
            Schema::create('material_store_role', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('material_store_id');
                $table->primary(['role_id', 'material_store_id']);
            });
        }

        if (!Schema::hasTable('region_role')) {
            Schema::create('region_role', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('region_id');
                $table->primary(['role_id', 'region_id']);
            });
        }

        if (!Schema::hasTable('factor_logs')) {
            Schema::create('factor_logs', function (Blueprint $table) {
                $table->id();
                $table->string('ip');
                $table->unsignedBigInteger('user_id')->index();
                $table->enum('action', ['create', 'update', 'delete', 'restore', 'forceDelete', 'login', 'logout']);
                $table->text('description');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('factor_makers')) {
            Schema::create('factor_makers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->boolean('type')->nullable();
                $table->boolean('pr_type')->nullable();
                $table->boolean('currency_type')->nullable();
                $table->string('seller_name')->nullable();
                $table->string('seller_economic_number', 20)->nullable();
                $table->string('seller_registration_number', 20)->nullable();
                $table->string('seller_id_number', 20)->nullable();
                $table->string('seller_address', 500)->nullable();
                $table->integer('seller_zip_code')->nullable();
                $table->string('seller_phone', 20)->nullable();
                $table->string('seller_fax', 20)->nullable();
                $table->boolean('buyer_name')->nullable();
                $table->boolean('buyer_econimic_code')->nullable();
                $table->boolean('buyer_registration_number')->nullable();
                $table->boolean('buyer_address')->nullable();
                $table->boolean('buyer_zip_code')->nullable();
                $table->boolean('buyer_phone')->nullable();
                $table->boolean('buyer_region_area')->nullable();
                $table->boolean('buyer_map_code')->nullable();
                $table->boolean('visitor_display')->nullable();
                $table->boolean('visitor_mobile')->nullable();
                $table->boolean('column_pr_code')->nullable();
                $table->boolean('column_moadian')->nullable();
                $table->boolean('column_sub_unit')->nullable();
                $table->boolean('column_discount')->nullable();
                $table->boolean('column_tax')->nullable();
                $table->string('organization_id', 55);
                $table->string('store_id')->nullable();
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('notifs')) {
            Schema::create('notifs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('title', 100);
                $table->text('content');
                $table->boolean('status')->default(false);
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('price_logs')) {
            Schema::create('price_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('pr_id');
                $table->string('price', 12);
                $table->string('discount', 25)->nullable();
                $table->string('tax', 25)->nullable();
                $table->string('fee_masraf', 25)->nullable();
                $table->string('price_from_fa', 12)->nullable();
                $table->string('price_exp_fa', 12)->nullable();
                $table->dateTime('price_from_en')->nullable();
                $table->dateTime('price_exp_en')->nullable();
                $table->integer('user_id');
                $this->legacyTimestamps($table);
                $table->dateTime('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('sms_verifications')) {
            Schema::create('sms_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('contact_number', 191);
                $table->string('code', 191);
                $table->unsignedBigInteger('user_id')->index();
                $table->string('status', 191)->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Intentionally left blank to avoid dropping legacy production data on rollback.
    }

    private function legacyTimestamps(Blueprint $table, bool $nullableUpdated = false): void
    {
        $table->dateTime('created_at')->useCurrent();

        $updatedAt = $table->dateTime('updated_at')->useCurrent();
        if ($nullableUpdated) {
            $updatedAt->nullable();
        }
    }
};

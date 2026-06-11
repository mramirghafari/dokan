<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('organizations')) {
            DB::statement('ALTER TABLE organizations MODIFY description VARCHAR(255) NULL');

            Schema::table('organizations', function (Blueprint $table) {
                if (!Schema::hasColumn('organizations', 'type')) {
                    $table->boolean('type')->default(false)->after('description');
                }
                if (!Schema::hasColumn('organizations', 'unit_order')) {
                    $table->string('unit_order', 55)->nullable()->after('type');
                }
                if (!Schema::hasColumn('organizations', 'sub_unit')) {
                    $table->string('sub_unit', 55)->nullable()->after('unit_order');
                }
                if (!Schema::hasColumn('organizations', 'pr_type')) {
                    $table->boolean('pr_type')->nullable()->after('sub_unit');
                }
                if (!Schema::hasColumn('organizations', 'currency_type')) {
                    $table->boolean('currency_type')->default(false)->after('pr_type');
                }
                if (!Schema::hasColumn('organizations', 'unit_display')) {
                    $table->boolean('unit_display')->default(true)->after('currency_type');
                }
                if (!Schema::hasColumn('organizations', 'tenants_id')) {
                    $table->integer('tenants_id')->nullable()->after('isActive');
                }
                if (!Schema::hasColumn('organizations', 'customer_group_status')) {
                    $table->boolean('customer_group_status')->default(false)->after('tenants_id');
                }
                if (!Schema::hasColumn('organizations', 'deleted_at')) {
                    $table->dateTime('deleted_at')->nullable()->after('updated_at');
                }
            });
        }

        if (Schema::hasTable('users')) {
            $this->dropForeignIfExists('users', 'users_organization_id_foreign');
            DB::statement('ALTER TABLE users MODIFY organization_id VARCHAR(30) NULL');

            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'tenants_id')) {
                    $table->integer('tenants_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('users', 'username')) {
                    $table->string('username')->nullable()->after('name');
                }
                if (!Schema::hasColumn('users', 'isGod')) {
                    $table->tinyInteger('isGod')->default(0)->after('isAdmin');
                }
                if (!Schema::hasColumn('users', 'leader_id')) {
                    $table->integer('leader_id')->nullable()->after('isGod');
                }
                if (!Schema::hasColumn('users', 'stores')) {
                    $table->text('stores')->nullable()->after('leader_id');
                }
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (!Schema::hasColumn('brands', 'organization_id')) {
                    $table->string('organization_id', 55)->nullable()->after('isActive');
                }
                if (!Schema::hasColumn('brands', 'store_id')) {
                    $table->integer('store_id')->nullable()->after('organization_id');
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'organization_id')) {
                    $table->integer('organization_id')->nullable()->after('isActive');
                }
                if (!Schema::hasColumn('categories', 'store_id')) {
                    $table->integer('store_id')->nullable()->after('organization_id');
                }
            });
        }

        if (Schema::hasTable('stores')) {
            $this->dropForeignIfExists('stores', 'stores_organization_id_foreign');
            DB::statement('ALTER TABLE stores MODIFY organization_id VARCHAR(255) NULL');

            Schema::table('stores', function (Blueprint $table) {
                if (!Schema::hasColumn('stores', 'code')) {
                    $table->integer('code')->nullable()->after('description');
                }
                if (!Schema::hasColumn('stores', 'prs_rel')) {
                    $table->text('prs_rel')->nullable()->after('code');
                }
                if (!Schema::hasColumn('stores', 'tenants_id')) {
                    $table->integer('tenants_id')->nullable()->after('isActive');
                }
                if (!Schema::hasColumn('stores', 'lat')) {
                    $table->string('lat', 25)->nullable()->after('tenants_id');
                }
                if (!Schema::hasColumn('stores', 'lang')) {
                    $table->string('lang', 25)->nullable()->after('lat');
                }
            });
        }

        if (Schema::hasTable('products')) {
            $this->dropForeignIfExists('products', 'products_brand_id_foreign');
            $this->dropForeignIfExists('products', 'products_organization_id_foreign');
            $this->dropForeignIfExists('products', 'products_store_id_foreign');
            DB::statement('ALTER TABLE products MODIFY brand_id INT UNSIGNED NULL');
            DB::statement('ALTER TABLE products MODIFY organization_id VARCHAR(255) NULL');
            DB::statement('ALTER TABLE products MODIFY store_id VARCHAR(255) NULL');

            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'sku')) {
                    $table->string('sku', 15)->nullable()->after('store_id');
                }
                if (!Schema::hasColumn('products', 'display_name')) {
                    $table->string('display_name', 500)->nullable()->after('title');
                }
                if (!Schema::hasColumn('products', 'pr_unit')) {
                    $table->string('pr_unit', 110)->nullable()->after('orderLimit');
                }
                if (!Schema::hasColumn('products', 'pr_sub_unit')) {
                    $table->string('pr_sub_unit', 110)->nullable()->after('pr_unit');
                }
                if (!Schema::hasColumn('products', 'pack_items')) {
                    $table->integer('pack_items')->nullable()->after('pr_sub_unit');
                }
                if (!Schema::hasColumn('products', 'pr_weight')) {
                    $table->string('pr_weight', 10)->nullable()->after('pack_items');
                }
                if (!Schema::hasColumn('products', 'pr_weight_unit')) {
                    $table->string('pr_weight_unit', 55)->nullable()->after('pr_weight');
                }
                if (!Schema::hasColumn('products', 'pack_weight')) {
                    $table->string('pack_weight', 10)->nullable()->after('pr_weight_unit');
                }
                if (!Schema::hasColumn('products', 'pack_weight_unit')) {
                    $table->string('pack_weight_unit', 55)->nullable()->after('pack_weight');
                }
                if (!Schema::hasColumn('products', 'start_date')) {
                    $table->string('start_date', 12)->nullable()->after('pack_weight_unit');
                }
                if (!Schema::hasColumn('products', 'exp_date')) {
                    $table->string('exp_date', 12)->nullable()->after('start_date');
                }
                if (!Schema::hasColumn('products', 'price')) {
                    $table->string('price', 10)->nullable()->after('exp_date');
                }
                if (!Schema::hasColumn('products', 'discount')) {
                    $table->tinyInteger('discount')->nullable()->default(0)->after('price');
                }
                if (!Schema::hasColumn('products', 'tax')) {
                    $table->integer('tax')->nullable()->default(0)->after('discount');
                }
                if (!Schema::hasColumn('products', 'fee_masraf')) {
                    $table->string('fee_masraf', 10)->nullable()->default('0')->after('tax');
                }
                if (!Schema::hasColumn('products', 'photo')) {
                    $table->string('photo', 300)->nullable()->after('fee_masraf');
                }
                if (!Schema::hasColumn('products', 'attrs')) {
                    $table->string('attrs', 5000)->nullable()->after('photo');
                }
                if (!Schema::hasColumn('products', 'item_sale_status')) {
                    $table->boolean('item_sale_status')->nullable()->after('attrs');
                }
                if (!Schema::hasColumn('products', 'pack_sale_status')) {
                    $table->boolean('pack_sale_status')->nullable()->default(false)->after('item_sale_status');
                }
                if (!Schema::hasColumn('products', 'isFreez')) {
                    $table->boolean('isFreez')->default(false)->after('pack_sale_status');
                }
                if (!Schema::hasColumn('products', 'set_price')) {
                    $table->boolean('set_price')->default(false)->after('isActive');
                }
                if (!Schema::hasColumn('products', 'isMaterial')) {
                    $table->boolean('isMaterial')->default(false)->after('set_price');
                }
                if (!Schema::hasColumn('products', 'depot_id')) {
                    $table->integer('depot_id')->nullable()->after('isMaterial');
                }
            });
        }

        if (Schema::hasTable('stocks')) {
            Schema::table('stocks', function (Blueprint $table) {
                if (!Schema::hasColumn('stocks', 'pr_id')) {
                    $table->integer('pr_id')->after('inputDate');
                }
            });
        }
    }

    public function down()
    {
        // Intentionally left blank to avoid dropping or narrowing legacy production columns on rollback.
    }

    private function dropForeignIfExists(string $table, string $constraint): void
    {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint}");
        }
    }
};

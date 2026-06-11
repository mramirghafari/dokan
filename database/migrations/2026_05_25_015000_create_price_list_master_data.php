<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->createPriceTypes();
        $this->createPriceLists();
        $this->createPriceListItems();
        $this->backfillDefaultPriceLists();
    }

    public function down()
    {
        // Non-destructive migration: keep standardized price-list master-data intact.
    }

    private function createPriceTypes(): void
    {
        if (Schema::hasTable('price_types')) {
            return;
        }

        Schema::create('price_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('code', 60)->index();
            $table->string('title');
            $table->string('price_basis', 30)->default('manual');
            $table->integer('priority')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('isActive')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'organization_id', 'code'], 'price_types_scope_code_index');
        });
    }

    private function createPriceLists(): void
    {
        if (Schema::hasTable('price_lists')) {
            return;
        }

        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('price_type_id')->nullable()->index();
            $table->string('code', 60)->index();
            $table->string('title');
            $table->string('currency_type', 20)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->boolean('is_default')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'organization_id', 'status'], 'price_lists_scope_status_index');
        });
    }

    private function createPriceListItems(): void
    {
        if (Schema::hasTable('price_list_items')) {
            return;
        }

        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('price_list_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('unit_id')->nullable()->index();
            $table->decimal('price', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->nullable();
            $table->decimal('tax', 18, 2)->nullable();
            $table->decimal('consumer_fee', 18, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('isActive')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['price_list_id', 'product_id', 'unit_id'], 'price_list_items_product_unit_unique');
            $table->index(['tenant_id', 'organization_id', 'product_id'], 'price_list_items_scope_product_index');
        });
    }

    private function backfillDefaultPriceLists(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasTable('price_types') || !Schema::hasTable('price_lists')) {
            return;
        }

        DB::table('products')->select('id', 'tenant_id', 'organization_id', 'base_unit_id', 'price', 'discount', 'tax', 'fee_masraf')->orderBy('id')->chunkById(500, function ($products) {
            foreach ($products as $product) {
                $organizationId = $this->primaryOrganizationId($product->organization_id);
                $tenantId = $product->tenant_id ?: $this->tenantIdForOrganization($organizationId);
                $priceTypeId = $this->priceTypeId($tenantId, $organizationId);
                $priceListId = $this->priceListId($tenantId, $organizationId, $priceTypeId);

                DB::table('price_list_items')->updateOrInsert(
                    [
                        'price_list_id' => $priceListId,
                        'product_id' => $product->id,
                        'unit_id' => $product->base_unit_id,
                    ],
                    [
                        'tenant_id' => $tenantId,
                        'organization_id' => $organizationId,
                        'price' => $this->money($product->price),
                        'discount' => $this->nullableMoney($product->discount),
                        'tax' => $this->nullableMoney($product->tax),
                        'consumer_fee' => $this->nullableMoney($product->fee_masraf),
                        'isActive' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    private function priceTypeId($tenantId, ?int $organizationId): int
    {
        DB::table('price_types')->updateOrInsert(
            ['tenant_id' => $tenantId, 'organization_id' => $organizationId, 'code' => 'default_sale'],
            ['title' => 'قیمت فروش پیش فرض', 'price_basis' => 'manual', 'priority' => 10, 'is_default' => 1, 'isActive' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        return (int) DB::table('price_types')->where(['tenant_id' => $tenantId, 'organization_id' => $organizationId, 'code' => 'default_sale'])->value('id');
    }

    private function priceListId($tenantId, ?int $organizationId, int $priceTypeId): int
    {
        DB::table('price_lists')->updateOrInsert(
            ['tenant_id' => $tenantId, 'organization_id' => $organizationId, 'code' => 'default_sale'],
            ['price_type_id' => $priceTypeId, 'title' => 'لیست فروش پیش فرض', 'status' => 'active', 'is_default' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        return (int) DB::table('price_lists')->where(['tenant_id' => $tenantId, 'organization_id' => $organizationId, 'code' => 'default_sale'])->value('id');
    }

    private function primaryOrganizationId($raw): ?int
    {
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? $decoded : [$raw];

        foreach ($values as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    private function tenantIdForOrganization(?int $organizationId): ?int
    {
        if (!$organizationId || !Schema::hasTable('organizations')) {
            return null;
        }

        $organization = DB::table('organizations')->where('id', $organizationId)->first();
        return $organization ? ($organization->tenant_id ?? $organization->tenants_id ?? null) : null;
    }

    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?: 0));
    }

    private function nullableMoney($value): ?float
    {
        return $value === null || $value === '' ? null : $this->money($value);
    }
};

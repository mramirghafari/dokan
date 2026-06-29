<?php

namespace App\Services;

use App\Models\Category;
use App\Models\factorMaker;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Store;
use App\Models\Tenants;
use App\Models\TenantUserMembership;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoohiTradePanelProvisioner
{
    private const PANEL_CODE = 'roohi-trade';
    private const PANEL_NAME = 'روحی ترید';
    private const LEGAL_NAME = 'روحی ترید';
    private const ADMIN_MOBILE = '09364352460';
    private const ADMIN_USERNAME = 'roohi';
    private const ADMIN_NAME = 'روحی';
    private const ADMIN_PASSWORD = 'Roohi@13';

    public function __construct(
        private SettingService $settings,
        private PermissionBootstrapService $permissions,
        private ProductPricePeriodService $pricePeriods,
        private FiscalYearService $fiscalYears
    ) {
    }

    public function provision(): array
    {
        return DB::transaction(function () {
            $tenant = $this->resolveTenant();
            $fiscalYear = $this->fiscalYears->ensureDefaultForTenant($tenant->id);
            $organization = $this->resolveOrganization($tenant);
            $admin = $this->resolveAdminUser($tenant, $organization);
            $role = $this->resolveAdminRole($tenant, $admin);
            $this->permissions->ensureCatalog();
            $this->permissions->syncRolePermissions($role);
            $store = $this->resolveStore($tenant, $organization);
            $category = $this->resolveCategory($tenant, $organization, $store);
            $unit = $this->resolveUnit($tenant, $organization, $store);
            $products = $this->resolveSubscriptionProducts($tenant, $organization, $store, $category, $unit, $admin);
            $pricePeriodCount = $this->syncSubscriptionPricePeriods($products);
            $factorMaker = $this->resolveFactorMaker($tenant, $organization, $store);
            $settingsCount = $this->applyPanelSettings($tenant, $organization, $admin);

            return [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->display_name,
                'fiscal_year_id' => $fiscalYear?->id,
                'fiscal_year_title' => $fiscalYear?->title,
                'organization_id' => $organization->id,
                'admin_user_id' => $admin->id,
                'admin_username' => $admin->username,
                'admin_mobile' => $admin->mobile,
                'admin_role_id' => $role->id,
                'store_id' => $store->id,
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'product_ids' => collect($products)->pluck('id')->all(),
                'price_period_count' => $pricePeriodCount,
                'factor_maker_id' => $factorMaker->id,
                'settings_count' => $settingsCount,
            ];
        });
    }

    private function resolveTenant(): Tenants
    {
        $tenant = Tenants::query()
            ->where('code', self::PANEL_CODE)
            ->orWhere('name', self::PANEL_NAME)
            ->orWhere('display_name', self::PANEL_NAME)
            ->first();

        if ($tenant) {
            $tenant->update([
                'code' => self::PANEL_CODE,
                'name' => self::PANEL_NAME,
                'display_name' => self::PANEL_NAME,
                'legal_name' => self::LEGAL_NAME,
                'mobile' => self::ADMIN_MOBILE,
                'subscription_type' => '1_year',
                'subscription_started_at' => Carbon::today(),
                'subscription_ends_at' => Carbon::today()->addYear(),
                'status' => 1,
                'panel_status' => 'active',
                'panel_type' => 'subscription_sales',
                'currency_type' => 'rial',
            ]);

            return $tenant->fresh();
        }

        return Tenants::create([
            'code' => self::PANEL_CODE,
            'name' => self::PANEL_NAME,
            'display_name' => self::PANEL_NAME,
            'legal_name' => self::LEGAL_NAME,
            'phone' => self::ADMIN_MOBILE,
            'mobile' => self::ADMIN_MOBILE,
            'subscription_type' => '1_year',
            'subscription_started_at' => Carbon::today(),
            'subscription_ends_at' => Carbon::today()->addYear(),
            'status' => 1,
            'panel_status' => 'active',
            'panel_type' => 'subscription_sales',
            'currency_type' => 'rial',
            'tozihat' => 'فروش اشتراک یک، دو و سه ماهه | تمرکز CRM، حسابداری و مدیریت کارمندان',
        ]);
    }

    private function resolveOrganization(Tenants $tenant): Organization
    {
        $organization = Organization::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'مرکز روحی ترید')
            ->first();

        if ($organization) {
            return $organization;
        }

        return Organization::create([
            'title' => 'مرکز روحی ترید',
            'description' => 'شعبه مرکزی ' . self::PANEL_NAME,
            'type' => 1,
            'currency_type' => 2,
            'isActive' => 1,
            'tenants_id' => $tenant->id,
            'tenant_id' => $tenant->id,
        ]);
    }

    private function resolveAdminUser(Tenants $tenant, Organization $organization): User
    {
        $user = User::query()
            ->where('mobile', self::ADMIN_MOBILE)
            ->orWhere('username', self::ADMIN_USERNAME)
            ->first();

        $payload = [
            'name' => self::ADMIN_NAME,
            'username' => self::ADMIN_USERNAME,
            'mobile' => self::ADMIN_MOBILE,
            'password' => Hash::make(self::ADMIN_PASSWORD),
            'isActive' => 1,
            'isAdmin' => 1,
            'isGod' => 0,
            'organization_id' => json_encode([(string) $organization->id, (int) $organization->id]),
            'tenant_id' => $tenant->id,
            'tenants_id' => $tenant->id,
        ];

        if ($user) {
            $user->update($payload);

            $user = $user->fresh();
        } else {
            $user = User::create($payload);
        }

        TenantUserMembership::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
            ],
            [
                'organization_id' => $organization->id,
                'is_admin' => true,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return $user;
    }

    private function resolveAdminRole(Tenants $tenant, User $admin): Role
    {
        $role = Role::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'panel_manager')
            ->first();

        if (!$role) {
            $role = Role::create([
                'title' => 'panel_manager',
                'description' => 'مدیر کل',
                'tenant_id' => $tenant->id,
                'isActive' => 1,
            ]);
        } else {
            $role->update([
                'description' => 'مدیر کل',
                'isActive' => 1,
            ]);
        }

        $admin->roles()->syncWithoutDetaching([$role->id]);

        return $role;
    }

    private function resolveStore(Tenants $tenant, Organization $organization): Store
    {
        $store = Store::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'مرکز فروش روحی ترید')
            ->first();

        if ($store) {
            return $store;
        }

        return Store::create([
            'title' => 'مرکز فروش روحی ترید',
            'description' => 'انبار مجازی برای ثبت اشتراک و فاکتور',
            'code' => 50001,
            'store_type' => 'virtual',
            'stock_tracking_mode' => 'non_tracked',
            'organization_id' => $organization->id,
            'tenants_id' => $tenant->id,
            'tenant_id' => $tenant->id,
            'isActive' => 1,
        ]);
    }

    private function resolveCategory(Tenants $tenant, Organization $organization, Store $store): Category
    {
        $category = Category::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'اشتراک')
            ->first();

        if ($category) {
            return $category;
        }

        return Category::create([
            'title' => 'اشتراک',
            'description' => 'محصولات اشتراکی روحی ترید',
            'isActive' => 1,
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
        ]);
    }

    private function resolveUnit(Tenants $tenant, Organization $organization, Store $store): Unit
    {
        $unit = Unit::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'ماه')
            ->first();

        if ($unit) {
            return $unit;
        }

        return Unit::create([
            'title' => 'ماه',
            'symbol' => 'ماه',
            'description' => 'واحد اشتراک ماهانه',
            'unit_type' => 'service',
            'isActive' => 1,
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
        ]);
    }

    private function resolveSubscriptionProducts(
        Tenants $tenant,
        Organization $organization,
        Store $store,
        Category $category,
        Unit $unit,
        User $admin
    ): array {
        $definitions = [
            ['sku' => 'ROOHI-SUB-1M', 'title' => 'اشتراک یک ماهه', 'display_name' => '۱ ماهه', 'price' => 0, 'pack_items' => 1],
            ['sku' => 'ROOHI-SUB-2M', 'title' => 'اشتراک دو ماهه', 'display_name' => '۲ ماهه', 'price' => 0, 'pack_items' => 2],
            ['sku' => 'ROOHI-SUB-3M', 'title' => 'اشتراک سه ماهه', 'display_name' => '۳ ماهه', 'price' => 0, 'pack_items' => 3],
        ];

        $products = [];

        foreach ($definitions as $definition) {
            $product = Product::query()
                ->where('tenant_id', $tenant->id)
                ->where('sku', $definition['sku'])
                ->first();

            $payload = [
                'title' => $definition['title'],
                'display_name' => $definition['display_name'],
                'sku' => $definition['sku'],
                'parentCategory_id' => $category->id,
                'product_type' => 'service',
                'stock_tracking_mode' => 'non_tracked',
                'traceability_mode' => 'none',
                'valuation_method' => 'manual',
                'pr_unit' => 'ماه',
                'base_unit_id' => $unit->id,
                'pack_items' => $definition['pack_items'],
                'unit_conversion_factor' => 1,
                'price' => $definition['price'],
                'consumer_price' => $definition['price'],
                'tax' => 0,
                'isActive' => 1,
                'item_sale_status' => 0,
                'pack_sale_status' => 0,
                'order_quantity_mode' => 'none',
                'organization_id' => $organization->id,
                'tenant_id' => $tenant->id,
                'store_id' => $store->id,
                'user_id' => $admin->id,
            ];

            if ($product) {
                $product->update($payload);
                $products[] = $product->fresh();
            } else {
                $products[] = Product::create($payload);
            }
        }

        return $products;
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function syncSubscriptionPricePeriods(array $products): int
    {
        $definitions = config('roohi_trade_subscription_prices', []);
        $bySku = collect($products)->keyBy('sku');
        $synced = 0;

        foreach ($definitions as $sku => $rows) {
            $product = $bySku->get($sku);
            if (!$product) {
                continue;
            }

            $this->pricePeriods->syncForProduct($product, $rows, true);
            $synced += count($rows);
        }

        return $synced;
    }

    public function syncPricePeriodsOnly(): array
    {
        $tenant = Tenants::query()
            ->where('code', self::PANEL_CODE)
            ->first();

        if (!$tenant) {
            throw new \RuntimeException('پنل روحی ترید یافت نشد. ابتدا panel:provision-roohi-trade را اجرا کنید.');
        }

        $products = Product::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('sku', array_keys(config('roohi_trade_subscription_prices', [])))
            ->get()
            ->all();

        if ($products === []) {
            throw new \RuntimeException('محصولات اشتراک روحی ترید یافت نشد.');
        }

        $count = $this->syncSubscriptionPricePeriods($products);

        return [
            'tenant_id' => $tenant->id,
            'product_ids' => collect($products)->pluck('id')->all(),
            'price_period_count' => $count,
        ];
    }

    private function resolveFactorMaker(Tenants $tenant, Organization $organization, Store $store): factorMaker
    {
        $factorMaker = factorMaker::query()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'فاکتور رسمی روحی ترید')
            ->first();

        $payload = [
            'name' => 'فاکتور رسمی روحی ترید',
            'type' => 1,
            'pr_type' => 'subscription',
            'business_profile' => 'subscription',
            'line_layout' => [
                'labels' => [
                    'plan' => 'پلن اشتراک',
                    'duration' => 'مدت (ماه)',
                    'seats' => 'تعداد کاربر',
                    'unit_price' => 'فی ماهانه',
                ],
            ],
            'currency_type' => 2,
            'seller_name' => self::PANEL_NAME,
            'seller_phone' => self::ADMIN_MOBILE,
            'column_pr_code' => 1,
            'column_moadian' => 2,
            'column_sub_unit' => 2,
            'column_discount' => 1,
            'column_tax' => 1,
            'organization_id' => json_encode([(string) $organization->id]),
            'store_id' => json_encode([(string) $store->id]),
            'tenant_id' => $tenant->id,
        ];

        if ($factorMaker) {
            $factorMaker->update($payload);

            return $factorMaker->fresh();
        }

        return factorMaker::create($payload);
    }

    private function applyPanelSettings(Tenants $tenant, Organization $organization, User $admin): int
    {
        $context = [
            'tenant_id' => $tenant->id,
            'organization_id' => $organization->id,
            'updated_by' => $admin->id,
        ];

        $tenantContext = [
            'tenant_id' => $tenant->id,
            'updated_by' => $admin->id,
        ];

        $settings = [
            'feature_distribution' => ['value' => 'no', 'type' => 'boolean'],
            'feature_route_management' => ['value' => 'no', 'type' => 'boolean'],
            'feature_visitor_management' => ['value' => 'no', 'type' => 'boolean'],
            'feature_area_management' => ['value' => 'no', 'type' => 'boolean'],
            'feature_city_management' => ['value' => 'no', 'type' => 'boolean'],
            'feature_multi_warehouse' => ['value' => 'no', 'type' => 'boolean'],
            'feature_warehouse_management' => ['value' => 'no', 'type' => 'boolean'],
            'feature_gps_tracking' => ['value' => 'no', 'type' => 'boolean'],
            'invoice_location_roles' => ['value' => json_encode(['visitor', 'leader', 'sales_manager', 'panel_manager']), 'type' => 'multiselect'],
            'feature_agency_sales' => ['value' => 'no', 'type' => 'boolean'],
            'feature_direct_customer_registration' => ['value' => 'yes', 'type' => 'boolean'],
            'feature_customer_location_map' => ['value' => 'no', 'type' => 'boolean'],
            'feature_branch_management' => ['value' => 'yes', 'type' => 'boolean'],
            'feature_double_entry_accounting' => ['value' => 'yes', 'type' => 'boolean'],
            'feature_manager_order_approval' => ['value' => 'yes', 'type' => 'boolean'],
            'feature_sales_targets' => ['value' => 'yes', 'type' => 'boolean'],
            'feature_customer_credit' => ['value' => 'yes', 'type' => 'boolean'],
            'feature_commission' => ['value' => 'no', 'type' => 'boolean'],
            'sales_scenario_template' => ['value' => 'direct_sales', 'type' => 'select'],
            'sales_process_entry_point' => ['value' => 'customer', 'type' => 'select'],
            'sales_document_flow' => ['value' => json_encode(['customer', 'invoice', 'approval', 'accounting', 'crm_followup']), 'type' => 'multiselect'],
            'sales_inventory_policy' => ['value' => 'preorder_supply', 'type' => 'select'],
            'invoice_creation_roles' => ['value' => json_encode(['panel_manager', 'sales_manager', 'expert']), 'type' => 'multiselect'],
            'invoice_approval_policy' => ['value' => 'general_manager', 'type' => 'select'],
            'invoice_approval_roles' => ['value' => json_encode(['panel_manager']), 'type' => 'multiselect'],
            'customer_creation_mode' => ['value' => json_encode(['direct']), 'type' => 'multiselect'],
            'customer_creation_roles' => ['value' => json_encode(['panel_manager', 'sales_manager', 'expert', 'all']), 'type' => 'multiselect'],
            'customer_approval_policy' => ['value' => 'auto', 'type' => 'select'],
            'crm_followup_policy' => ['value' => 'required_after_invoice', 'type' => 'select'],
            'crm_automation_policy' => ['value' => json_encode(['task_after_invoice', 'task_for_new_customer']), 'type' => 'multiselect'],
            'currency_type' => ['value' => 'rial', 'type' => 'select'],
            'tax_percent' => ['value' => '0', 'type' => 'number'],
            'vat_percent' => ['value' => '0', 'type' => 'number'],
            'panel_onboarding_active' => ['value' => 'yes', 'type' => 'boolean'],
            'panel_welcome_seen' => ['value' => 'no', 'type' => 'boolean'],
            'panel_tour_completed' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_quick_actions' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_user_info' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_org_stats' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_top_visitors' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_top_leaders' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_recent_factors' => ['value' => 'no', 'type' => 'boolean'],
            'dashboard_widget_warehouse' => ['value' => 'no', 'type' => 'boolean'],
        ];

        foreach ($settings as $key => $meta) {
            $saveContext = $this->shouldSaveAtTenantScope($key) ? $tenantContext : $context;

            $this->settings->set(
                $key,
                $meta['value'],
                $saveContext,
                $meta['type'],
                config("panel_settings.definitions.$key.group", 'general')
            );
        }

        $tenant->update([
            'settings' => array_merge((array) ($tenant->settings ?? []), [
                'business_model' => 'subscription_sales',
                'focus_modules' => ['crm', 'accounting', 'employees', 'customers', 'invoices'],
            ]),
        ]);

        return count($settings);
    }

    private function shouldSaveAtTenantScope(string $key): bool
    {
        if (str_starts_with($key, 'feature_')) {
            return true;
        }

        return str_starts_with($key, 'panel_')
            || str_starts_with($key, 'dashboard_widget_');
    }
}

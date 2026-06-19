<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\Customers;
use App\Models\factorMaker;
use App\Models\Product;
use App\Models\Role;
use App\Models\Tenants;
use App\Models\User;
use Illuminate\Support\Carbon;

class PanelOnboardingService
{
    public function __construct(
        private SettingService $settings,
        private TenantContextService $tenantContext,
        private PanelDashboardWidgetService $widgets
    ) {
    }

    private function settingContext(?User $user = null, ?int $tenantId = null): array
    {
        return $this->tenantContext->settingContext($user, array_filter([
            'tenant_id' => $tenantId,
        ]));
    }

    public function isYoungPanel(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?: $this->tenantContext->tenantId();

        if (!$tenantId) {
            return false;
        }

        if (!$this->onboardingActive($tenantId, null)) {
            return false;
        }

        $tenant = Tenants::query()->find($tenantId);

        if (!$tenant) {
            return false;
        }

        if ($tenant->created_at && Carbon::parse($tenant->created_at)->greaterThan(now()->subDays(90))) {
            return true;
        }

        return $this->completionPercent($tenantId) < 100;
    }

    public function dashboardState(?User $user = null, ?int $tenantId = null): array
    {
        $tenantId = $tenantId ?: $this->tenantContext->tenantId($user);
        $young = $this->isYoungPanel($tenantId);
        $context = $this->settingContext($user, $tenantId);
        $steps = $this->setupSteps($tenantId);
        $completed = collect($steps)->where('done', true)->count();
        $total = max(count($steps), 1);
        $percent = (int) round(($completed / $total) * 100);
        $setupIncomplete = $completed < $total;

        return [
            'is_young_panel' => $young,
            'show_setup_card' => $young,
            'setup_incomplete' => $setupIncomplete,
            'show_welcome_modal' => $young && $setupIncomplete && !$this->flagIsYes('panel_tour_completed', $tenantId, $user),
            'show_tour' => $young
                && $this->flagIsYes('panel_welcome_seen', $tenantId, $user)
                && !$this->flagIsYes('panel_tour_completed', $tenantId, $user),
            'completion_percent' => $percent,
            'steps' => $steps,
            'panel_name' => Tenants::query()->find($tenantId)?->display_name
                ?: Tenants::query()->find($tenantId)?->name
                ?: 'پنل شما',
        ];
    }

    public function setupSteps(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?: $this->tenantContext->tenantId();

        if (!$tenantId) {
            return [];
        }

        $productCount = Product::query()->where('tenant_id', $tenantId)->count();
        $customerCount = Customers::query()->where('tenant_id', $tenantId)->count();
        $factorMakerCount = factorMaker::query()->where('tenant_id', $tenantId)->count();
        $userCount = User::query()->where('tenant_id', $tenantId)->where('isActive', 1)->count();
        $roleCount = Role::query()->where('tenant_id', $tenantId)->where('isActive', 1)->count();
        $accountCount = Accounts::query()->where('tenant_id', $tenantId)->count();
        $salesScenario = $this->settings->get('sales_scenario_template', $this->settingContext(null, $tenantId), null);

        return [
            [
                'key' => 'panel_settings',
                'title' => 'تنظیمات اولیه پنل',
                'description' => 'سناریوی فروش، مالیات و ماژول‌های فعال را مشخص کنید.',
                'route' => route('settings.salesScenario'),
                'icon' => 'settings',
                'done' => !empty($salesScenario),
            ],
            [
                'key' => 'factor_template',
                'title' => 'قالب فاکتور',
                'description' => 'حداقل یک قالب فاکتور با ستون‌های مناسب کسب‌وکار تعریف کنید.',
                'route' => route('FactorManager.index'),
                'icon' => 'file-invoice',
                'done' => $factorMakerCount > 0,
            ],
            [
                'key' => 'products',
                'title' => 'محصولات و خدمات',
                'description' => 'کالا یا اشتراک‌های قابل فروش را ثبت کنید.',
                'route' => route('products.index'),
                'icon' => 'package',
                'done' => $productCount > 0,
            ],
            [
                'key' => 'customers',
                'title' => 'اولین مشتری',
                'description' => 'حداقل یک مشتری برای شروع فروش و CRM ثبت کنید.',
                'route' => route('customers.create'),
                'icon' => 'users',
                'done' => $customerCount > 0,
            ],
            [
                'key' => 'team',
                'title' => 'تیم و نقش‌ها',
                'description' => 'کاربران و نقش‌های دسترسی پنل را بسازید.',
                'route' => route('users.index'),
                'icon' => 'user-check',
                'done' => $userCount > 1 || $roleCount > 1,
            ],
            [
                'key' => 'accounts',
                'title' => 'حساب‌های مالی',
                'description' => 'حساب بانکی یا صندوق برای ثبت تراکنش‌ها تعریف کنید.',
                'route' => route('Account.index'),
                'icon' => 'cash',
                'done' => $accountCount > 0,
            ],
        ];
    }

    public function completionPercent(?int $tenantId = null): int
    {
        $steps = $this->setupSteps($tenantId);
        $total = max(count($steps), 1);
        $done = collect($steps)->where('done', true)->count();

        return (int) round(($done / $total) * 100);
    }

    public function markWelcomeSeen(?int $tenantId = null, ?int $userId = null): void
    {
        $this->setFlag('panel_welcome_seen', 'yes', $tenantId, $userId);
    }

    public function markTourCompleted(?int $tenantId = null, ?int $userId = null): void
    {
        $this->setFlag('panel_tour_completed', 'yes', $tenantId, $userId);
    }

    public function completeOnboarding(?int $tenantId = null, ?int $userId = null): void
    {
        $tenantId = $tenantId ?: $this->tenantContext->tenantId();

        $this->setFlag('panel_onboarding_active', 'no', $tenantId, $userId);
        $this->setFlag('panel_welcome_seen', 'yes', $tenantId, $userId);
        $this->setFlag('panel_tour_completed', 'yes', $tenantId, $userId);

        foreach ($this->widgets->definitions() as $definition) {
            $key = $definition['key'];

            if ($key === 'dashboard_widget_setup_card') {
                continue;
            }

            $this->settings->set(
                $key,
                'yes',
                [
                    'tenant_id' => $tenantId,
                    'updated_by' => $userId,
                ],
                'boolean',
                'dashboard_widgets'
            );
        }
    }

    private function flagIsYes(string $key, ?int $tenantId, ?User $user = null): bool
    {
        $value = $this->settings->get(
            $key,
            $this->settingContext($user, $tenantId),
            config("panel_settings.definitions.$key.default", 'no')
        );

        return in_array($value, ['yes', '1', 1, true, 'on'], true);
    }

    private function onboardingActive(?int $tenantId, ?User $user = null): bool
    {
        return $this->flagIsYes('panel_onboarding_active', $tenantId, $user);
    }

    private function setFlag(string $key, string $value, ?int $tenantId, ?int $userId): void
    {
        $this->settings->set(
            $key,
            $value,
            [
                'tenant_id' => $tenantId,
                'updated_by' => $userId,
            ],
            'boolean',
            config("panel_settings.definitions.$key.group", 'onboarding')
        );
    }
}

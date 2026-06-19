<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\Tenants;
use App\Models\User;
use Illuminate\Support\Carbon;

class CustomerListColumnService
{
    public const SETTING_KEY = 'customer_list_visible_columns';

    public function isSubscriptionPanel(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?: app(TenantContextService::class)->tenantId();

        if (!$tenantId) {
            return false;
        }

        $tenant = Tenants::query()->find($tenantId);

        if (!$tenant) {
            return false;
        }

        if ($tenant->panel_type === 'subscription_sales') {
            return true;
        }

        $settings = (array) ($tenant->settings ?? []);

        return ($settings['business_model'] ?? null) === 'subscription_sales';
    }

    /**
     * @return array<int, array{key: string, label: string, index: int, description?: string, fixed?: bool, toggleable?: bool, sortable?: string, feature?: string, panel_mode?: string, default?: bool}>
     */
    public function resolvedColumns(?int $tenantId = null): array
    {
        $columns = [];
        $index = 0;
        $subscriptionPanel = $this->isSubscriptionPanel($tenantId);

        foreach ($this->definitions() as $definition) {
            if (!empty($definition['feature']) && !TenantSettings::enabled($definition['feature'], $tenantId)) {
                continue;
            }

            $panelMode = $definition['panel_mode'] ?? null;

            if ($panelMode === 'subscription' && !$subscriptionPanel) {
                continue;
            }

            if ($panelMode === 'product' && $subscriptionPanel) {
                continue;
            }

            $columns[] = array_merge($definition, ['index' => $index++]);
        }

        return $columns;
    }

    /**
     * @return array<string, array{label: string, index: int, description: string}>
     */
    public function catalog(?int $tenantId = null): array
    {
        $catalog = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            if (($column['toggleable'] ?? true) === false) {
                continue;
            }

            $catalog[$column['key']] = [
                'label' => $column['label'],
                'index' => $column['index'],
                'description' => $column['description'] ?? '',
            ];
        }

        return $catalog;
    }

    /**
     * @return array<int, array{key: string, label: string, index: int}>
     */
    public function headers(?int $tenantId = null): array
    {
        return array_map(
            fn (array $column) => [
                'key' => $column['key'],
                'label' => $column['label'],
                'index' => $column['index'],
            ],
            $this->resolvedColumns($tenantId)
        );
    }

    public function columnCount(?int $tenantId = null): int
    {
        return count($this->resolvedColumns($tenantId));
    }

    /**
     * @return array<int, int>
     */
    public function fixedColumnIndexes(?int $tenantId = null): array
    {
        return array_values(array_map(
            fn (array $column) => $column['index'],
            array_filter(
                $this->resolvedColumns($tenantId),
                fn (array $column) => (bool) ($column['fixed'] ?? false)
            )
        ));
    }

    /**
     * @return array<int, string>
     */
    public function sortableColumnsMap(?int $tenantId = null): array
    {
        $map = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            if (!empty($column['sortable'])) {
                $map[$column['index']] = $column['sortable'];
            }
        }

        return $map;
    }

    /**
     * @return array<int, int>
     */
    public function defaultVisibleKeys(?int $tenantId = null): array
    {
        $keys = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            if (($column['toggleable'] ?? true) === false) {
                continue;
            }

            if ($column['default'] ?? false) {
                $keys[] = $column['key'];
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return array<int, string>
     */
    public function visibleKeys(?int $tenantId = null): array
    {
        $saved = TenantSettings::get(self::SETTING_KEY, $tenantId);

        if (!is_array($saved) || $saved === []) {
            return $this->defaultVisibleKeys($tenantId);
        }

        return $this->normalizeKeys($saved, $tenantId);
    }

    /**
     * @return array<int, int>
     */
    public function hiddenColumnIndexes(?int $tenantId = null): array
    {
        $catalog = $this->catalog($tenantId);
        $visible = $this->visibleKeys($tenantId);
        $hidden = [];

        foreach ($catalog as $key => $column) {
            if (!in_array($key, $visible, true)) {
                $hidden[] = (int) $column['index'];
            }
        }

        return $hidden;
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function saveVisibleKeys(array $keys, User $user): array
    {
        $tenantId = app(TenantContextService::class)->tenantId($user);
        $normalized = $this->normalizeKeys($keys, $tenantId);
        $context = app(TenantContextService::class)->fromUser($user);
        unset($context['user_id']);
        $context['updated_by'] = $user->id;

        app(SettingService::class)->set(
            self::SETTING_KEY,
            $normalized,
            $context,
            'json',
            'customers'
        );

        return $normalized;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    public function normalizeKeys(array $keys, ?int $tenantId = null): array
    {
        $allowed = array_keys($this->catalog($tenantId));

        return array_values(array_filter(
            array_unique(array_map('strval', $keys)),
            fn (string $key) => in_array($key, $allowed, true)
        ));
    }

    /**
     * @return array<int, string|int>
     */
    public function buildDatatableRow(
        Customers $customer,
        int $rowNumber,
        string $showUrl,
        string $ordersUrl,
        string $actionsHtml,
        ?int $tenantId = null
    ): array {
        $regionName = $customer->region->name ?? '';
        $areaName = $customer->Area->name ?? '';
        $statusBadge = (int) $customer->status === 1
            ? '<span class="badge bg-label-success">فعال</span>'
            : '<span class="badge bg-label-danger">غیرفعال</span>';

        $marketerName = optional(optional($customer->latestPishfactor)->visitor)->name
            ?: optional($customer->creator)->name
            ?: '—';

        $values = [
            'row' => $rowNumber,
            'customer_code' => '<a href="' . $showUrl . '">' . e($customer->customer_code) . '</a>',
            'name' => $this->formatCustomerNameCell($customer, $showUrl),
            'mobile' => '<span dir="ltr" class="d-inline-block">' . e($customer->mobile ?: '—') . '</span>',
            'national_id' => e($customer->national_id ?: '—'),
            'tablo' => '<a href="' . $showUrl . '"><small>' . e($customer->tablo) . '</small></a>',
            'region_area' => '<small>' . e($regionName) . ($regionName && $areaName ? ' / ' : '') . e($areaName) . '</small>',
            'segment_channel' => '<small>' . e($customer->senf) . ($customer->senf && $customer->channel ? ' / ' : '') . e($customer->channel) . '</small>',
            'joined_at' => e($this->formatJoinedAt($customer->created_at)),
            'marketer' => e($marketerName),
            'registrar' => e(optional($customer->creator)->name ?: '—'),
            'account_balance' => $this->formatMoneyCell((float) ($customer->list_account_balance ?? 0)),
            'subscription_balance' => $this->formatSubscriptionBalanceCell($customer->list_subscription_end ?? null),
            'orders_count' => '<a href="' . $ordersUrl . '">' . number_format((int) ($customer->active_orders_count ?? 0)) . '</a>',
            'orders_sum' => '<a href="' . $ordersUrl . '">' . number_format((int) ($customer->active_orders_sum ?? 0)) . '</a>',
            'purchases_count' => '<a href="' . $ordersUrl . '">' . number_format((int) ($customer->purchases_count ?? 0)) . '</a>',
            'purchases_sum' => '<a href="' . $ordersUrl . '">' . number_format((int) ($customer->purchases_sum ?? 0)) . '</a>',
            'leader' => e($customer->leader->name ?? '—'),
            'status' => $statusBadge,
            'actions' => $actionsHtml,
        ];

        $row = [];

        foreach ($this->resolvedColumns($tenantId) as $column) {
            $row[] = $values[$column['key']] ?? '—';
        }

        return $row;
    }

    private function formatJoinedAt(mixed $createdAt): string
    {
        if ($createdAt === null || $createdAt === '') {
            return '—';
        }

        try {
            return Carbon::parse($createdAt)->format('Y/m/d H:i');
        } catch (\Throwable) {
            return '—';
        }
    }

    private function formatCustomerNameCell(Customers $customer, string $showUrl): string
    {
        $badges = [];

        if ((int) $customer->status === 1) {
            $badges[] = '<span class="customer-name-badge customer-name-badge--active" title="مشتری فعال" aria-label="مشتری فعال">'
                . $this->activeCustomerIconSvg()
                . '</span>';
        }

        if ((int) ($customer->purchases_count ?? 0) > 1) {
            $badges[] = '<span class="customer-name-badge customer-name-badge--loyal" title="مشتری وفادار (بیش از یک خرید)" aria-label="مشتری وفادار">'
                . $this->loyalCustomerIconSvg()
                . '</span>';
        }

        $badgeHtml = $badges === []
            ? ''
            : '<span class="customer-name-badges">' . implode('', $badges) . '</span>';

        return '<span class="customer-name-cell d-inline-flex align-items-center gap-1">'
            . $badgeHtml
            . '<a href="' . $showUrl . '">' . e($customer->name) . '</a>'
            . '</span>';
    }

    private function formatMoneyCell(float $amount): string
    {
        if ($amount <= 0) {
            return '<span class="text-muted">۰</span>';
        }

        return '<span class="text-end d-inline-block fw-medium" dir="ltr">' . number_format($amount) . '</span>';
    }

    private function formatSubscriptionBalanceCell(mixed $subscriptionEnd): string
    {
        if ($subscriptionEnd === null || $subscriptionEnd === '') {
            return '<span class="text-muted">—</span>';
        }

        try {
            $endDate = Carbon::parse($subscriptionEnd)->startOfDay();
            $daysRemaining = now()->startOfDay()->diffInDays($endDate, false);
        } catch (\Throwable) {
            return '<span class="text-muted">—</span>';
        }

        if ($daysRemaining > 0) {
            return '<span class="badge bg-label-success">' . number_format($daysRemaining) . ' روز مانده</span>'
                . '<small class="d-block text-muted mt-1">' . $endDate->format('Y/m/d') . '</small>';
        }

        if ($daysRemaining === 0) {
            return '<span class="badge bg-label-warning">امروز پایان</span>'
                . '<small class="d-block text-muted mt-1">' . $endDate->format('Y/m/d') . '</small>';
        }

        return '<span class="badge bg-label-danger">منقضی (' . number_format(abs($daysRemaining)) . ' روز)</span>'
            . '<small class="d-block text-muted mt-1">' . $endDate->format('Y/m/d') . '</small>';
    }

    private function activeCustomerIconSvg(): string
    {
        return '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
            . '<circle cx="8" cy="8" r="6.25" stroke="#22c55e" stroke-width="1.5"/>'
            . '<path d="M5.25 8.1L7.1 9.95L10.85 6.2" stroke="#22c55e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</svg>';
    }

    private function loyalCustomerIconSvg(): string
    {
        return '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
            . '<path d="M8 13.5S3.5 10.2 3.5 6.6C3.5 4.9 4.8 3.5 6.5 3.5C7.45 3.5 8.3 4 8 4.8C7.7 4 8.55 3.5 9.5 3.5C11.2 3.5 12.5 4.9 12.5 6.6C12.5 10.2 8 13.5 8 13.5Z" fill="#F9BA16" stroke="#543C92" stroke-width="1.1" stroke-linejoin="round"/>'
            . '</svg>';
    }

    /**
     * @return array<int, array{key: string, label: string, description?: string, fixed?: bool, toggleable?: bool, sortable?: string, feature?: string, panel_mode?: string, default?: bool}>
     */
    private function definitions(): array
    {
        return [
            ['key' => 'row', 'label' => 'ردیف', 'fixed' => true, 'toggleable' => false, 'sortable' => 'customers.id'],
            ['key' => 'customer_code', 'label' => 'کد مشتری', 'description' => 'کد یکتای مشتری در سیستم', 'sortable' => 'customers.customer_code', 'default' => true],
            ['key' => 'name', 'label' => 'نام مشتری', 'description' => 'آیکون سبز = فعال، قلب طلایی = وفادار (بیش از یک خرید)', 'fixed' => true, 'toggleable' => false],
            ['key' => 'mobile', 'label' => 'موبایل', 'description' => 'شماره موبایل مشتری', 'sortable' => 'customers.mobile', 'default' => true],
            ['key' => 'national_id', 'label' => 'کد ملی', 'description' => 'کد ملی یا شناسه مشتری', 'sortable' => 'customers.national_id', 'default' => false],
            ['key' => 'tablo', 'label' => 'تابلو', 'description' => 'نام تابلو یا ویترین مشتری حضوری', 'sortable' => 'customers.tablo', 'feature' => 'feature_route_management', 'default' => false],
            ['key' => 'region_area', 'label' => 'منطقه / مسیر', 'description' => 'منطقه فروش و مسیر ویزیت', 'feature' => 'feature_area_management', 'default' => false],
            ['key' => 'segment_channel', 'label' => 'صنف / کانال', 'description' => 'صنف کاری و کانال فروش', 'default' => true],
            ['key' => 'joined_at', 'label' => 'تاریخ عضویت', 'description' => 'تاریخ ثبت مشتری در سیستم', 'sortable' => 'customers.created_at', 'default' => false],
            ['key' => 'marketer', 'label' => 'بازاریاب', 'description' => 'بازاریاب آخرین سفارش یا ثبت‌کننده مشتری', 'feature' => 'feature_visitor_management', 'default' => true],
            ['key' => 'registrar', 'label' => 'ثبت‌کننده', 'description' => 'کاربری که مشتری را در سیستم ثبت کرده', 'default' => false],
            ['key' => 'account_balance', 'label' => 'مانده حساب', 'description' => 'جمع فاکتورهای تسویه‌نشده مشتری', 'sortable' => 'list_account_balance', 'default' => true],
            ['key' => 'subscription_balance', 'label' => 'مانده اشتراک', 'description' => 'روزهای باقی‌مانده تا پایان اشتراک (پنل‌های دوره‌ای)', 'sortable' => 'list_subscription_end', 'panel_mode' => 'subscription', 'default' => true],
            ['key' => 'orders_count', 'label' => 'تعداد سفارش فعال', 'description' => 'سفارش‌های در جریان یا تکمیل‌شده فعال', 'sortable' => 'active_orders_count', 'default' => true],
            ['key' => 'orders_sum', 'label' => 'مجموع سفارشات فعال', 'description' => 'جمع مبلغ سفارش‌های فعال', 'sortable' => 'active_orders_sum', 'default' => true],
            ['key' => 'purchases_count', 'label' => 'تعداد خرید', 'description' => 'تعداد کل فاکتورهای ثبت‌شده برای مشتری', 'sortable' => 'purchases_count', 'default' => false],
            ['key' => 'purchases_sum', 'label' => 'مجموع خرید', 'description' => 'جمع مبلغ کل فاکتورهای مشتری', 'sortable' => 'purchases_sum', 'default' => false],
            ['key' => 'leader', 'label' => 'سرپرست', 'description' => 'سرپرست منطقه یا مسیر مشتری', 'feature' => 'feature_visitor_management', 'default' => false],
            ['key' => 'status', 'label' => 'وضعیت', 'fixed' => true, 'toggleable' => false, 'sortable' => 'customers.status'],
            ['key' => 'actions', 'label' => 'عملیات', 'fixed' => true, 'toggleable' => false],
        ];
    }
}

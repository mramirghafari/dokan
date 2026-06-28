<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Module;
use App\Models\Organization;
use App\Models\OrganizationFeature;
use App\Models\Setting;
use App\Models\TenantFeature;
use App\Models\Tenants;
use App\Services\ActivityLogService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:settings,user')->only(['index', 'salesScenario', 'notifications', 'dashboardWidgets', 'update']);
    }

    public function salesScenario(Request $request)
    {
        $request->query->set('settings_section', 'sales_scenario');

        return $this->index($request);
    }

    public function notifications(Request $request)
    {
        abort_unless($this->canManageGlobalNotificationSettings(), 403);

        $request->query->set('settings_section', 'notification_sms');

        return $this->index($request);
    }

    public function dashboardWidgets(Request $request)
    {
        $request->query->set('settings_section', 'dashboard_widgets');

        return $this->index($request);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $targetTenantId = $this->resolveTargetTenantId($request);
        $targetOrganizationId = $this->resolveTargetOrganizationId($request, $targetTenantId);
        $tenants = $this->availableTenants();
        $organizations = $this->availableOrganizations($targetTenantId);
        $selectedTenant = $targetTenantId ? Tenants::find($targetTenantId) : null;
        $selectedOrganization = $targetOrganizationId ? Organization::find($targetOrganizationId) : null;
        $definitions = (array) config('panel_settings.definitions', []);
        $groups = (array) config('panel_settings.groups', []);
        $settingsSection = $this->resolveSettingsSection($request, $groups);

        if ($settingsSection === 'notification_sms' && !$this->canManageGlobalNotificationSettings()) {
            abort(403);
        }

        if (!$this->canManageGlobalNotificationSettings()) {
            $definitions = $this->withoutNotificationSettings($definitions);
            unset($groups['notification_sms']);
        }

        if ($settingsSection !== 'dashboard_widgets') {
            unset($groups['dashboard_widgets']);
        }

        $definitions = $this->withoutHiddenSettings($definitions, $settingsSection);

        $globalSettings = $this->settingsValues(null, array_keys($definitions));

        $tenantSettings = $targetTenantId ? $this->settingsValues($targetTenantId, array_keys($definitions)) : [];
        $organizationSettings = $targetOrganizationId ? $this->organizationSettingsValues($targetTenantId, $targetOrganizationId, array_keys($definitions)) : [];
        $featureModules = $this->featureModules($targetTenantId, $targetOrganizationId, $globalSettings, $tenantSettings);
        $navigationItems = $this->navigationItems($targetTenantId);

        $settings = [];

        foreach ($definitions as $key => $definition) {
            $group = $definition['group'] ?? 'general';
            $hasOverride = array_key_exists($key, $tenantSettings);
            $inheritedValue = $this->castSettingValueForView(
                $globalSettings[$key] ?? ($definition['default'] ?? null),
                $definition
            );
            $tenantValue = $hasOverride
                ? $this->castSettingValueForView($tenantSettings[$key], $definition)
                : $inheritedValue;
            $hasOrganizationOverride = array_key_exists($key, $organizationSettings);
            $organizationValue = $hasOrganizationOverride
                ? $this->castSettingValueForView($organizationSettings[$key], $definition)
                : $tenantValue;

            $settings[$group][$key] = array_merge($definition, [
                'key' => $key,
                'value' => $tenantValue,
                'inherited_value' => $inheritedValue,
                'inherited_display' => $this->formatSettingDisplayValue($inheritedValue, $definition),
                'has_override' => $hasOverride,
                'organization_value' => $organizationValue,
                'has_organization_override' => $hasOrganizationOverride,
            ]);
        }

        if ($settingsSection) {
            $settings = array_intersect_key($settings, [$settingsSection => true]);
            $featureModules = collect();
            $navigationItems = collect();
        } else {
            $settings = $this->sortSettingsByGroupOrder($settings);
        }

        $pageTitle = match ($settingsSection) {
            'sales_scenario' => 'سناریوی فروش',
            'notification_sms' => 'اعلانات و پیامک ها',
            'dashboard_widgets' => 'مدیریت ویجت‌های داشبورد',
            default => 'تنظیمات اختصاصی پنل',
        };
        $pageDescription = match ($settingsSection) {
            'sales_scenario' => 'در این صفحه مسیر کاری فروش از ثبت مشتری و فاکتور تا تایید، انبار، حسابداری و پیگیری CRM برای هر پنل تنظیم می شود.',
            'notification_sms' => 'در این صفحه مشخص می شود هر عملیات اعلان هدر، پیامک کاوه نگار یا هر دو را ارسال کند و متن پیامک هر عملیات چیست.',
            'dashboard_widgets' => 'مشخص کنید کدام بخش‌های داشبورد برای این پنل نمایش داده شوند.',
            default => 'تنظیمات این صفحه برای فعال یا غیرفعال کردن قابلیت ها و رفتارهای هر پنل استفاده می شود.',
        };

        return view('settings.index', compact(
            'settings',
            'groups',
            'settingsSection',
            'pageTitle',
            'pageDescription',
            'tenants',
            'organizations',
            'selectedTenant',
            'selectedOrganization',
            'targetTenantId',
            'targetOrganizationId',
            'featureModules',
            'navigationItems',
            'user'
        ));
    }

    public function update(Request $request)
    {
        $targetTenantId = $this->resolveTargetTenantId($request);
        $targetOrganizationId = $this->resolveTargetOrganizationId($request, $targetTenantId);

        if (!$targetTenantId) {
            Alert::error('خطا', 'برای ثبت تنظیمات باید یک پنل انتخاب شود');
            return redirect()->route('settings.index');
        }

        $definitions = (array) config('panel_settings.definitions', []);
        $settingsSection = $this->resolveSettingsSection($request, (array) config('panel_settings.groups', []));

        if ($settingsSection === 'notification_sms' && !$this->canManageGlobalNotificationSettings()) {
            abort(403);
        }

        if (!$this->canManageGlobalNotificationSettings()) {
            $definitions = $this->withoutNotificationSettings($definitions);
        }

        if ($settingsSection) {
            $definitions = array_filter(
                $definitions,
                fn($definition) => ($definition['group'] ?? 'general') === $settingsSection
            );
        }

        $inputSettings = $request->input('settings', []);

        foreach ($definitions as $key => $definition) {
            $value = $this->normalizeValue($inputSettings[$key] ?? null, $definition);

            Setting::updateOrCreate(
                [
                    'tenant_id' => $targetTenantId,
                    'scope' => 'tenant',
                    'key' => $key,
                ],
                [
                    'title' => $key,
                    'value' => $value,
                    'category' => $definition['group'] ?? 'general',
                    'type' => $definition['type'] ?? 'string',
                    'updated_by' => auth()->id(),
                ]
            );

            $this->syncTenantFeature($targetTenantId, $key, $value);
        }

        if ($targetOrganizationId) {
            $this->syncOrganizationFeatures($targetOrganizationId, $request->input('organization_features', []));
            $this->syncOrganizationSettings($targetTenantId, $targetOrganizationId, $request->input('organization_settings', []), $definitions);
        }

        if ($request->has('navigation_order')) {
            $this->syncNavigationOrder($targetTenantId, $request->input('navigation_order', []));
        }

        $tenant = Tenants::find($targetTenantId);
        $user = auth()->user();

        ActivityLogService::safeLog('update', 'تنظیمات پنل ویرایش شد' . ($tenant ? ' - ' . $tenant->name : ''), $user->id, [
            'section' => 'settings',
            'event_key' => 'settings.update',
        ]);

        Alert::success('تشکر', 'تنظیمات پنل با موفقیت ذخیره شد');

        session()->flash('toast', [
            'type' => 'success',
            'message' => 'تنظیمات پنل با موفقیت ذخیره شد.',
        ]);

        $route = match ($settingsSection) {
            'sales_scenario' => 'settings.salesScenario',
            'notification_sms' => 'settings.notifications',
            'dashboard_widgets' => 'settings.dashboardWidgets',
            default => 'settings.index',
        };

        return redirect()->route($route, array_filter([
            'target_tenant_id' => $targetTenantId,
            'target_organization_id' => $targetOrganizationId,
        ], fn($value) => $value !== null && $value !== ''));
    }

    private function resolveSettingsSection(Request $request, array $groups): ?string
    {
        $section = $request->input('settings_section') ?: $request->query('settings_section');

        return $section && array_key_exists($section, $groups) ? (string) $section : null;
    }

    private function sortSettingsByGroupOrder(array $settings): array
    {
        $order = (array) config('panel_settings.settings_index_group_order', []);
        $sorted = [];

        foreach ($order as $groupKey) {
            if (isset($settings[$groupKey])) {
                $sorted[$groupKey] = $settings[$groupKey];
            }
        }

        foreach ($settings as $groupKey => $items) {
            if (!isset($sorted[$groupKey])) {
                $sorted[$groupKey] = $items;
            }
        }

        return $sorted;
    }

    private function canManageGlobalNotificationSettings(): bool
    {
        return (int) Auth::user()?->isGod === 1;
    }

    private function withoutNotificationSettings(array $definitions): array
    {
        return array_filter(
            $definitions,
            fn($definition) => ($definition['group'] ?? 'general') !== 'notification_sms'
        );
    }

    private function withoutHiddenSettings(array $definitions, ?string $settingsSection): array
    {
        return array_filter($definitions, function ($definition) use ($settingsSection) {
            if (!empty($definition['hidden'])) {
                return false;
            }

            $group = $definition['group'] ?? 'general';

            if ($group === 'onboarding') {
                return false;
            }

            if ($settingsSection) {
                return $group === $settingsSection;
            }

            return !in_array($group, ['dashboard_widgets', 'onboarding'], true);
        });
    }

    private function resolveTargetTenantId(Request $request): ?int
    {
        $user = auth()->user();

        if ($user && $user->isGod == 1 && $request->filled('target_tenant_id')) {
            $tenantId = (int) $request->target_tenant_id;

            if (Tenants::where('id', $tenantId)->exists()) {
                return $tenantId;
            }
        }

        if ($user && $user->isGod == 1) {
            return Tenants::orderBy('id')->value('id');
        }

        return $user ? (int) ($user->tenant_id ?: $user->tenants_id) : null;
    }

    private function availableTenants()
    {
        $user = auth()->user();

        if ($user && $user->isGod == 1) {
            return Tenants::orderBy('name')->get();
        }

        $tenantId = $user ? ($user->tenant_id ?: $user->tenants_id) : null;

        return $tenantId ? Tenants::where('id', $tenantId)->get() : collect();
    }

    private function resolveTargetOrganizationId(Request $request, ?int $tenantId): ?int
    {
        if (!$tenantId || !$request->filled('target_organization_id')) {
            return null;
        }

        $organizationId = (int) $request->target_organization_id;

        return $this->availableOrganizations($tenantId)->contains('id', $organizationId) ? $organizationId : null;
    }

    private function availableOrganizations(?int $tenantId)
    {
        if (!$tenantId) {
            return collect();
        }

        return Organization::where(function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                ->orWhere('tenants_id', $tenantId);
        })->orderBy('title')->get();
    }

    private function settingsValues(?int $tenantId, array $keys): array
    {
        return Setting::query()
            ->when($tenantId, fn($query) => $query->where('tenant_id', $tenantId), fn($query) => $query->whereNull('tenant_id'))
            ->where(function ($query) use ($keys) {
                $query->whereIn('key', $keys)->orWhereIn('title', $keys);
            })
            ->get()
            ->mapWithKeys(fn($setting) => [($setting->key ?: $setting->title) => $setting->value])
            ->toArray();
    }

    private function organizationSettingsValues(?int $tenantId, int $organizationId, array $keys): array
    {
        return Setting::query()
            ->where('scope', 'organization')
            ->where('tenant_id', $tenantId)
            ->where('organization_id', $organizationId)
            ->where(function ($query) use ($keys) {
                $query->whereIn('key', $keys)->orWhereIn('title', $keys);
            })
            ->get()
            ->mapWithKeys(fn($setting) => [($setting->key ?: $setting->title) => $setting->value])
            ->toArray();
    }

    private function navigationItems(?int $tenantId)
    {
        $savedOrder = $tenantId
            ? app(SettingService::class)->get('navigation_menu_order', ['tenant_id' => $tenantId], [])
            : [];

        if (!is_array($savedOrder)) {
            $savedOrder = [];
        }

        return collect((array) config('panel_navigation.items', []))
            ->map(function ($item, $key) use ($savedOrder) {
                $defaultOrder = (int) ($item['default_order'] ?? 1000);

                return [
                    'key' => $key,
                    'title' => $item['title'] ?? $key,
                    'default_order' => $defaultOrder,
                    'order' => (int) ($savedOrder[$key] ?? $defaultOrder),
                ];
            })
            ->sortBy('order')
            ->values();
    }

    private function syncNavigationOrder(int $tenantId, array $inputOrder): void
    {
        $orders = [];

        foreach ((array) config('panel_navigation.items', []) as $key => $item) {
            $defaultOrder = (int) ($item['default_order'] ?? 1000);
            $orders[$key] = max(1, (int) ($inputOrder[$key] ?? $defaultOrder));
        }

        asort($orders, SORT_NUMERIC);

        $normalizedOrder = [];
        $position = 10;

        foreach (array_keys($orders) as $key) {
            $normalizedOrder[$key] = $position;
            $position += 10;
        }

        app(SettingService::class)->set(
            'navigation_menu_order',
            $normalizedOrder,
            ['tenant_id' => $tenantId, 'updated_by' => auth()->id()],
            'json',
            'layout'
        );
    }

    private function normalizeValue($value, array $definition): ?string
    {
        $type = $definition['type'] ?? 'text';
        $default = $definition['default'] ?? null;

        if ($type === 'boolean') {
            return in_array($value, ['yes', '1', 1, true, 'on'], true) ? 'yes' : 'no';
        }

        if ($type === 'number') {
            return is_numeric($value) ? (string) $value : (string) $default;
        }

        if ($type === 'select') {
            return array_key_exists((string) $value, $definition['options'] ?? []) ? (string) $value : (string) $default;
        }

        if ($type === 'multiselect') {
            $values = is_array($value) ? $value : [$value];
            $allowed = array_keys($definition['options'] ?? []);
            $normalized = array_values(array_intersect(array_map('strval', $values), $allowed));

            if (empty($normalized)) {
                $normalized = is_array($default) ? $default : [$default];
            }

            return json_encode(array_values(array_filter($normalized)));
        }

        return $value !== null ? trim((string) $value) : $default;
    }

    private function castSettingValueForView($value, array $definition)
    {
        $type = $definition['type'] ?? 'text';

        if ($type === 'multiselect') {
            if (is_array($value)) {
                return array_values(array_map('strval', $value));
            }

            $decoded = json_decode((string) $value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_values(array_map('strval', $decoded));
            }

            if ($value === null || $value === '') {
                $default = $definition['default'] ?? [];

                return is_array($default) ? array_values(array_map('strval', $default)) : [];
            }

            return [(string) $value];
        }

        if ($type === 'json') {
            if (is_array($value)) {
                return $value;
            }

            $decoded = json_decode((string) $value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        if ($type === 'boolean') {
            return in_array($value, ['yes', '1', 1, true, 'on'], true) ? 'yes' : 'no';
        }

        if ($type === 'number' && ($value === null || $value === '')) {
            return (string) ($definition['default'] ?? '');
        }

        return $value;
    }

    private function formatSettingDisplayValue($value, array $definition): string
    {
        $type = $definition['type'] ?? 'text';
        $options = (array) ($definition['options'] ?? []);

        if ($type === 'boolean') {
            return $value === 'yes' ? 'فعال' : 'غیرفعال';
        }

        if ($type === 'multiselect') {
            $values = is_array($value) ? $value : $this->castSettingValueForView($value, $definition);

            if (!is_array($values) || $values === []) {
                return '—';
            }

            return collect($values)
                ->map(fn ($item) => $options[(string) $item] ?? (string) $item)
                ->implode('، ');
        }

        if ($type === 'json') {
            if (is_array($value)) {
                return $value === [] ? '—' : json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            return ($value === null || $value === '') ? '—' : (string) $value;
        }

        if ($type === 'select') {
            return (string) ($options[(string) $value] ?? $value ?? '—');
        }

        if ($value === null || $value === '') {
            return '—';
        }

        return is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    private function syncTenantFeature(int $tenantId, string $key, ?string $value): void
    {
        if (!str_starts_with($key, 'feature_')) {
            return;
        }

        $feature = Feature::where('key', $key)->first();

        if (!$feature) {
            return;
        }

        TenantFeature::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'feature_id' => $feature->id,
            ],
            [
                'key' => $key,
                'is_enabled' => $value === 'yes',
                'value' => $value,
                'updated_by' => auth()->id(),
            ]
        );
    }

    private function featureModules(?int $tenantId, ?int $organizationId, array $globalSettings, array $tenantSettings)
    {
        $tenantFeatures = $tenantId
            ? TenantFeature::where('tenant_id', $tenantId)->get()->keyBy('key')
            : collect();

        $organizationFeatures = $organizationId
            ? OrganizationFeature::where('organization_id', $organizationId)->get()->keyBy('key')
            : collect();

        return Module::with(['features' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('title');
        }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function ($module) use ($globalSettings, $tenantSettings, $tenantFeatures, $organizationFeatures) {
                $module->features->transform(function ($feature) use ($globalSettings, $tenantSettings, $tenantFeatures, $organizationFeatures) {
                    $tenantFeature = $tenantFeatures->get($feature->key);
                    $organizationFeature = $organizationFeatures->get($feature->key);
                    $globalValue = $globalSettings[$feature->key] ?? $feature->default_value;
                    $tenantValue = $tenantFeature
                        ? ($tenantFeature->is_enabled ? 'yes' : 'no')
                        : ($tenantSettings[$feature->key] ?? $globalValue);
                    $organizationValue = $organizationFeature ? ($organizationFeature->is_enabled ? 'yes' : 'no') : null;

                    $feature->state = [
                        'global_value' => $globalValue,
                        'tenant_value' => $tenantValue,
                        'organization_override' => $organizationValue,
                        'effective_value' => $organizationValue ?? $tenantValue,
                        'has_tenant_override' => (bool) $tenantFeature || array_key_exists($feature->key, $tenantSettings),
                        'has_organization_override' => (bool) $organizationFeature,
                    ];

                    return $feature;
                });

                return $module;
            })
            ->filter(fn($module) => $module->features->isNotEmpty())
            ->values();
    }

    private function syncOrganizationFeatures(int $organizationId, array $inputFeatures): void
    {
        foreach ($inputFeatures as $key => $value) {
            $feature = Feature::where('key', $key)->first();

            if (!$feature) {
                continue;
            }

            if ($value === 'inherit') {
                OrganizationFeature::where('organization_id', $organizationId)
                    ->where('feature_id', $feature->id)
                    ->delete();
                continue;
            }

            $value = $value === 'yes' ? 'yes' : 'no';

            OrganizationFeature::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'feature_id' => $feature->id,
                ],
                [
                    'key' => $key,
                    'is_enabled' => $value === 'yes',
                    'value' => $value,
                    'updated_by' => auth()->id(),
                ]
            );
        }
    }

    private function syncOrganizationSettings(int $tenantId, int $organizationId, array $inputSettings, array $definitions): void
    {
        foreach ($inputSettings as $key => $payload) {
            if (!isset($definitions[$key]) || str_starts_with($key, 'feature_')) {
                continue;
            }

            if (($payload['mode'] ?? 'inherit') === 'inherit') {
                Setting::where('scope', 'organization')
                    ->where('tenant_id', $tenantId)
                    ->where('organization_id', $organizationId)
                    ->where(function ($query) use ($key) {
                        $query->where('key', $key)->orWhere('title', $key);
                    })
                    ->delete();
                continue;
            }

            $definition = $definitions[$key];
            $value = $this->normalizeValue($payload['value'] ?? null, $definition);

            Setting::updateOrCreate(
                [
                    'scope' => 'organization',
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'key' => $key,
                ],
                [
                    'title' => $key,
                    'value' => $value,
                    'category' => $definition['group'] ?? 'general',
                    'type' => $definition['type'] ?? 'string',
                    'updated_by' => auth()->id(),
                ]
            );
        }
    }
}

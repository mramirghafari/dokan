<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>{{ $pageTitle ?? 'تنظیمات پنل' }} - دکان دارمینو</title>
    <meta content="" name="description" />
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .settings-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .setting-row {
            border: 1px solid #eceef1;
            border-radius: 8px;
            padding: 14px 16px;
            min-height: 78px;
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .setting-row__control {
            width: 100%;
        }

        .setting-row__control .form-select,
        .setting-row__control .form-control,
        .setting-row__control textarea {
            width: 100%;
            min-width: 0;
        }

        .setting-row .select2-container {
            width: 100% !important;
            max-width: 100%;
        }

        .setting-row .select2-selection--multiple {
            min-height: 42px;
            padding: .35rem .5rem;
        }

        .setting-row .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
        }

        .setting-row .select2-selection--single {
            min-height: 38px;
            display: flex;
            align-items: center;
        }

        .setting-row .select2-selection__placeholder {
            color: #a8a8b0 !important;
        }

        .setting-row .form-check-input {
            width: 2.8rem;
            height: 1.45rem;
        }

        .setting-meta {
            font-size: 12px;
        }

        .feature-module-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .feature-row {
            border: 1px solid #eceef1;
            border-radius: 8px;
            padding: 14px 16px;
            min-height: 118px;
        }

        .feature-row .form-check-input {
            width: 2.8rem;
            height: 1.45rem;
        }

        .feature-state {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .5rem;
        }

        .navigation-grid {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .navigation-row {
            border: 1px solid #eceef1;
            border-radius: 8px;
            padding: 12px;
        }

        .organization-setting-override {
            border-top: 1px dashed #d9dee3;
        }

        @media (max-width: 992px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }

            .feature-module-grid {
                grid-template-columns: 1fr;
            }

            .navigation-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {
            .navigation-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4" id="tour-settings-page-title">
                            <span class="text-muted fw-light">اطلاعات پایه /</span>
                            {{ $pageTitle ?? 'تنظیمات اختصاصی پنل' }}
                        </h4>

                        <div class="card mb-4" id="tour-settings-intro">
                            <div
                                class="card-body d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
                                <div>
                                    <h5 class="mb-1">
                                        {{ $selectedTenant ? $selectedTenant->name : 'پنل انتخاب نشده' }}</h5>
                                    <small
                                        class="text-muted">{{ $pageDescription ?? 'تنظیمات این صفحه برای فعال یا غیرفعال کردن قابلیت های هر پنل استفاده می شود.' }}</small>
                                </div>
                                @if ($user->isGod == 1)
                                    <form
                                        id="tour-settings-tenant-switch"
                                        action="{{ route(match ($settingsSection ?? null) {
                                            'sales_scenario' => 'settings.salesScenario',
                                            'notification_sms' => 'settings.notifications',
                                            'dashboard_widgets' => 'settings.dashboardWidgets',
                                            default => 'settings.index',
                                        }) }}"
                                        class="d-flex gap-2 align-items-center flex-wrap" method="GET">
                                        <select class="select2 form-select" name="target_tenant_id"
                                            style="min-width: 260px;">
                                            @foreach ($tenants as $tenant)
                                                <option value="{{ $tenant->id }}"
                                                    @if ((int) $targetTenantId === (int) $tenant->id) selected @endif>
                                                    {{ $tenant->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($organizations->count())
                                            <select class="select2 form-select" name="target_organization_id"
                                                style="min-width: 260px;">
                                                <option value="">همه شعبه ها / بدون override شعبه</option>
                                                @foreach ($organizations as $organization)
                                                    <option value="{{ $organization->id }}"
                                                        @if ((int) $targetOrganizationId === (int) $organization->id) selected @endif>
                                                        {{ $organization->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                        <button class="btn btn-label-primary" type="submit">نمایش</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('settings.update') }}" method="POST">
                            @csrf
                            <input name="target_tenant_id" type="hidden" value="{{ $targetTenantId }}">
                            <input name="target_organization_id" type="hidden" value="{{ $targetOrganizationId }}">
                            @if ($settingsSection ?? null)
                                <input name="settings_section" type="hidden" value="{{ $settingsSection }}">
                            @endif

                            @if (!($settingsSection ?? null))
                                <div class="alert alert-label-info mb-4" id="tour-settings-feature-help">
                                    <div class="fw-semibold mb-1">راهنمای سریع اثر سوییچ‌ها</div>
                                    <small class="d-block">
                                        خاموش‌کردن «مسیر فروش و ویزیت روزانه» منوهای مسیرها و امکانات مسیرمحور را غیرفعال می‌کند.
                                    </small>
                                    <small class="d-block">
                                        خاموش‌کردن «مدیریت انبار و موجودی» منوهای انبار/تولید و عملیات موجودی مرتبط را غیرفعال می‌کند.
                                    </small>
                                    <small class="d-block mt-1">
                                        مسیر تغییر: <strong>اطلاعات پایه → تنظیمات پنل → کارت‌های قابلیت‌ها</strong>.
                                    </small>
                                </div>
                                @foreach ($featureModules as $module)
                                    <div class="card mb-4" @if ($loop->first) id="tour-settings-features" @endif>
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">{{ $module->title }}</h5>
                                                <small class="text-muted">مدیریت قابلیت های ماژول</small>
                                            </div>
                                            <span class="badge bg-label-primary">{{ $module->features->count() }}
                                                قابلیت</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="feature-module-grid">
                                                @foreach ($module->features as $feature)
                                                    @php
                                                        $state = $feature->state;
                                                        $featureDescription = trim(
                                                            (string) ($feature->description ?? config("panel_settings.definitions.{$feature->key}.description", '')),
                                                        );
                                                    @endphp
                                                    <div class="feature-row" id="tour-feature-{{ $feature->key }}">
                                                        <div
                                                            class="d-flex justify-content-between gap-3 align-items-start">
                                                            <div>
                                                                <label class="form-label mb-1"
                                                                    for="feature_{{ $feature->key }}">{{ $feature->title }}</label>
                                                                <div class="setting-meta text-muted">
                                                                    مقدار پایه:
                                                                    {{ $state['global_value'] === 'yes' ? 'فعال' : ($state['global_value'] === 'no' ? 'غیرفعال' : $state['global_value']) }}
                                                                </div>
                                                                @if ($featureDescription !== '')
                                                                    <div class="setting-meta text-muted mt-1">
                                                                        {{ $featureDescription }}
                                                                    </div>
                                                                @endif
                                                                <div class="feature-state">
                                                                    @if ($state['has_tenant_override'])
                                                                        <span class="badge bg-label-info">اختصاصی
                                                                            پنل</span>
                                                                    @else
                                                                        <span class="badge bg-label-secondary">ارث بری
                                                                            از
                                                                            پایه</span>
                                                                    @endif
                                                                    @if ($targetOrganizationId)
                                                                        @if ($state['has_organization_override'])
                                                                            <span
                                                                                class="badge bg-label-warning">override
                                                                                شعبه</span>
                                                                        @else
                                                                            <span class="badge bg-label-secondary">شعبه
                                                                                ارث
                                                                                بری می کند</span>
                                                                        @endif
                                                                        <span
                                                                            class="badge bg-label-{{ $state['effective_value'] === 'yes' ? 'success' : 'danger' }}">
                                                                            نتیجه شعبه:
                                                                            {{ $state['effective_value'] === 'yes' ? 'فعال' : 'غیرفعال' }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="text-nowrap d-flex flex-column gap-2 align-items-end">
                                                                <input name="settings[{{ $feature->key }}]"
                                                                    type="hidden" value="no">
                                                                <div class="form-check form-switch mb-0">
                                                                    <input class="form-check-input"
                                                                        id="feature_{{ $feature->key }}"
                                                                        name="settings[{{ $feature->key }}]"
                                                                        type="checkbox" value="yes"
                                                                        @if ($state['tenant_value'] === 'yes') checked @endif>
                                                                </div>
                                                                @if ($targetOrganizationId)
                                                                    <select class="form-select form-select-sm"
                                                                        name="organization_features[{{ $feature->key }}]"
                                                                        style="min-width: 170px;">
                                                                        <option value="inherit"
                                                                            @if (!$state['has_organization_override']) selected @endif>
                                                                            ارث بری شعبه</option>
                                                                        <option value="yes"
                                                                            @if ($state['organization_override'] === 'yes') selected @endif>
                                                                            فعال برای شعبه</option>
                                                                        <option value="no"
                                                                            @if ($state['organization_override'] === 'no') selected @endif>
                                                                            غیرفعال برای شعبه</option>
                                                                    </select>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @foreach ($settings as $groupKey => $items)
                                @php
                                    $visibleItems = collect($items)->reject(
                                        fn($settingItem, $settingKey) => str_starts_with(
                                            (string) $settingKey,
                                            'feature_',
                                        ),
                                    );
                                @endphp
                                @if ($visibleItems->isEmpty())
                                    @continue
                                @endif
                                <div class="card mb-4" id="tour-settings-group-{{ $groupKey }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">{{ $groups[$groupKey] ?? $groupKey }}</h5>
                                        <span class="badge bg-label-secondary">{{ $visibleItems->count() }}
                                            مورد</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="settings-grid">
                                            @foreach ($visibleItems as $settingKey => $setting)
                                                @php
                                                    $setting = array_merge(
                                                        [
                                                            'label' => $settingKey,
                                                            'type' => 'text',
                                                            'value' => null,
                                                            'inherited_value' => null,
                                                            'options' => [],
                                                            'has_override' => false,
                                                            'organization_value' => null,
                                                            'has_organization_override' => false,
                                                        ],
                                                        is_array($setting) ? $setting : [],
                                                    );
                                                    $organizationValue =
                                                        $setting['organization_value'] ?? $setting['value'];
                                                    $organizationSelectedValues = is_array($organizationValue)
                                                        ? $organizationValue
                                                        : [];
                                                    $settingInputValue = is_array($setting['value'] ?? null)
                                                        ? json_encode($setting['value'], JSON_UNESCAPED_UNICODE)
                                                        : ($setting['value'] ?? '');
                                                    $organizationInputValue = is_array($organizationValue)
                                                        ? json_encode($organizationValue, JSON_UNESCAPED_UNICODE)
                                                        : ($organizationValue ?? '');
                                                @endphp
                                                <div class="setting-row" id="tour-setting-{{ $settingKey }}">
                                                    <div class="setting-row__head">
                                                        <label class="form-label mb-1"
                                                            for="setting_{{ $settingKey }}">{{ $setting['label'] }}</label>
                                                        <div class="setting-meta text-muted">
                                                            مقدار پایه:
                                                            {{ $setting['inherited_display'] ?? '—' }}
                                                            @if ($setting['has_override'])
                                                                <span class="badge bg-label-info ms-2">اختصاصی
                                                                    پنل</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="setting-row__control">
                                                        @if (($setting['type'] ?? 'text') === 'boolean')
                                                            <input name="settings[{{ $settingKey }}]"
                                                                type="hidden" value="no">
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input"
                                                                    id="setting_{{ $settingKey }}"
                                                                    name="settings[{{ $settingKey }}]"
                                                                    type="checkbox" value="yes"
                                                                    @if ($setting['value'] === 'yes') checked @endif>
                                                            </div>
                                                        @elseif (($setting['type'] ?? 'text') === 'select')
                                                            <select class="form-select"
                                                                id="setting_{{ $settingKey }}"
                                                                name="settings[{{ $settingKey }}]">
                                                                @foreach ($setting['options'] ?? [] as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        @if ((string) $setting['value'] === (string) $value) selected @endif>
                                                                        {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif (($setting['type'] ?? 'text') === 'multiselect')
                                                            @php
                                                                $selectedValues = is_array($setting['value'])
                                                                    ? $setting['value']
                                                                    : [];
                                                            @endphp
                                                            <select class="form-select select2-multiselect"
                                                                id="setting_{{ $settingKey }}" multiple
                                                                name="settings[{{ $settingKey }}][]"
                                                                data-placeholder="انتخاب کنید...">
                                                                @foreach ($setting['options'] ?? [] as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        @if (in_array((string) $value, array_map('strval', $selectedValues), true)) selected @endif>
                                                                        {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif (($setting['type'] ?? 'text') === 'number')
                                                            <input class="form-control"
                                                                id="setting_{{ $settingKey }}"
                                                                name="settings[{{ $settingKey }}]"
                                                                step="0.01" type="number"
                                                                value="{{ $setting['value'] ?? ($setting['default'] ?? '') }}">
                                                        @elseif (($setting['type'] ?? 'text') === 'textarea')
                                                            <textarea class="form-control" id="setting_{{ $settingKey }}" name="settings[{{ $settingKey }}]"
                                                                rows="3">{{ $settingInputValue }}</textarea>
                                                        @elseif (($setting['type'] ?? 'text') === 'json')
                                                            <textarea class="form-control font-monospace" id="setting_{{ $settingKey }}"
                                                                name="settings[{{ $settingKey }}]" rows="4" readonly>{{ $settingInputValue }}</textarea>
                                                            <small class="text-muted d-block mt-1">این مقدار از صفحه مربوطه تنظیم می‌شود و اینجا فقط نمایشی است.</small>
                                                        @else
                                                            <input class="form-control"
                                                                id="setting_{{ $settingKey }}"
                                                                name="settings[{{ $settingKey }}]"
                                                                type="text" value="{{ $settingInputValue }}">
                                                        @endif
                                                    </div>
                                                    @if ($targetOrganizationId)
                                                        <div class="organization-setting-override mt-3 pt-3">
                                                            <div
                                                                class="d-flex justify-content-between gap-3 align-items-center flex-wrap">
                                                                <div>
                                                                    <strong class="small">تنظیم اختصاصی شعبه</strong>
                                                                    <div class="setting-meta text-muted">
                                                                        {{ $selectedOrganization ? $selectedOrganization->title : 'شعبه انتخاب شده' }}
                                                                        @if ($setting['has_organization_override'])
                                                                            <span
                                                                                class="badge bg-label-warning ms-2">override
                                                                                شعبه</span>
                                                                        @else
                                                                            <span
                                                                                class="badge bg-label-secondary ms-2">ارث
                                                                                بری از پنل</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <select class="form-select form-select-sm"
                                                                    name="organization_settings[{{ $settingKey }}][mode]"
                                                                    style="min-width: 170px;">
                                                                    <option value="inherit"
                                                                        @if (!$setting['has_organization_override']) selected @endif>
                                                                        ارث بری از پنل</option>
                                                                    <option value="override"
                                                                        @if ($setting['has_organization_override']) selected @endif>
                                                                        مقدار اختصاصی شعبه</option>
                                                                </select>
                                                            </div>
                                                            <div class="mt-2">
                                                                @if (($setting['type'] ?? 'text') === 'boolean')
                                                                    <input
                                                                        name="organization_settings[{{ $settingKey }}][value]"
                                                                        type="hidden" value="no">
                                                                    <div class="form-check form-switch mb-0">
                                                                        <input class="form-check-input"
                                                                            id="organization_setting_{{ $settingKey }}"
                                                                            name="organization_settings[{{ $settingKey }}][value]"
                                                                            type="checkbox" value="yes"
                                                                            @if ($organizationValue === 'yes') checked @endif>
                                                                    </div>
                                                                @elseif (($setting['type'] ?? 'text') === 'select')
                                                                    <select class="form-select"
                                                                        id="organization_setting_{{ $settingKey }}"
                                                                        name="organization_settings[{{ $settingKey }}][value]">
                                                                        @foreach ($setting['options'] ?? [] as $value => $label)
                                                                            <option value="{{ $value }}"
                                                                                @if ((string) $organizationValue === (string) $value) selected @endif>
                                                                                {{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @elseif (($setting['type'] ?? 'text') === 'multiselect')
                                                                    <select class="form-select select2-multiselect"
                                                                        id="organization_setting_{{ $settingKey }}"
                                                                        multiple
                                                                        name="organization_settings[{{ $settingKey }}][value][]"
                                                                        data-placeholder="انتخاب کنید...">
                                                                        @foreach ($setting['options'] ?? [] as $value => $label)
                                                                            <option value="{{ $value }}"
                                                                                @if (in_array((string) $value, array_map('strval', $organizationSelectedValues), true)) selected @endif>
                                                                                {{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @elseif (($setting['type'] ?? 'text') === 'number')
                                                                    <input class="form-control"
                                                                        id="organization_setting_{{ $settingKey }}"
                                                                        name="organization_settings[{{ $settingKey }}][value]"
                                                                        step="0.01" type="number"
                                                                        value="{{ $organizationValue }}">
                                                                @elseif (($setting['type'] ?? 'text') === 'textarea')
                                                                    <textarea class="form-control" id="organization_setting_{{ $settingKey }}"
                                                                        name="organization_settings[{{ $settingKey }}][value]" rows="3">{{ $organizationInputValue }}</textarea>
                                                                @elseif (($setting['type'] ?? 'text') === 'json')
                                                                    <textarea class="form-control font-monospace" id="organization_setting_{{ $settingKey }}"
                                                                        rows="4" readonly>{{ $organizationInputValue }}</textarea>
                                                                @else
                                                                    <input class="form-control"
                                                                        id="organization_setting_{{ $settingKey }}"
                                                                        name="organization_settings[{{ $settingKey }}][value]"
                                                                        type="text"
                                                                        value="{{ $organizationInputValue }}">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if (!($settingsSection ?? null))
                                <div class="card mb-4" id="tour-settings-navigation">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">چینش منوی پنل</h5>
                                            <small class="text-muted">عدد کوچکتر بالاتر نمایش داده می‌شود؛ بعد از ذخیره،
                                                ترتیب از اول تا آخر مرتب‌سازی می‌شود.</small>
                                        </div>
                                        <span class="badge bg-label-primary">{{ $navigationItems->count() }}
                                            آیتم</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="navigation-grid">
                                            @foreach ($navigationItems as $navigationItem)
                                                <div class="navigation-row">
                                                    <label class="form-label mb-2"
                                                        for="navigation_{{ $navigationItem['key'] }}">{{ $navigationItem['title'] }}</label>
                                                    <input class="form-control"
                                                        id="navigation_{{ $navigationItem['key'] }}" min="1"
                                                        name="navigation_order[{{ $navigationItem['key'] }}]"
                                                        step="1" type="number"
                                                        value="{{ $navigationItem['order'] }}">
                                                    <small class="text-muted">پیش‌فرض:
                                                        {{ $navigationItem['default_order'] }}</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="card" id="tour-settings-save">
                                <div class="card-body d-flex justify-content-end gap-2">
                                    <button class="btn btn-primary" type="submit">
                                        <x-ui.icon name="device-floppy" class="me-1" />
                                        ذخیره تنظیمات پنل
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @include('sections.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $(function() {
            $('.select2-multiselect').each(function() {
                $(this).select2({
                    dir: 'rtl',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'انتخاب کنید...',
                    closeOnSelect: false,
                });
            });
        });
    </script>
</body>

</html>

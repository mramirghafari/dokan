<?php use Hekmatinasser\Verta\Verta; ?>
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer"
    data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport" />
    <title>داشبورد مدیر کل پنل — دارمینو ERP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        :root {
            --pm-primary:   #7367f0;
            --pm-success:   #28c76f;
            --pm-warning:   #ff9f43;
            --pm-danger:    #ea5455;
            --pm-info:      #00cfe8;
            --pm-secondary: #82868b;
        }

        /* ── کارت‌های آمار ── */
        .pm-stat-card {
            border-radius: 12px;
            transition: transform .18s, box-shadow .18s;
            border: none;
            overflow: hidden;
        }
        .pm-stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,.12); }
        .pm-stat-icon {
            width: 54px; height: 54px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; flex-shrink: 0;
        }
        .pm-stat-label  { font-size: .78rem; color: #9b9bbb; }
        .pm-stat-value  { font-size: 1.65rem; font-weight: 700; line-height: 1.2; }
        .pm-stat-sub    { font-size: .78rem; }
        .pm-trend-up    { color: var(--pm-success); }
        .pm-trend-down  { color: var(--pm-danger); }
        .pm-trend-flat  { color: var(--pm-secondary); }

        /* ── هدر ویجت ── */
        .pm-widget-header {
            display: flex; align-items: center; gap: .5rem;
            padding: .9rem 1.25rem; border-bottom: 1px solid rgba(0,0,0,.06);
            font-weight: 600; font-size: .95rem;
        }
        .pm-widget-icon { width: 30px; height: 30px; border-radius: 8px;
            display:flex; align-items:center; justify-content:center; font-size:.9rem; }

        /* ── جدول تیم ── */
        .team-row-leader td { background: rgba(115,103,240,.06); font-weight: 600; }
        .team-row-visitor td { padding-right: 2.5rem !important; font-size: .87rem; }
        .team-toggle { cursor: pointer; }
        .team-visitors { display:none; }
        .team-visitors.show { display:table-row-group; }

        /* ── Activity Log timeline ── */
        .activity-timeline { position: relative; padding-right: 1.4rem; }
        .activity-timeline::before {
            content: ''; position: absolute; right: .5rem; top: 0; bottom: 0;
            width: 2px; background: rgba(115,103,240,.15);
        }
        .activity-item {
            position: relative; padding: .5rem 0 .5rem 0; margin-bottom: .2rem;
        }
        .activity-dot {
            position: absolute; right: -1.05rem; top: .55rem;
            width: 10px; height: 10px; border-radius: 50%;
            border: 2px solid #fff; box-shadow: 0 0 0 2px rgba(115,103,240,.3);
        }
        .activity-meta { font-size: .75rem; color: #9b9bbb; }

        /* ── responsive ── */
        @media (max-width:768px) {
            .pm-stat-value { font-size: 1.25rem; }
        }

        /* ── loading skeleton ── */
        .pm-skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 6px; }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    </style>
</head>

@php
    $Organ = DB::table('organizations')->where('id', auth()->user()->organization_id)->first();
    $numberFmt = fn($n) => number_format((int)$n);
@endphp

<body>
@include('sweetalert::alert')
<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
    @include('sections/sidebar')
    <div class="layout-page">
        @include('sections/header')
        <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">

                {{-- ══════════════════════════════════════
                     هدر داشبورد
                ══════════════════════════════════════ --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <span class="text-muted fw-normal ms-2">داشبورد</span>
                            مدیر کل پنل
                        </h4>
                        <small class="text-muted">
                            {{ Verta::now()->format('l، j F Y') }}
                            &nbsp;·&nbsp;
                            {{ $Organ->title ?? '' }}
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('settings.dashboardWidgets') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-settings me-1"></i> تنظیمات ویجت‌ها
                        </a>
                        <a href="{{ route('index') }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-layout-dashboard me-1"></i> داشبورد اصلی
                        </a>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                     ردیف ۱ — کارت‌های آمار کلی
                ══════════════════════════════════════ --}}
                @if (!empty($statCards) && ($dashWidgets['dashboard_widget_pm_stat_cards'] ?? true))
                <div class="row g-3 mb-4">
                    {{-- مشتریان --}}
                    <div class="col-6 col-md-3">
                        <div class="card pm-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <div class="pm-stat-icon" style="background:rgba(115,103,240,.15);">
                                    <i class="ti ti-users" style="color:var(--pm-primary)"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="pm-stat-label text-truncate">مشتریان فعال</div>
                                    <div class="pm-stat-value">{{ $numberFmt($statCards['active_customers']) }}</div>
                                    <div class="pm-stat-sub text-muted">از {{ $numberFmt($statCards['total_customers']) }} کل</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- فاکتورهای این ماه --}}
                    <div class="col-6 col-md-3">
                        <div class="card pm-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <div class="pm-stat-icon" style="background:rgba(40,199,111,.15);">
                                    <i class="ti ti-receipt" style="color:var(--pm-success)"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="pm-stat-label text-truncate">فاکتور این ماه</div>
                                    <div class="pm-stat-value">{{ $numberFmt($statCards['this_month_factors']) }}</div>
                                    <div class="pm-stat-sub @if($statCards['sales_trend'] >= 0) pm-trend-up @else pm-trend-down @endif">
                                        @if($statCards['sales_trend'] >= 0)<i class="ti ti-trending-up"></i>@else<i class="ti ti-trending-down"></i>@endif
                                        {{ abs($statCards['sales_trend']) }}٪ نسبت به ماه قبل
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- فروش این ماه --}}
                    <div class="col-6 col-md-3">
                        <div class="card pm-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <div class="pm-stat-icon" style="background:rgba(255,159,67,.15);">
                                    <i class="ti ti-currency-dollar" style="color:var(--pm-warning)"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="pm-stat-label text-truncate">فروش این ماه</div>
                                    <div class="pm-stat-value" style="font-size:1.2rem">
                                        {{ number_format((int)$statCards['this_month_amount'] / 10) }}
                                        <small class="fw-normal fs-6">ت</small>
                                    </div>
                                    <div class="pm-stat-sub text-muted">
                                        ماه قبل: {{ number_format((int)$statCards['last_month_amount'] / 10) }} ت
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارمندان --}}
                    <div class="col-6 col-md-3">
                        <div class="card pm-stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <div class="pm-stat-icon" style="background:rgba(0,207,232,.15);">
                                    <i class="ti ti-user-check" style="color:var(--pm-info)"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="pm-stat-label text-truncate">کارمندان فعال</div>
                                    <div class="pm-stat-value">{{ $numberFmt($statCards['active_employees']) }}</div>
                                    @if($statCards['warehouse_stock'] > 0)
                                    <div class="pm-stat-sub text-muted">موجودی انبار: {{ $numberFmt($statCards['warehouse_stock']) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ══════════════════════════════════════
                     ردیف ۲ — تیم فروش + نمودار ماهانه
                ══════════════════════════════════════ --}}
                <div class="row g-3 mb-4">

                    {{-- تیم فروش --}}
                    @if (!empty($salesTeam) && ($dashWidgets['dashboard_widget_pm_sales_team'] ?? true))
                    <div class="col-12 col-lg-6">
                        <div class="card h-100">
                            <div class="pm-widget-header">
                                <div class="pm-widget-icon" style="background:rgba(115,103,240,.15);">
                                    <i class="ti ti-hierarchy" style="color:var(--pm-primary)"></i>
                                </div>
                                سلسله مراتب تیم فروش
                            </div>
                            <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                                <table class="table table-hover mb-0" style="font-size:.88rem">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>نام</th>
                                            <th class="text-center">مشتری</th>
                                            <th class="text-center">فاکتور</th>
                                            <th class="text-center">این ماه (ت)</th>
                                            <th class="text-center">روند</th>
                                        </tr>
                                    </thead>
                                    @foreach($salesTeam as $leader)
                                    <tbody>
                                        <tr class="team-row-leader team-toggle" data-target="visitors-{{ $leader['id'] }}" style="cursor:pointer">
                                            <td>
                                                <span class="badge bg-label-primary me-1" style="font-size:.7rem">سرپرست</span>
                                                {{ $leader['name'] }}
                                                @if(!empty($leader['visitors']))
                                                <i class="ti ti-chevron-down ms-1 small"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($leader['customer_count']) }}</td>
                                            <td class="text-center">{{ number_format($leader['factor_count']) }}</td>
                                            <td class="text-center">{{ number_format((int)$leader['this_month_amount'] / 10) }}</td>
                                            <td class="text-center">
                                                @if($leader['trend'] > 0)
                                                    <span class="pm-trend-up"><i class="ti ti-trending-up"></i> {{ $leader['trend'] }}٪</span>
                                                @elseif($leader['trend'] < 0)
                                                    <span class="pm-trend-down"><i class="ti ti-trending-down"></i> {{ abs($leader['trend']) }}٪</span>
                                                @else
                                                    <span class="pm-trend-flat">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                    @if(!empty($leader['visitors']))
                                    <tbody class="team-visitors" id="visitors-{{ $leader['id'] }}">
                                        @foreach($leader['visitors'] as $visitor)
                                        <tr class="team-row-visitor">
                                            <td>
                                                <span class="badge bg-label-secondary me-1" style="font-size:.68rem">ویزیتور</span>
                                                {{ $visitor['name'] }}
                                                @if(!$visitor['is_active'])
                                                    <span class="badge bg-label-danger ms-1" style="font-size:.65rem">غیرفعال</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($visitor['customer_count']) }}</td>
                                            <td class="text-center">{{ number_format($visitor['factor_count']) }}</td>
                                            <td class="text-center">{{ number_format((int)$visitor['this_month_amount'] / 10) }}</td>
                                            <td class="text-center">
                                                @if($visitor['trend'] > 0)
                                                    <span class="pm-trend-up" style="font-size:.8rem"><i class="ti ti-trending-up"></i> {{ $visitor['trend'] }}٪</span>
                                                @elseif($visitor['trend'] < 0)
                                                    <span class="pm-trend-down" style="font-size:.8rem"><i class="ti ti-trending-down"></i> {{ abs($visitor['trend']) }}٪</span>
                                                @else
                                                    <span class="pm-trend-flat">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    @endif
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- نمودار فروش ماهانه --}}
                    @if (!empty($monthlyChart) && ($dashWidgets['dashboard_widget_pm_bi_chart'] ?? true))
                    <div class="col-12 col-lg-6">
                        <div class="card h-100">
                            <div class="pm-widget-header">
                                <div class="pm-widget-icon" style="background:rgba(40,199,111,.15);">
                                    <i class="ti ti-chart-bar" style="color:var(--pm-success)"></i>
                                </div>
                                نمودار فروش ۶ ماه اخیر
                            </div>
                            <div class="card-body">
                                <div id="monthlyChart" style="min-height:300px"></div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ══════════════════════════════════════
                     ردیف ۳ — خلاصه مالی + محصولات + مناطق
                ══════════════════════════════════════ --}}
                <div class="row g-3 mb-4">

                    {{-- خلاصه مالی --}}
                    @if (!empty($financialSummary) && ($dashWidgets['dashboard_widget_pm_financial'] ?? true))
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="pm-widget-header">
                                <div class="pm-widget-icon" style="background:rgba(255,159,67,.15);">
                                    <i class="ti ti-report-money" style="color:var(--pm-warning)"></i>
                                </div>
                                خلاصه مالی
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span class="text-muted small">درآمد این ماه</span>
                                    <strong class="pm-trend-up">
                                        {{ number_format((int)$financialSummary['this_month_revenue'] / 10) }} ت
                                    </strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span class="text-muted small">درآمد ماه قبل</span>
                                    <span>{{ number_format((int)$financialSummary['last_month_revenue'] / 10) }} ت</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span class="text-muted small">روند فروش</span>
                                    <span class="{{ $financialSummary['revenue_trend'] >= 0 ? 'pm-trend-up' : 'pm-trend-down' }}">
                                        @if($financialSummary['revenue_trend'] >= 0)
                                            <i class="ti ti-trending-up"></i>
                                        @else
                                            <i class="ti ti-trending-down"></i>
                                        @endif
                                        {{ abs($financialSummary['revenue_trend']) }}٪
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span class="text-muted small">فاکتورهای در انتظار</span>
                                    <span class="badge bg-label-warning">{{ number_format($financialSummary['pending_factors']) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span class="text-muted small">مبلغ در انتظار</span>
                                    <span>{{ number_format((int)$financialSummary['pending_amount'] / 10) }} ت</span>
                                </div>
                                @if($financialSummary['receivable'] > 0 || $financialSummary['payable'] > 0)
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                    <span class="text-muted small">بدهکاران</span>
                                    <span class="pm-trend-up">{{ number_format((int)$financialSummary['receivable'] / 10) }} ت</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">طلبکاران</span>
                                    <span class="pm-trend-down">{{ number_format((int)$financialSummary['payable'] / 10) }} ت</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- محصولات و موجودی --}}
                    @if (!empty($productSummary) && ($dashWidgets['dashboard_widget_pm_products'] ?? true))
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="pm-widget-header">
                                <div class="pm-widget-icon" style="background:rgba(0,207,232,.15);">
                                    <i class="ti ti-package" style="color:var(--pm-info)"></i>
                                </div>
                                محصولات و موجودی
                            </div>
                            <div class="card-body p-0">
                                <div class="px-3 py-2 border-bottom">
                                    <small class="text-muted fw-semibold d-block mb-1">پرفروش‌ترین محصولات (۳۰ روز)</small>
                                    @forelse($productSummary['top_products'] as $idx => $prod)
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-label-primary rounded-circle" style="width:20px;height:20px;font-size:.7rem">{{ $idx + 1 }}</span>
                                            <span style="font-size:.83rem">{{ Str::limit($prod['product_name'], 22) }}</span>
                                        </div>
                                        <span class="badge bg-label-success">{{ number_format($prod['total_qty']) }}</span>
                                    </div>
                                    @empty
                                    <small class="text-muted">داده‌ای یافت نشد</small>
                                    @endforelse
                                </div>
                                @if($productSummary['low_stock_products']->count() > 0)
                                <div class="px-3 py-2">
                                    <small class="text-muted fw-semibold d-block mb-1">کم‌موجودترین محصولات</small>
                                    @foreach($productSummary['low_stock_products']->take(4) as $prod)
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span style="font-size:.83rem">{{ Str::limit($prod->name, 22) }}</span>
                                        <span class="badge bg-label-danger">{{ number_format($prod->quantity) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                <div class="px-3 py-2 border-top bg-light-subtle">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">کل محصولات</small>
                                        <strong>{{ number_format($productSummary['total_products']) }}</strong>
                                    </div>
                                    @if($productSummary['total_stock_items'] > 0)
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">کل موجودی انبار</small>
                                        <strong>{{ number_format($productSummary['total_stock_items']) }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- مناطق و مسیرها --}}
                    @if (!empty($routesSummary) && ($routesSummary['enabled'] ?? false) && ($dashWidgets['dashboard_widget_pm_routes'] ?? true))
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="pm-widget-header">
                                <div class="pm-widget-icon" style="background:rgba(234,84,85,.15);">
                                    <i class="ti ti-map-pin" style="color:var(--pm-danger)"></i>
                                </div>
                                مناطق و مسیرها
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="text-center p-2 rounded" style="background:rgba(234,84,85,.08)">
                                            <div style="font-size:1.6rem;font-weight:700;color:var(--pm-danger)">{{ $routesSummary['active_regions'] }}</div>
                                            <small class="text-muted">منطقه فعال</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 rounded" style="background:rgba(115,103,240,.08)">
                                            <div style="font-size:1.6rem;font-weight:700;color:var(--pm-primary)">{{ $routesSummary['total_areas'] }}</div>
                                            <small class="text-muted">مسیر کل</small>
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($routesSummary['customers_per_region']) && count($routesSummary['customers_per_region']) > 0)
                                <small class="text-muted fw-semibold d-block mb-2">توزیع مشتریان بر اساس منطقه</small>
                                @foreach($routesSummary['customers_per_region'] as $region)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>{{ $region['title'] }}</small>
                                        <small class="fw-semibold">{{ number_format($region['count']) }}</small>
                                    </div>
                                    @php
                                        $maxCount = max(collect($routesSummary['customers_per_region'])->pluck('count')->toArray() ?: [1]);
                                        $pct = $maxCount > 0 ? min(100, round($region['count'] / $maxCount * 100)) : 0;
                                    @endphp
                                    <div class="progress" style="height:6px">
                                        <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ══════════════════════════════════════
                     ردیف ۴ — لاگ فعالیت (عرض کامل)
                ══════════════════════════════════════ --}}
                @if (!empty($activityLog) && ($dashWidgets['dashboard_widget_pm_activity_log'] ?? true))
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="pm-widget-header d-flex justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="pm-widget-icon" style="background:rgba(130,134,139,.15);">
                                        <i class="ti ti-activity" style="color:var(--pm-secondary)"></i>
                                    </div>
                                    لاگ فعالیت سیستم
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" id="activitySearch" class="form-control form-control-sm"
                                           placeholder="جستجو در لاگ..." style="width:200px">
                                    <select id="activityFilter" class="form-select form-select-sm" style="width:130px">
                                        <option value="">همه عملیات</option>
                                        <option value="create">ایجاد</option>
                                        <option value="update">ویرایش</option>
                                        <option value="delete">حذف</option>
                                        <option value="login">ورود</option>
                                        <option value="approve">تایید</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="activity-timeline" id="activityList">
                                    @forelse($activityLog as $log)
                                    <div class="activity-item"
                                         data-action="{{ $log['action'] }}"
                                         data-search="{{ strtolower($log['user_name'] . ' ' . $log['description']) }}">
                                        <div class="activity-dot bg-{{ $log['action_color'] }}"></div>
                                        <div class="d-flex flex-wrap gap-2 align-items-start">
                                            <span class="badge bg-label-{{ $log['action_color'] }}" style="font-size:.72rem;min-width:46px;text-align:center">
                                                {{ $log['action_label'] }}
                                            </span>
                                            <span class="fw-semibold" style="font-size:.85rem">{{ $log['user_name'] }}</span>
                                            @if($log['description'])
                                            <span class="text-muted" style="font-size:.83rem">—</span>
                                            <span style="font-size:.83rem">{{ $log['description'] }}</span>
                                            @endif
                                            <span class="activity-meta me-auto">{{ $log['time_ago'] }}</span>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="ti ti-clock-off" style="font-size:2rem"></i>
                                        <p class="mt-2">رویدادی یافت نشد</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            {{-- /container --}}
            @include('sections/footer')
        </div>
    </div>
</div>
</div>

{{-- ══════════════════════════════════════
     اسکریپت‌ها
══════════════════════════════════════ --}}
<script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<script src="{{ asset('assets/') }}/js/main.js"></script>

<script>
(function () {
    'use strict';

    // ── Toggle سطرهای ویزیتور ──
    document.querySelectorAll('.team-toggle').forEach(function (row) {
        row.addEventListener('click', function () {
            var targetId = this.getAttribute('data-target');
            var target   = document.getElementById(targetId);
            if (target) {
                target.classList.toggle('show');
                var icon = this.querySelector('.ti-chevron-down, .ti-chevron-up');
                if (icon) {
                    icon.classList.toggle('ti-chevron-down');
                    icon.classList.toggle('ti-chevron-up');
                }
            }
        });
    });

    // ── جستجو و فیلتر لاگ ──
    var searchInput  = document.getElementById('activitySearch');
    var filterSelect = document.getElementById('activityFilter');

    function filterLogs() {
        var q      = (searchInput ? searchInput.value.toLowerCase() : '');
        var action = (filterSelect ? filterSelect.value.toLowerCase() : '');

        document.querySelectorAll('#activityList .activity-item').forEach(function (item) {
            var itemAction = item.getAttribute('data-action') || '';
            var itemSearch = item.getAttribute('data-search') || '';
            var matchSearch = !q || itemSearch.includes(q);
            var matchAction = !action || itemAction === action;
            item.style.display = (matchSearch && matchAction) ? '' : 'none';
        });
    }

    if (searchInput)  searchInput.addEventListener('input', filterLogs);
    if (filterSelect) filterSelect.addEventListener('change', filterLogs);

    // ── نمودار فروش ماهانه ──
    @if (!empty($monthlyChart))
    var chartLabels  = @json($monthlyChart['labels']);
    var chartAmounts = @json($monthlyChart['amounts']);
    var chartAmountsToman = chartAmounts.map(function(v){ return Math.round(v / 10); });

    var isDark   = document.documentElement.classList.contains('dark-style');
    var textColor = isDark ? '#cfd3ec' : '#697a8d';
    var gridColor = isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.06)';

    var options = {
        series: [{
            name: 'فروش (تومان)',
            data: chartAmountsToman
        }],
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false },
            fontFamily: 'inherit'
        },
        plotOptions: {
            bar: { borderRadius: 6, columnWidth: '50%' }
        },
        colors: ['#7367f0'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: .3,
                opacityFrom: .9,
                opacityTo: .6
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: chartLabels,
            axisBorder: { show: false },
            axisTicks:  { show: false },
            labels: { style: { colors: textColor, fontFamily: 'inherit' } }
        },
        yaxis: {
            labels: {
                style: { colors: textColor, fontFamily: 'inherit' },
                formatter: function(val) {
                    if (val >= 1000000) return (val/1000000).toFixed(1) + 'م';
                    if (val >= 1000)    return (val/1000).toFixed(0) + 'ه';
                    return val;
                }
            }
        },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val.toLocaleString('fa-IR') + ' تومان';
                }
            }
        }
    };

    var chartEl = document.getElementById('monthlyChart');
    if (chartEl) {
        var chart = new ApexCharts(chartEl, options);
        chart.render();
    }
    @endif

})();
</script>

</body>
</html>

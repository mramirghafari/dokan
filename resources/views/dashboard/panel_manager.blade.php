<?php use Hekmatinasser\Verta\Verta; ?>
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer"
    data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport" />
    <title>داشبورد مدیر پنل — دارمینو ERP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        /* ── تایپوگرافی و پالت رنگ ── */
        :root {
            --pm-primary:   #7367f0;
            --pm-success:   #28c76f;
            --pm-warning:   #ff9f43;
            --pm-danger:    #ea5455;
            --pm-info:      #00cfe8;
            --pm-secondary: #82868b;
        }

        /* ── کارت‌های ویلکام ── */
        .welcome-card-img {
            position: absolute;
            bottom: 0;
            left: 1rem;
            width: 140px;
            opacity: .9;
        }
        @media (max-width: 575px) { .welcome-card-img { display: none; } }

        /* ── کارت‌های آمار sparkline ── */
        .stat-card .card-body { padding: 1rem 1.25rem .5rem; }
        .stat-card .stat-icon {
            width: 42px; height: 42px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0;
        }
        .stat-card .stat-val { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
        .stat-card .stat-lbl { font-size: .78rem; color: #9b9bbb; }
        .stat-card .stat-sub { font-size: .77rem; }
        .trend-up   { color: var(--pm-success); }
        .trend-down { color: var(--pm-danger); }
        .trend-flat { color: var(--pm-secondary); }

        /* ── Revenue chart wrapper ── */
        .revenue-split { border-right: 1px solid rgba(0,0,0,.06); }
        @media (max-width: 767px) { .revenue-split { border-right: none; border-bottom: 1px solid rgba(0,0,0,.06); } }

        /* ── Activity timeline ── */
        .activity-timeline { position: relative; padding-right: 1.4rem; }
        .activity-timeline::before {
            content: ''; position: absolute; right: .55rem; top: 0; bottom: 0;
            width: 2px; background: rgba(115,103,240,.12);
        }
        .activity-item { position: relative; padding: .45rem 0; }
        .activity-dot {
            position: absolute; right: -1.05rem; top: .55rem;
            width: 10px; height: 10px; border-radius: 50%;
            border: 2px solid #fff; box-shadow: 0 0 0 2px rgba(115,103,240,.25);
        }
        .activity-meta { font-size: .74rem; color: #9b9bbb; }

        /* ── Team table ── */
        .team-row-leader td { background: rgba(115,103,240,.05); font-weight: 600; }
        .team-row-visitor td { padding-right: 2.5rem !important; font-size: .86rem; }
        .team-visitors { display: none; }
        .team-visitors.show { display: table-row-group; }
        .team-toggle { cursor: pointer; }

        /* ── Progress bars ── */
        .product-progress { height: 6px; border-radius: 4px; }

        /* ── Skeleton ── */
        .pm-skeleton {
            background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
            background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 6px;
        }
        @keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

        /* ── Last-update badge ── */
        .widget-updated { font-size: .7rem; color: #9b9bbb; }

        @media (max-width: 768px) {
            .stat-card .stat-val { font-size: 1.15rem; }
        }
    </style>
</head>

@php
    try {
        $rawOrgId = auth()->user()->organization_id ?? 0;
        if (is_string($rawOrgId)) {
            $decoded = json_decode($rawOrgId, true);
            $rawOrgId = (is_array($decoded) && !empty($decoded[0])) ? (int)$decoded[0] : (int)$rawOrgId;
        }
        $Organ = DB::table('organizations')->where('id', (int)$rawOrgId)->first();
    } catch (\Exception $e) {
        $Organ = null;
    }
    $nf      = fn($n) => number_format((int)$n);
    $nfToman = fn($n) => number_format((int)($n / 10));
    try {
        $updatedAt = \Hekmatinasser\Verta\Verta::now()->format('H:i');
    } catch (\Exception $e) {
        $updatedAt = now()->format('H:i');
    }
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
                     هدر صفحه
                ══════════════════════════════════════ --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <span class="text-muted fw-normal ms-2">داشبورد</span>مدیر پنل
                        </h4>
                        <small class="text-muted">
                            {{ Verta::now()->format('l، j F Y') }}
                            &nbsp;·&nbsp;{{ $Organ->title ?? '' }}
                            &nbsp;·&nbsp;<span class="widget-updated">بروزرسانی: {{ $updatedAt }}</span>
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('settings.dashboardWidgets') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-settings me-1"></i> تنظیمات ویجت‌ها
                        </a>
                        <a href="{{ route('index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-layout-dashboard me-1"></i> داشبورد اصلی
                        </a>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                     ردیف ۱ — کارت خوش‌آمد + ۴ آمار sparkline
                ══════════════════════════════════════ --}}
                <div class="row g-4 mb-4">

                    {{-- ستون ۱: کارت خوش‌آمد --}}
                    <div class="col-12 col-xl-4">
                        <div class="card h-100" style="background: linear-gradient(135deg,rgba(115,103,240,.15) 0%,rgba(115,103,240,.02) 100%); overflow:hidden; position:relative;">
                            <div class="card-body" style="min-height:160px;">
                                <h5 class="card-title fw-bold mb-1">
                                    تبریک 🎉 {{ $User->name }}!
                                </h5>
                                <p class="text-muted mb-3" style="font-size:.88rem">
                                    @if(!empty($statCards))
                                        این ماه <strong class="text-primary">{{ $nf($statCards['this_month_factors']) }}</strong> فاکتور
                                        به ارزش
                                        <strong class="text-success">{{ $nfToman($statCards['this_month_amount']) }}</strong> تومان
                                        ثبت شده است.
                                    @else
                                        به داشبورد مدیریت پنل {{ $Organ->title ?? 'دارمینو' }} خوش آمدید.
                                    @endif
                                </p>
                                <a href="{{ route('index') }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-chart-bar me-1"></i> مشاهده گزارش فروش
                                </a>
                                <a href="{{ route('pishfactor.index') }}" class="btn btn-outline-secondary btn-sm ms-2">
                                    <i class="ti ti-file-plus me-1"></i> ثبت سفارش
                                </a>
                            </div>
                            <img src="{{ asset('assets/') }}/img/illustrations/card-advance-sale.png"
                                 alt="" class="welcome-card-img" style="width:120px;" onerror="this.style.display='none'">
                        </div>
                    </div>

                    @if(!empty($statCards))
                    {{-- کارت: مشتریان فعال --}}
                    <div class="col-6 col-md-3 col-xl-2">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="stat-icon bg-label-primary">
                                        <i class="ti ti-users text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="stat-lbl text-truncate">مشتریان فعال</div>
                                        <div class="stat-val">{{ $nf($statCards['active_customers']) }}</div>
                                    </div>
                                </div>
                                <div id="sparkCustomers" style="min-height:50px"></div>
                                <div class="stat-sub text-muted mt-1">از {{ $nf($statCards['total_customers']) }} کل</div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت: فاکتورهای این ماه --}}
                    <div class="col-6 col-md-3 col-xl-2">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="stat-icon bg-label-success">
                                        <i class="ti ti-receipt text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="stat-lbl text-truncate">فاکتور این ماه</div>
                                        <div class="stat-val">{{ $nf($statCards['this_month_factors']) }}</div>
                                    </div>
                                </div>
                                <div id="sparkFactors" style="min-height:50px"></div>
                                <div class="stat-sub mt-1 @if(($statCards['sales_trend'] ?? 0) >= 0) trend-up @else trend-down @endif">
                                    @if(($statCards['sales_trend'] ?? 0) >= 0)
                                        <i class="ti ti-trending-up"></i>
                                    @else
                                        <i class="ti ti-trending-down"></i>
                                    @endif
                                    {{ abs($statCards['sales_trend'] ?? 0) }}٪ نسبت به ماه قبل
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت: فروش این ماه --}}
                    <div class="col-6 col-md-3 col-xl-2">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="stat-icon bg-label-warning">
                                        <i class="ti ti-coin text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="stat-lbl text-truncate">فروش این ماه</div>
                                        <div class="stat-val" style="font-size:1.1rem">
                                            {{ $nfToman($statCards['this_month_amount']) }}
                                            <small class="fw-normal" style="font-size:.7rem">ت</small>
                                        </div>
                                    </div>
                                </div>
                                <div id="sparkSales" style="min-height:50px"></div>
                                <div class="stat-sub text-muted mt-1">
                                    ماه قبل: {{ $nfToman($statCards['last_month_amount']) }} ت
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت: کارمندان --}}
                    <div class="col-6 col-md-3 col-xl-2">
                        <div class="card stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="stat-icon bg-label-info">
                                        <i class="ti ti-user-check text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="stat-lbl text-truncate">کارمندان فعال</div>
                                        <div class="stat-val">{{ $nf($statCards['active_employees']) }}</div>
                                    </div>
                                </div>
                                <div id="sparkEmployees" style="min-height:50px"></div>
                                @if($statCards['warehouse_stock'] > 0)
                                <div class="stat-sub text-muted mt-1">موجودی انبار: {{ $nf($statCards['warehouse_stock']) }}</div>
                                @else
                                <div class="stat-sub text-muted mt-1">آخرین بروزرسانی: {{ $updatedAt }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ══════════════════════════════════════
                     ردیف ۲ — Revenue Report + Earning Reports
                ══════════════════════════════════════ --}}
                @if(!empty($revenueReport) && ($dashWidgets['dashboard_widget_pm_bi_chart'] ?? true))
                <div class="row g-4 mb-4">

                    {{-- Revenue Report: نمودار گروهی ۶ ماه --}}
                    <div class="col-12 col-xl-8">
                        <div class="card h-100">
                            <div class="card-body p-0">
                                <div class="row row-bordered g-0">
                                    <div class="col-md-8 p-4 revenue-split">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-0">گزارش درآمد</h5>
                                                <small class="text-muted">مقایسه فروش و فاکتورها — ۶ ماه اخیر</small>
                                            </div>
                                            <span class="widget-updated">{{ $updatedAt }}</span>
                                        </div>
                                        <div id="revenueReportChart" style="min-height:220px"></div>
                                    </div>
                                    <div class="col-md-4 p-4 d-flex flex-column justify-content-center">
                                        <div class="text-center mb-3">
                                            <small class="text-muted d-block mb-1">مجموع فروش ۶ ماه</small>
                                            <h4 class="text-primary mb-0">
                                                {{ number_format(array_sum($revenueReport['sales'])) }}
                                                <small class="fw-normal fs-6 text-muted">ت</small>
                                            </h4>
                                        </div>
                                        <div id="revenueBudgetChart" style="min-height:130px"></div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted">
                                                مجموع فاکتور: <strong>{{ number_format(array_sum($revenueReport['factors'])) }}</strong>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Earning Reports: تب‌های هفتگی --}}
                    <div class="col-12 col-xl-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">گزارش درآمد</h5>
                                    <small class="text-muted">بررسی هفتگی</small>
                                </div>
                                <span class="widget-updated">{{ $updatedAt }}</span>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs widget-nav-tabs pb-3 gap-3 mx-1 d-flex flex-nowrap" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link btn active d-flex flex-column align-items-center justify-content-center"
                                           data-bs-target="#earning-orders" data-bs-toggle="tab" href="javascript:void(0);" role="tab">
                                            <div class="badge bg-label-secondary rounded p-2">
                                                <i class="ti ti-shopping-cart ti-sm"></i>
                                            </div>
                                            <h6 class="tab-widget-title mb-0 mt-2" style="font-size:.78rem">سفارشات</h6>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn d-flex flex-column align-items-center justify-content-center"
                                           data-bs-target="#earning-sales" data-bs-toggle="tab" href="javascript:void(0);" role="tab">
                                            <div class="badge bg-label-secondary rounded p-2">
                                                <i class="ti ti-chart-bar ti-sm"></i>
                                            </div>
                                            <h6 class="tab-widget-title mb-0 mt-2" style="font-size:.78rem">فروش</h6>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn d-flex flex-column align-items-center justify-content-center"
                                           data-bs-target="#earning-customers" data-bs-toggle="tab" href="javascript:void(0);" role="tab">
                                            <div class="badge bg-label-secondary rounded p-2">
                                                <i class="ti ti-users ti-sm"></i>
                                            </div>
                                            <h6 class="tab-widget-title mb-0 mt-2" style="font-size:.78rem">مشتریان</h6>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content p-0">
                                    <div class="tab-pane fade show active" id="earning-orders" role="tabpanel">
                                        <div id="earningOrdersChart" style="min-height:130px"></div>
                                    </div>
                                    <div class="tab-pane fade" id="earning-sales" role="tabpanel">
                                        <div id="earningSalesChart" style="min-height:130px"></div>
                                    </div>
                                    <div class="tab-pane fade" id="earning-customers" role="tabpanel">
                                        <div id="earningCustomersChart" style="min-height:130px"></div>
                                    </div>
                                </div>

                                @if(!empty($financialSummary))
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex mb-3">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ti ti-chart-pie-2 ti-sm"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex w-100 align-items-center justify-content-between gap-2">
                                            <div>
                                                <h6 class="mb-0">درآمد این ماه</h6>
                                                <small class="text-muted">فاکتورهای تأییدشده</small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <small class="fw-medium">{{ $nfToman($financialSummary['this_month_revenue']) }} ت</small>
                                                @if($financialSummary['revenue_trend'] >= 0)
                                                    <i class="ti ti-chevron-up text-success"></i>
                                                @else
                                                    <i class="ti ti-chevron-down text-danger"></i>
                                                @endif
                                                <small class="text-muted">{{ abs($financialSummary['revenue_trend']) }}٪</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-warning">
                                                <i class="ti ti-clock ti-sm"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex w-100 align-items-center justify-content-between gap-2">
                                            <div>
                                                <h6 class="mb-0">در انتظار تأیید</h6>
                                                <small class="text-muted">{{ $nf($financialSummary['pending_factors']) }} فاکتور</small>
                                            </div>
                                            <small class="fw-medium">{{ $nfToman($financialSummary['pending_amount']) }} ت</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
                @endif

                {{-- ══════════════════════════════════════
                     ردیف ۳ — تیم فروش + آخرین مشتریان
                ══════════════════════════════════════ --}}
                <div class="row g-4 mb-4">

                    {{-- تیم فروش --}}
                    @if(!empty($salesTeam) && ($dashWidgets['dashboard_widget_pm_sales_team'] ?? true))
                    <div class="col-12 col-xl-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">تیم فروش</h5>
                                    <small class="text-muted">سلسله مراتب سرپرست → ویزیتور</small>
                                </div>
                                <span class="widget-updated">{{ $updatedAt }}</span>
                            </div>
                            <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                                <table class="table table-hover mb-0" style="font-size:.85rem">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>نام</th>
                                            <th class="text-center">مشتری</th>
                                            <th class="text-center">فاکتور</th>
                                            <th class="text-center">این ماه</th>
                                            <th class="text-center">روند</th>
                                        </tr>
                                    </thead>
                                    @foreach($salesTeam as $leader)
                                    <tbody>
                                        <tr class="team-row-leader team-toggle" data-target="visitors-{{ $leader['id'] }}">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="avatar avatar-xs">
                                                        <span class="avatar-initial rounded-circle bg-label-primary" style="font-size:.65rem">
                                                            {{ mb_substr($leader['name'], 0, 1) }}
                                                        </span>
                                                    </span>
                                                    <div>
                                                        <span class="badge bg-label-primary" style="font-size:.65rem">سرپرست</span>
                                                        <div style="font-size:.84rem">{{ $leader['name'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $nf($leader['customer_count']) }}</td>
                                            <td class="text-center">{{ $nf($leader['factor_count']) }}</td>
                                            <td class="text-center" style="font-size:.8rem">{{ $nfToman($leader['this_month_amount']) }} ت</td>
                                            <td class="text-center">
                                                @if($leader['trend'] > 0)
                                                    <span class="badge bg-label-success rounded-pill"><i class="ti ti-trending-up"></i> {{ $leader['trend'] }}٪</span>
                                                @elseif($leader['trend'] < 0)
                                                    <span class="badge bg-label-danger rounded-pill"><i class="ti ti-trending-down"></i> {{ abs($leader['trend']) }}٪</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                    @if(!empty($leader['visitors']))
                                    <tbody class="team-visitors" id="visitors-{{ $leader['id'] }}">
                                        @foreach($leader['visitors'] as $visitor)
                                        <tr class="team-row-visitor">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="avatar avatar-xs">
                                                        <span class="avatar-initial rounded-circle bg-label-secondary" style="font-size:.65rem">
                                                            {{ mb_substr($visitor['name'], 0, 1) }}
                                                        </span>
                                                    </span>
                                                    <div>
                                                        <span class="badge bg-label-secondary" style="font-size:.62rem">ویزیتور</span>
                                                        @if(!$visitor['is_active'])
                                                            <span class="badge bg-label-danger" style="font-size:.6rem">غیرفعال</span>
                                                        @endif
                                                        <div style="font-size:.82rem">{{ $visitor['name'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $nf($visitor['customer_count']) }}</td>
                                            <td class="text-center">{{ $nf($visitor['factor_count']) }}</td>
                                            <td class="text-center" style="font-size:.78rem">{{ $nfToman($visitor['this_month_amount']) }} ت</td>
                                            <td class="text-center">
                                                @if($visitor['trend'] > 0)
                                                    <span class="badge bg-label-success rounded-pill" style="font-size:.65rem">↑{{ $visitor['trend'] }}٪</span>
                                                @elseif($visitor['trend'] < 0)
                                                    <span class="badge bg-label-danger rounded-pill" style="font-size:.65rem">↓{{ abs($visitor['trend']) }}٪</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill" style="font-size:.65rem">—</span>
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

                    {{-- آخرین مشتریان ثبت‌شده --}}
                    <div class="{{ !empty($salesTeam) && ($dashWidgets['dashboard_widget_pm_sales_team'] ?? true) ? 'col-12 col-xl-8' : 'col-12' }}">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">آخرین مشتریان</h5>
                                    <small class="text-muted">۱۰ مشتری اخیر ثبت‌شده در سیستم</small>
                                </div>
                                <span class="widget-updated">{{ $updatedAt }}</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size:.84rem">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>نام مشتری</th>
                                            <th>موبایل</th>
                                            <th>بازاریاب</th>
                                            <th>ثبت‌کننده</th>
                                            <th class="text-center">فاکتور</th>
                                            <th class="text-center">مانده (ت)</th>
                                            <th>تاریخ ثبت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentCustomers ?? [] as $idx => $cust)
                                        <tr>
                                            <td>
                                                <span class="badge rounded-pill bg-label-primary" style="min-width:22px">{{ $idx + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="avatar avatar-sm">
                                                        <span class="avatar-initial rounded-circle bg-label-{{ ['primary','success','warning','info','danger'][$idx % 5] }}">
                                                            {{ mb_substr($cust['name'] ?? '?', 0, 1) }}
                                                        </span>
                                                    </span>
                                                    <span class="fw-medium">{{ $cust['name'] }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted" dir="ltr">{{ $cust['mobile'] }}</span>
                                            </td>
                                            <td>
                                                @if($cust['marketer'] !== '-')
                                                    <span class="badge bg-label-success">{{ $cust['marketer'] }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cust['added_by'] !== '-')
                                                    <span class="badge bg-label-info">{{ $cust['added_by'] }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-{{ $cust['factor_count'] > 0 ? 'primary' : 'secondary' }} rounded-pill">
                                                    {{ $cust['factor_count'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $bal = $cust['balance']; @endphp
                                                @if($bal > 0)
                                                    <span class="text-success fw-medium">{{ number_format((int)($bal/10)) }}</span>
                                                @elseif($bal < 0)
                                                    <span class="text-danger fw-medium">{{ number_format((int)(abs($bal)/10)) }}</span>
                                                @else
                                                    <span class="text-muted">۰</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted" style="font-size:.78rem">{{ $cust['registered_at'] }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="ti ti-users-off" style="font-size:2rem"></i>
                                                <p class="mt-2 mb-0">هنوز مشتری‌ای ثبت نشده است</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ══════════════════════════════════════
                     ردیف ۴ — محصولات + لاگ فعالیت
                ══════════════════════════════════════ --}}
                <div class="row g-4 mb-4">

                    {{-- برترین محصولات با progress bar --}}
                    @if(!empty($topProducts) && ($dashWidgets['dashboard_widget_pm_products'] ?? true))
                    <div class="col-12 col-xl-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">محصولات پرفروش</h5>
                                    <small class="text-muted">۳۰ روز اخیر</small>
                                </div>
                                <span class="widget-updated">{{ $updatedAt }}</span>
                            </div>
                            <div class="card-body">
                                @php $colors = ['primary','success','warning','info','danger']; @endphp
                                @forelse($topProducts as $pidx => $prod)
                                <div class="mb-4 @if(!$loop->last) pb-3 border-bottom @endif">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill bg-label-{{ $colors[$pidx % 5] }}" style="min-width:22px;font-size:.7rem">
                                                {{ $pidx + 1 }}
                                            </span>
                                            <span style="font-size:.85rem; font-weight:500">{{ \Illuminate\Support\Str::limit($prod['name'], 26) }}</span>
                                        </div>
                                        <span class="badge bg-label-{{ $colors[$pidx % 5] }} rounded-pill">{{ number_format($prod['qty']) }}</span>
                                    </div>
                                    <div class="progress product-progress">
                                        <div class="progress-bar bg-{{ $colors[$pidx % 5] }}"
                                             role="progressbar"
                                             style="width:{{ $prod['percent'] }}%"
                                             aria-valuenow="{{ $prod['percent'] }}"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted py-3">
                                    <i class="ti ti-package-off" style="font-size:2rem"></i>
                                    <p class="mt-2 mb-0">داده‌ای یافت نشد</p>
                                </div>
                                @endforelse

                                @if(!empty($productSummary))
                                <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                    <div class="text-center">
                                        <div class="fw-bold text-primary">{{ $nf($productSummary['total_products']) }}</div>
                                        <small class="text-muted">کل محصولات</small>
                                    </div>
                                    @if($productSummary['total_stock_items'] > 0)
                                    <div class="text-center">
                                        <div class="fw-bold text-success">{{ $nf($productSummary['total_stock_items']) }}</div>
                                        <small class="text-muted">موجودی انبار</small>
                                    </div>
                                    @endif
                                    @if($productSummary['low_stock_products']->count() > 0)
                                    <div class="text-center">
                                        <div class="fw-bold text-danger">{{ $productSummary['low_stock_products']->count() }}</div>
                                        <small class="text-muted">کم‌موجود</small>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- لاگ فعالیت --}}
                    @if(!empty($activityLog) && ($dashWidgets['dashboard_widget_pm_activity_log'] ?? true))
                    <div class="{{ !empty($topProducts) && ($dashWidgets['dashboard_widget_pm_products'] ?? true) ? 'col-12 col-xl-8' : 'col-12' }}">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h5 class="card-title mb-0">لاگ فعالیت سیستم</h5>
                                        <small class="text-muted">آخرین ۱۵ رویداد</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <input type="text" id="activitySearch" class="form-control form-control-sm"
                                               placeholder="جستجو..." style="width:160px">
                                        <select id="activityFilter" class="form-select form-select-sm" style="width:120px">
                                            <option value="">همه عملیات</option>
                                            <option value="create">ایجاد</option>
                                            <option value="update">ویرایش</option>
                                            <option value="delete">حذف</option>
                                            <option value="login">ورود</option>
                                            <option value="approve">تأیید</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="activity-timeline" id="activityList">
                                    @forelse($activityLog as $log)
                                    <div class="activity-item"
                                         data-action="{{ $log['action'] }}"
                                         data-search="{{ strtolower($log['user_name'] . ' ' . $log['description']) }}">
                                        <div class="activity-dot bg-{{ $log['action_color'] }}"></div>
                                        <div class="d-flex flex-wrap gap-2 align-items-start">
                                            <span class="badge bg-label-{{ $log['action_color'] }}" style="font-size:.7rem;min-width:44px;text-align:center">
                                                {{ $log['action_label'] }}
                                            </span>
                                            <span class="fw-semibold" style="font-size:.84rem">{{ $log['user_name'] }}</span>
                                            @if($log['description'])
                                            <span class="text-muted" style="font-size:.82rem">— {{ $log['description'] }}</span>
                                            @endif
                                            <span class="activity-meta me-auto">{{ $log['time_ago'] }}</span>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="ti ti-clock-off" style="font-size:2rem"></i>
                                        <p class="mt-2 mb-0">رویدادی یافت نشد</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ══════════════════════════════════════
                     ردیف ۵ — مناطق (اگر فعال باشد)
                ══════════════════════════════════════ --}}
                @if(!empty($routesSummary) && ($routesSummary['enabled'] ?? false) && ($dashWidgets['dashboard_widget_pm_routes'] ?? true))
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title mb-0">مناطق و مسیرها</h5>
                                    <small class="text-muted">توزیع مشتریان</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-6 text-center p-2 rounded" style="background:rgba(234,84,85,.08)">
                                        <div class="fw-bold" style="font-size:1.5rem;color:var(--pm-danger)">{{ $routesSummary['active_regions'] }}</div>
                                        <small class="text-muted">منطقه فعال</small>
                                    </div>
                                    <div class="col-6 text-center p-2 rounded" style="background:rgba(115,103,240,.08)">
                                        <div class="fw-bold" style="font-size:1.5rem;color:var(--pm-primary)">{{ $routesSummary['total_areas'] }}</div>
                                        <small class="text-muted">مسیر کل</small>
                                    </div>
                                </div>
                                @if(!empty($routesSummary['customers_per_region']))
                                @foreach($routesSummary['customers_per_region'] as $region)
                                @php
                                    $maxR = max(collect($routesSummary['customers_per_region'])->pluck('count')->max(), 1);
                                    $pct  = min(100, round($region['count'] / $maxR * 100));
                                @endphp
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>{{ $region['title'] }}</small>
                                        <small class="fw-semibold">{{ number_format($region['count']) }}</small>
                                    </div>
                                    <div class="progress" style="height:6px">
                                        <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>{{-- /container --}}
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

    var isDark      = document.documentElement.classList.contains('dark-style');
    var textColor   = isDark ? '#cfd3ec' : '#697a8d';
    var gridColor   = isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.06)';
    var cardBg      = isDark ? '#2b2c40' : '#fff';

    // ── توابع کمکی نمودار ──
    function sparklineOpts(color, data) {
        return {
            series: [{ data: data }],
            chart: { type: 'line', sparkline: { enabled: true }, height: 50, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 2 },
            colors: [color],
            tooltip: { enabled: false },
            fill: {
                type: 'gradient',
                gradient: { shade: 'light', type: 'vertical', opacityFrom: .4, opacityTo: 0 }
            }
        };
    }

    function barOpts(color, categories, data, height) {
        return {
            series: [{ name: 'مقدار', data: data }],
            chart: { type: 'bar', height: height || 130, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            colors: [color],
            dataLabels: { enabled: false },
            xaxis: {
                categories: categories,
                axisBorder: { show: false },
                axisTicks:  { show: false },
                labels: { style: { colors: textColor, fontFamily: 'inherit', fontSize: '11px' } }
            },
            yaxis: { show: false },
            grid: { show: false },
            tooltip: {
                y: { formatter: function(v) { return v.toLocaleString('fa-IR'); } }
            }
        };
    }

    // ══════════════════════════════════════
    // Sparkline کارت‌های آمار
    // ══════════════════════════════════════
    @if(!empty($earningTabs))
    var weekDays = @json($earningTabs['days']);

    var spC = document.getElementById('sparkCustomers');
    if (spC) new ApexCharts(spC, sparklineOpts('#7367f0', @json($earningTabs['customers']))).render();

    var spF = document.getElementById('sparkFactors');
    if (spF) new ApexCharts(spF, sparklineOpts('#28c76f', @json($earningTabs['orders']))).render();

    var spS = document.getElementById('sparkSales');
    if (spS) new ApexCharts(spS, sparklineOpts('#ff9f43', @json($earningTabs['sales']))).render();

    // کارت کارمند: نمودار ساده مستطیل روزانه
    var spE = document.getElementById('sparkEmployees');
    if (spE) new ApexCharts(spE, sparklineOpts('#00cfe8', [2,3,2,4,3,4,5])).render();
    @endif

    // ══════════════════════════════════════
    // Revenue Report: نمودار گروهی
    // ══════════════════════════════════════
    @if(!empty($revenueReport))
    var rvChartEl = document.getElementById('revenueReportChart');
    if (rvChartEl) {
        var rvOpts = {
            series: [
                { name: 'فروش (تومان)', data: @json($revenueReport['sales']) },
                { name: 'تعداد فاکتور', data: @json($revenueReport['factors']) }
            ],
            chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%', grouped: true } },
            colors: ['#7367f0', '#28c76f'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($revenueReport['labels']),
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { colors: textColor, fontFamily: 'inherit' } }
            },
            yaxis: {
                labels: {
                    style: { colors: textColor, fontFamily: 'inherit' },
                    formatter: function(v) {
                        if (v >= 1000000) return (v/1000000).toFixed(1) + 'م';
                        if (v >= 1000)    return (v/1000).toFixed(0) + 'ه';
                        return v;
                    }
                }
            },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            legend: {
                show: true, position: 'top',
                labels: { colors: textColor },
                fontFamily: 'inherit'
            },
            tooltip: {
                y: [{
                    formatter: function(v) { return v.toLocaleString('fa-IR') + ' ت'; }
                }, {
                    formatter: function(v) { return v.toLocaleString('fa-IR') + ' فاکتور'; }
                }]
            }
        };
        new ApexCharts(rvChartEl, rvOpts).render();
    }

    // Budget donut
    var bdEl = document.getElementById('revenueBudgetChart');
    if (bdEl) {
        var totalSales   = @json(array_sum($revenueReport['sales']));
        var totalFactors = @json(array_sum($revenueReport['factors']));
        var bdOpts = {
            series: [totalSales > 0 ? totalSales : 1, totalFactors],
            chart: { type: 'donut', height: 130, toolbar: { show: false }, fontFamily: 'inherit' },
            colors: ['#7367f0', '#28c76f'],
            labels: ['فروش', 'فاکتورها'],
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '65%' } } },
            tooltip: {
                y: { formatter: function(v, opts) {
                    return opts.seriesIndex === 0
                        ? v.toLocaleString('fa-IR') + ' ت'
                        : v.toLocaleString('fa-IR') + ' فاکتور';
                }}
            }
        };
        new ApexCharts(bdEl, bdOpts).render();
    }
    @endif

    // ══════════════════════════════════════
    // Earning Tabs: نمودار هفتگی
    // ══════════════════════════════════════
    @if(!empty($earningTabs))
    var etDays = @json($earningTabs['days']);
    var etOrders    = @json($earningTabs['orders']);
    var etSales     = @json($earningTabs['sales']);
    var etCustomers = @json($earningTabs['customers']);

    var eoEl = document.getElementById('earningOrdersChart');
    if (eoEl) new ApexCharts(eoEl, barOpts('#7367f0', etDays, etOrders)).render();

    var esEl = document.getElementById('earningSalesChart');
    if (esEl) new ApexCharts(esEl, barOpts('#28c76f', etDays, etSales)).render();

    var ecEl = document.getElementById('earningCustomersChart');
    if (ecEl) new ApexCharts(ecEl, barOpts('#00cfe8', etDays, etCustomers)).render();
    @endif

    // ══════════════════════════════════════
    // Toggle ردیف‌های ویزیتور
    // ══════════════════════════════════════
    document.querySelectorAll('.team-toggle').forEach(function(row) {
        row.addEventListener('click', function() {
            var target = document.getElementById(this.getAttribute('data-target'));
            if (target) target.classList.toggle('show');
        });
    });

    // ══════════════════════════════════════
    // جستجو و فیلتر لاگ
    // ══════════════════════════════════════
    var searchInput  = document.getElementById('activitySearch');
    var filterSelect = document.getElementById('activityFilter');

    function filterLogs() {
        var q      = searchInput  ? searchInput.value.toLowerCase()  : '';
        var action = filterSelect ? filterSelect.value.toLowerCase() : '';
        document.querySelectorAll('#activityList .activity-item').forEach(function(item) {
            var matchS = !q      || (item.dataset.search  || '').includes(q);
            var matchA = !action || (item.dataset.action  || '') === action;
            item.style.display = (matchS && matchA) ? '' : 'none';
        });
    }

    if (searchInput)  searchInput.addEventListener('input', filterLogs);
    if (filterSelect) filterSelect.addEventListener('change', filterLogs);

})();
</script>

</body>
</html>

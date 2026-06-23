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
        :root {
            --pm-primary:   #7367f0;
            --pm-success:   #28c76f;
            --pm-warning:   #ff9f43;
            --pm-danger:    #ea5455;
            --pm-info:      #00cfe8;
            --pm-secondary: #82868b;
        }
        .stat-icon {
            width: 42px; height: 42px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px; font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-card { border: none; box-shadow: 0 2px 12px rgba(115,103,240,.08); }
        .welcome-card-img {
            position: absolute; bottom: 0; left: 1rem;
            width: 130px; opacity: .9;
        }
        @media (max-width: 575px) { .welcome-card-img { display: none; } }
    </style>
</head>

@php
/* ─────────────────────────────────────────────
   Nuclear-safe inline queries — all wrapped in
   one big try/catch; falls back to zeros/empty.
───────────────────────────────────────────── */
try {
    /* ── Resolve organisation ID ── */
    $rawOrgId = auth()->user()->organization_id ?? 0;
    if (is_string($rawOrgId)) {
        $decoded  = json_decode($rawOrgId, true);
        $rawOrgId = (is_array($decoded) && !empty($decoded[0]))
            ? (int) $decoded[0]
            : (int) $rawOrgId;
    }
    $orgId = (int) $rawOrgId;

    /* ── Helpers ── */
    $nf      = fn($n) => number_format((int) $n);
    $nfToman = fn($n) => number_format((int) ($n / 10));

    try {
        $updatedAt = Verta::now()->format('H:i');
    } catch (\Throwable $e) {
        $updatedAt = now()->format('H:i');
    }

    /* ── Organisation name ── */
    $orgName = \DB::table('organizations')->where('id', $orgId)->value('title') ?? 'دارمینو';

    /* ── Stat card counts ── */
    $customerCount = \DB::table('customers')
        ->where('organization_id', $orgId)
        ->whereNull('deleted_at')
        ->count();

    $invoiceCount = \DB::table('pishfactors')
        ->where('organization_id', $orgId)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->whereNull('deleted_at')
        ->count();

    $revenue = (float) \DB::table('pishfactors')
        ->where('organization_id', $orgId)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->whereNull('deleted_at')
        ->sum('fullPrice');

    $employeeCount = \DB::table('users')
        ->where('organization_id', $orgId)
        ->whereNull('deleted_at')
        ->count();

    /* ── Recent customers (last 10) ── */
    $recentCustomers = \DB::table('customers')
        ->select(['id', 'name', 'mobile', 'created_at'])
        ->where('organization_id', $orgId)
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    /* ── Monthly chart — last 6 months ── */
    $monthlyData = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i);
        $monthlyData[] = [
            'label' => $month->format('Y/m'),
            'count' => \DB::table('pishfactors')
                ->where('organization_id', $orgId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->whereNull('deleted_at')
                ->count(),
        ];
    }

} catch (\Throwable $e) {
    /* Failsafe defaults — page NEVER 500s */
    $nf             = fn($n) => number_format((int) $n);
    $nfToman        = fn($n) => number_format((int) ($n / 10));
    $updatedAt      = now()->format('H:i');
    $orgName        = 'دارمینو';
    $customerCount  = 0;
    $invoiceCount   = 0;
    $revenue        = 0;
    $employeeCount  = 0;
    $recentCustomers = collect([]);
    $monthlyData    = [];
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

                    {{-- ── Welcome Banner ── --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card"
                                 style="background:linear-gradient(135deg,#7367f0,#9e95f5);color:#fff;position:relative;overflow:hidden;min-height:130px;">
                                <div class="card-body py-3 ps-4" style="max-width:75%">
                                    <small class="opacity-75 d-block mb-1">{{ $updatedAt }} — آخرین به‌روزرسانی</small>
                                    <h5 class="mb-1" style="color:#fff">خوش آمدید به داشبورد مدیر پنل</h5>
                                    <p class="mb-3 opacity-75" style="font-size:.88rem">{{ $orgName }}</p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('index') }}"
                                           class="btn btn-sm fw-semibold"
                                           style="background:#fff;color:#7367f0">
                                            <i class="ti ti-chart-bar me-1"></i> گزارش فروش
                                        </a>
                                        <a href="{{ route('invoices.all_invoices') }}"
                                           class="btn btn-sm btn-outline-light">
                                            <i class="ti ti-file-invoice me-1"></i> همه فاکتورها
                                        </a>
                                        @if(\Illuminate\Support\Facades\Route::has('settings.dashboardWidgets'))
                                        <a href="{{ route('settings.dashboardWidgets') }}"
                                           class="btn btn-sm btn-outline-light">
                                            <i class="ti ti-settings me-1"></i> تنظیمات داشبورد
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                <img src="{{ asset('assets/') }}/img/illustrations/card-advance-sale.png"
                                     alt="" class="welcome-card-img"
                                     onerror="this.style.display='none'">
                            </div>
                        </div>
                    </div>

                    {{-- ── 4 Stat Cards ── --}}
                    <div class="row g-4 mb-4">

                        {{-- مشتریان --}}
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="stat-icon bg-label-primary">
                                            <i class="ti ti-users text-primary"></i>
                                        </div>
                                        <span class="text-muted small">مشتریان</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $nf($customerCount) }}</h4>
                                    <p class="text-muted small mb-0 mt-1">مشتری ثبت‌شده</p>
                                </div>
                            </div>
                        </div>

                        {{-- فاکتور این ماه --}}
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="stat-icon bg-label-success">
                                            <i class="ti ti-file-invoice text-success"></i>
                                        </div>
                                        <span class="text-muted small">فاکتور این ماه</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $nf($invoiceCount) }}</h4>
                                    <p class="text-muted small mb-0 mt-1">فاکتور صادر‌شده</p>
                                </div>
                            </div>
                        </div>

                        {{-- فروش این ماه --}}
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="stat-icon bg-label-warning">
                                            <i class="ti ti-currency-dollar text-warning"></i>
                                        </div>
                                        <span class="text-muted small">فروش این ماه</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $nfToman($revenue) }}</h4>
                                    <p class="text-muted small mb-0 mt-1">تومان</p>
                                </div>
                            </div>
                        </div>

                        {{-- کارمندان --}}
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="stat-icon bg-label-info">
                                            <i class="ti ti-user-check text-info"></i>
                                        </div>
                                        <span class="text-muted small">کارمندان</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $nf($employeeCount) }}</h4>
                                    <p class="text-muted small mb-0 mt-1">کاربر سیستم</p>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /stat cards --}}

                    {{-- ── Chart + Recent Customers ── --}}
                    <div class="row g-4">

                        {{-- Monthly Chart --}}
                        <div class="col-12 col-lg-7">
                            <div class="card h-100">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-chart-bar text-primary me-2"></i>
                                        نمودار فاکتورهای ۶ ماهه
                                    </h5>
                                </div>
                                <div class="card-body pt-3">
                                    <div id="pm-monthly-chart"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Customers --}}
                        <div class="col-12 col-lg-5">
                            <div class="card h-100">
                                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-users text-success me-2"></i>
                                        آخرین مشتریان
                                    </h5>
                                    <span class="badge bg-label-primary rounded-pill">{{ count($recentCustomers) }}</span>
                                </div>
                                <div class="card-body p-0">
                                    @if($recentCustomers->isEmpty())
                                        <p class="text-center text-muted py-5 mb-0">داده‌ای یافت نشد</p>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">نام</th>
                                                    <th>موبایل</th>
                                                    <th class="pe-3">تاریخ ثبت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentCustomers as $cust)
                                                <tr>
                                                    <td class="ps-3 fw-semibold">{{ $cust->name }}</td>
                                                    <td>
                                                        @if(!empty($cust->mobile))
                                                            <span class="badge bg-label-secondary">{{ $cust->mobile }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="pe-3 text-muted small">
                                                        @php
                                                            try {
                                                                echo \Carbon\Carbon::parse($cust->created_at)->format('Y/m/d');
                                                            } catch (\Throwable $e) {
                                                                echo '—';
                                                            }
                                                        @endphp
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>{{-- /row chart+table --}}

                </div>{{-- /container --}}

                @include('sections/footer')
            </div>{{-- /content-wrapper --}}
        </div>{{-- /layout-page --}}
    </div>{{-- /layout-container --}}
    <div class="layout-overlay layout-menu-toggle"></div>
</div>{{-- /layout-wrapper --}}

{{-- Core vendor scripts --}}
<script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<script src="{{ asset('assets/') }}/js/main.js"></script>

<script>
(function () {
    'use strict';

    var labels = {!! json_encode(array_column($monthlyData, 'label')) !!};
    var counts = {!! json_encode(array_column($monthlyData, 'count')) !!};

    if (typeof ApexCharts !== 'undefined') {
        var el = document.getElementById('pm-monthly-chart');
        if (el) {
            new ApexCharts(el, {
                chart: {
                    type: 'bar',
                    height: 270,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                series: [{ name: 'تعداد فاکتور', data: counts }],
                xaxis: {
                    categories: labels,
                    labels: { style: { fontSize: '12px' } },
                },
                yaxis: {
                    labels: { style: { fontSize: '12px' } },
                },
                colors: ['#7367f0'],
                plotOptions: {
                    bar: { borderRadius: 5, columnWidth: '55%' }
                },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                tooltip: {
                    y: { formatter: function (v) { return v + ' فاکتور'; } }
                },
            }).render();
        }
    }
})();
</script>

</body>
</html>

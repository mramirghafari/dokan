<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پورسانت و تسویه - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
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
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">تارگت های فروش /</span> پورسانت و تسویه
                        </h4>
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">تارگت ها</span>
                                        <h4 class="mb-0">{{ number_format($reports->count()) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">فروش مشمول</span>
                                        <h4 class="mb-0">{{ number_format($reports->sum('sales_amount')) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">هدف فروش</span>
                                        <h4 class="mb-0">{{ number_format($reports->sum('target_amount')) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">پورسانت قابل پرداخت</span>
                                        <h4 class="mb-0 text-success">
                                            {{ number_format($reports->sum('payable_amount')) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">محاسبه پورسانت بر اساس تارگت</h5>
                                    <small class="text-muted">محاسبه با فاکتورهای تاییدشده، پلن پورسانت، achievement و
                                        سقف/جریمه انجام می شود.</small>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>کاربر</th>
                                            <th>بازه</th>
                                            <th>فاکتور</th>
                                            <th>فروش</th>
                                            <th>هدف</th>
                                            <th>تحقق</th>
                                            <th>پورسانت پایه</th>
                                            <th>پلکان/بونس</th>
                                            <th>قابل پرداخت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reports as $report)
                                            <tr>
                                                <td>{{ optional($report['user'])->name ?? '-' }}</td>
                                                <td><small>{{ $report['period_start'] }}<br>{{ $report['period_end'] }}</small>
                                                </td>
                                                <td>{{ number_format($report['invoices_count']) }}</td>
                                                <td>{{ number_format($report['sales_amount']) }}</td>
                                                <td>{{ number_format($report['target_amount']) }}</td>
                                                <td><span
                                                        class="badge bg-label-{{ $report['achievement_percent'] >= 100 ? 'success' : 'warning' }}">{{ $report['achievement_percent'] }}%</span>
                                                </td>
                                                <td>{{ number_format($report['base_commission_amount']) }}</td>
                                                <td>{{ number_format($report['tier_commission_amount'] + $report['bonus_amount'] - $report['penalty_amount']) }}
                                                </td>
                                                <td><strong>{{ number_format($report['payable_amount']) }}</strong>
                                                </td>
                                                <td>
                                                    <form
                                                        action="{{ route('commissions.calculate', $report['target']->id) }}"
                                                        method="post">
                                                        @csrf
                                                        <button class="btn btn-sm btn-primary" type="submit">ثبت
                                                            settlement</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted" colspan="10">تارگتی برای محاسبه
                                                    وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

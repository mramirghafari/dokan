<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مانده نقدینگی - دکان دارمینو</title>
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
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> مانده نقدینگی
                            </h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-success"
                                    href="{{ route('Accounting.treasury.cashForecast') }}">پیش بینی نقدینگی</a>
                                <a class="btn btn-outline-warning"
                                    href="{{ route('Accounting.treasury.bankReconciliation') }}">مغایرت بانکی</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury') }}">بازگشت</a>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('Accounting.treasury.liquidity') }}" class="card mb-4">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">از تاریخ سند</label>
                                        <input type="date" name="date_from" class="form-control"
                                            value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">تا تاریخ سند</label>
                                        <input type="date" name="date_to" class="form-control"
                                            value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">بروزرسانی گزارش</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">مانده دفتر حسابداری</div>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format((float) $rows->sum('ledger_balance')) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">مانده صورت حساب ثبت شده</div>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format((float) $rows->sum('statement_balance')) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">اختلاف قابل پیگیری</div>
                                        <h4 class="mb-0 text-end">{{ number_format((float) $rows->sum('difference')) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>حساب</th>
                                            <th class="text-end">بدهکار دفتر</th>
                                            <th class="text-end">بستانکار دفتر</th>
                                            <th class="text-end">مانده دفتر</th>
                                            <th class="text-end">مانده صورت حساب</th>
                                            <th class="text-end">اختلاف</th>
                                            <th class="text-center">تطبیق شده</th>
                                            <th class="text-center">تطبیق نشده</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rows as $row)
                                            <tr>
                                                <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                                                <td class="text-end">{{ number_format((float) $row['ledger_debit']) }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $row['ledger_credit']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['ledger_balance']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['statement_balance']) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['difference']) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ number_format((int) $row['matched_count']) }}</td>
                                                <td class="text-center">
                                                    {{ number_format((int) $row['unmatched_count']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">حساب خزانه ای
                                                    برای گزارش
                                                    پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @include('sections/footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>

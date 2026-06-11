<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>دفتر کل و تراز چندستونی - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> دفتر کل و تراز
                                چندستونی</h4>
                            <a class="btn btn-outline-secondary"
                                href="{{ route('Accounting.financialStatements') }}">صورت های مالی</a>
                        </div>

                        <form method="GET" action="{{ route('Accounting.detailedLedgers') }}" class="card mb-4">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-2">
                                    <label class="form-label">از تاریخ</label>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ request('from_date', $report['from_date']) }}">
                                </div>
                                <div class="col-12 col-md-2">
                                    <label class="form-label">تا تاریخ</label>
                                    <input type="date" name="to_date" class="form-control"
                                        value="{{ request('to_date', $report['to_date']) }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">سطح دفتر کل</label>
                                    <select name="level" class="form-select">
                                        <option value="">همه سطوح حساب</option>
                                        <option value="1" @selected((string) request('level', $report['level']) === '1')>سطح 1 - گروه/کل</option>
                                        <option value="2" @selected((string) request('level', $report['level']) === '2')>سطح 2 - کل/معین</option>
                                        <option value="3" @selected((string) request('level', $report['level']) === '3')>سطح 3 - معین/تفصیل</option>
                                        <option value="4" @selected((string) request('level', $report['level']) === '4')>سطح 4 و پایین تر</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permanent_only"
                                            value="1" id="permanent_only" @checked($report['permanent_only'])>
                                        <label class="form-check-label" for="permanent_only">فقط دائم</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">نمایش گزارش</button>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="alert alert-info mb-0 py-2">تراز ۸ ستونی شامل افتتاحیه، گردش، جمع گردش و
                                        مانده پایان دوره است.</div>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">گردش بدهکار دوره</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['period_debit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">گردش بستانکار دوره</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['period_credit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مانده بدهکار پایان</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['closing_debit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مانده بستانکار پایان</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['closing_credit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-1">دفتر کل چندسطحی</h5>
                                <small class="text-muted">مانده ها با توجه به ساختار پدر/فرزند حساب ها تجمیع شده
                                    اند.</small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>سطح</th>
                                            <th>کد حساب</th>
                                            <th>نام حساب</th>
                                            <th class="text-end">افتتاحیه بدهکار</th>
                                            <th class="text-end">افتتاحیه بستانکار</th>
                                            <th class="text-end">گردش بدهکار</th>
                                            <th class="text-end">گردش بستانکار</th>
                                            <th class="text-end">جمع بدهکار</th>
                                            <th class="text-end">جمع بستانکار</th>
                                            <th class="text-end">مانده بدهکار</th>
                                            <th class="text-end">مانده بستانکار</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report['ledger_rows'] as $row)
                                            <tr>
                                                <td>{{ $row['level'] ?: '-' }}</td>
                                                <td>{{ $row['account']?->code }}</td>
                                                <td>{{ $row['account']?->name }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['opening_debit']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['opening_credit']) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['period_debit']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['period_credit']) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['total_debit']) }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $row['total_credit']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['closing_debit']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['closing_credit']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">گردشی برای
                                                    گزارش پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-1">تراز آزمایشی ۸ ستونی</h5>
                                <small class="text-muted">ردیف های مستقیم حساب ها بدون تجمیع والد نمایش داده می
                                    شوند.</small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>کد حساب</th>
                                            <th>نام حساب</th>
                                            <th class="text-end">افتتاحیه بدهکار</th>
                                            <th class="text-end">افتتاحیه بستانکار</th>
                                            <th class="text-end">گردش بدهکار</th>
                                            <th class="text-end">گردش بستانکار</th>
                                            <th class="text-end">جمع بدهکار</th>
                                            <th class="text-end">جمع بستانکار</th>
                                            <th class="text-end">مانده بدهکار</th>
                                            <th class="text-end">مانده بستانکار</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report['trial_rows'] as $row)
                                            <tr>
                                                <td>{{ $row['account']?->code }}</td>
                                                <td>{{ $row['account']?->name }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['opening_debit']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['opening_credit']) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['period_debit']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['period_credit']) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['total_debit']) }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $row['total_credit']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['closing_debit']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['closing_credit']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">گردشی برای تراز
                                                    آزمایشی پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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

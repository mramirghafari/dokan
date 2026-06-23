<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پیش بینی نقدینگی - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('partials.panel-toasts')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> پیش بینی
                                نقدینگی</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-info" href="{{ route('Accounting.treasury.cheques') }}">گزارش
                                    چک ها</a>
                                <a class="btn btn-outline-dark"
                                    href="{{ route('Accounting.treasury.liquidity') }}">مانده نقدینگی</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury') }}">بازگشت</a>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('Accounting.treasury.cashForecast') }}"
                            class="card mb-4">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">تاریخ مبنا</label>
                                        <input type="date" name="base_date" class="form-control"
                                            value="{{ request('base_date', $report['base_date']) }}">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">افق پیش بینی</label>
                                        <select name="days" class="form-select">
                                            @foreach ([7, 15, 30, 60, 90, 180] as $days)
                                                <option value="{{ $days }}" @selected((int) $report['days'] === $days)>
                                                    {{ $days }} روز</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">محاسبه پیش بینی</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">مانده مبنا</div>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['opening_balance']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">ورودی پیش بینی</div>
                                        <h5 class="mb-0 text-end text-success">
                                            {{ number_format((float) $report['expected_inflow']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">خروجی پیش بینی</div>
                                        <h5 class="mb-0 text-end text-danger">
                                            {{ number_format((float) $report['expected_outflow']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">مانده پایان</div>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['projected_balance']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">چک معوق</div>
                                        <h5 class="mb-0 text-end text-warning">
                                            {{ number_format((int) $report['overdue_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">مبلغ معوق</div>
                                        <h5 class="mb-0 text-end text-warning">
                                            {{ number_format((float) $report['overdue_amount']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">پیش بینی روزانه تا {{ $report['to_date'] }}</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>تاریخ سررسید</th>
                                            <th class="text-center">تعداد دریافتنی</th>
                                            <th class="text-end">ورودی</th>
                                            <th class="text-center">تعداد پرداختنی</th>
                                            <th class="text-end">خروجی</th>
                                            <th class="text-end">اثر خالص</th>
                                            <th class="text-end">مانده پیش بینی شده</th>
                                            <th>منابع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report['daily_rows'] as $row)
                                            <tr>
                                                <td>{{ $row['date'] }}</td>
                                                <td class="text-center">
                                                    {{ number_format((int) $row['incoming_count']) }}</td>
                                                <td class="text-end text-success">
                                                    {{ number_format((float) $row['inflow']) }}</td>
                                                <td class="text-center">
                                                    {{ number_format((int) $row['outgoing_count']) }}</td>
                                                <td class="text-end text-danger">
                                                    {{ number_format((float) $row['outflow']) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['net']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['projected_balance']) }}</td>
                                                <td>{{ $row['sources'] ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">در افق انتخاب
                                                    شده جریان نقدی پیش بینی شده وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">خلاصه منابع پیش بینی</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>منبع</th>
                                                    <th class="text-center">تعداد ورودی</th>
                                                    <th class="text-end">مبلغ ورودی</th>
                                                    <th class="text-center">تعداد خروجی</th>
                                                    <th class="text-end">مبلغ خروجی</th>
                                                    <th class="text-end">خالص</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($report['source_summary'] as $source)
                                                    <tr>
                                                        <td>{{ $source['source_label'] }}</td>
                                                        <td class="text-center">
                                                            {{ number_format((int) $source['incoming_count']) }}</td>
                                                        <td class="text-end text-success">
                                                            {{ number_format((float) $source['incoming_amount']) }}
                                                        </td>
                                                        <td class="text-center">
                                                            {{ number_format((int) $source['outgoing_count']) }}</td>
                                                        <td class="text-end text-danger">
                                                            {{ number_format((float) $source['outgoing_amount']) }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $source['net']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">منبعی
                                                            برای پیش بینی پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">جریان های نقدی چندمنبعی در افق پیش بینی</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>تاریخ</th>
                                                    <th>منبع</th>
                                                    <th>عنوان</th>
                                                    <th>جهت</th>
                                                    <th class="text-end">مبلغ</th>
                                                    <th>طرف حساب</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($report['forecast_events'] as $event)
                                                    <tr>
                                                        <td>{{ $event['date'] ?: '-' }}</td>
                                                        <td>{{ $event['source_label'] }}</td>
                                                        <td>{{ $event['title'] }}</td>
                                                        <td>{{ $event['direction'] === 'incoming' ? 'ورودی' : 'خروجی' }}
                                                        </td>
                                                        <td
                                                            class="text-end {{ $event['direction'] === 'incoming' ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format((float) $event['amount']) }}</td>
                                                        <td>{{ $event['counterparty'] ?: '-' }}</td>
                                                        <td>{{ $event['status'] ?: '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">جریان
                                                            نقدی چندمنبعی در این بازه ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">چک های باز در افق پیش بینی</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>سررسید</th>
                                                    <th>شماره</th>
                                                    <th>جهت</th>
                                                    <th>وضعیت</th>
                                                    <th class="text-end">مبلغ</th>
                                                    <th>طرف حساب</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($report['due_cheques'] as $cheque)
                                                    <tr>
                                                        <td>{{ verta_date($cheque->due_date) }}</td>
                                                        <td>{{ $cheque->cheque_number ?: '-' }}</td>
                                                        <td>{{ $cheque->direction === 'incoming' ? 'دریافتنی' : 'پرداختنی' }}
                                                        </td>
                                                        <td>{{ $statuses[$cheque->status] ?? $cheque->status }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $cheque->amount) }}</td>
                                                        <td>{{ optional($cheque->counterAccount)->name ?: '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">چک باز
                                                            سررسیددار پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">هشدار جریان های معوق</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>سررسید</th>
                                                    <th>منبع</th>
                                                    <th>عنوان</th>
                                                    <th>جهت</th>
                                                    <th class="text-end">مبلغ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($report['overdue_events'] as $event)
                                                    <tr>
                                                        <td>{{ $event['date'] ?: '-' }}</td>
                                                        <td>{{ $event['source_label'] }}</td>
                                                        <td>{{ $event['title'] }}</td>
                                                        <td>{{ $event['direction'] === 'incoming' ? 'ورودی' : 'خروجی' }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $event['amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">جریان
                                                            نقدی
                                                            معوق وجود ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>

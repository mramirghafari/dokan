<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>صورت های مالی - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
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
                        <div id="tour-financial-statements-page" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> صورت های مالی
                            </h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.legalLedgers') }}">دفاتر و
                                تراز آزمایشی</a>
                        </div>

                        <form id="tour-financial-statements-filters" method="GET" action="{{ route('Accounting.financialStatements') }}" class="card mb-4">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-3">
                                    <label class="form-label">سال مالی</label>
                                    <select name="fiscal_year_id" class="form-select fiscal-year-select">
                                        <option value="">انتخاب بر اساس تاریخ</option>
                                        @foreach ($fiscalYears as $fiscalYear)
                                            <option value="{{ $fiscalYear->id }}" @selected($selectedFiscalYear && (int) $selectedFiscalYear->id === (int) $fiscalYear->id)>
                                                {{ $fiscalYear->title }} |
                                                {{ verta_date($fiscalYear->starts_at) }} تا
                                                {{ verta_date($fiscalYear->ends_at) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
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
                                <div class="col-12 col-md-2">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="permanent_only"
                                            value="1" id="permanent_only" @checked($report['permanent_only'])>
                                        <label class="form-check-label" for="permanent_only">فقط اسناد دائم</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">نمایش گزارش</button>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">مرکز درآمد</label>
                                    <select name="revenue_center_id" class="form-select select2-basic">
                                        <option value="">همه مراکز درآمد</option>
                                        @foreach ($revenueCenters as $revenueCenter)
                                            <option value="{{ $revenueCenter->id }}" @selected((string) request('revenue_center_id') === (string) $revenueCenter->id)>
                                                {{ $revenueCenter->code }} - {{ $revenueCenter->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">بعد گزارش سود</label>
                                    <select name="profit_dimension" class="form-select">
                                        <option value="revenue_center" @selected($report['multi_dimensional_profit']['dimension'] === 'revenue_center')>مرکز درآمد</option>
                                        <option value="product" @selected($report['multi_dimensional_profit']['dimension'] === 'product')>کالا / محصول</option>
                                        <option value="project" @selected($report['multi_dimensional_profit']['dimension'] === 'project')>پروژه</option>
                                        <option value="contract" @selected($report['multi_dimensional_profit']['dimension'] === 'contract')>قرارداد</option>
                                        <option value="route" @selected($report['multi_dimensional_profit']['dimension'] === 'route')>مسیر فروش</option>
                                        <option value="visitor" @selected($report['multi_dimensional_profit']['dimension'] === 'visitor')>ویزیتور</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-12">
                                    <div class="alert alert-info mb-0 py-2">سند اختتامیه در محاسبه مانده ها حذف می شود
                                        تا گزارش سال بسته شده صفر نشود.</div>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">درآمد دوره</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['income_statement']['total_income']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">هزینه دوره</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['income_statement']['total_expense']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">سود / زیان خالص</small>
                                        <h5
                                            class="mb-0 text-end {{ $report['income_statement']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format(abs((float) $report['income_statement']['net_profit'])) }}
                                            {{ $report['income_statement']['net_profit'] >= 0 ? 'سود' : 'زیان' }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">کنترل تراز وضعیت مالی</small>
                                        <h5
                                            class="mb-0 text-end {{ abs((float) $report['financial_position']['position_check']) < 1 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format((float) $report['financial_position']['position_check']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="mb-1">داشبورد حاشیه سود و زیان دهی</h5>
                                    <small class="text-muted">بر اساس فروش خالص، مرجوعی، بهای تمام شده و هزینه های
                                        تخصیص یافته</small>
                                </div>
                                <span class="badge bg-label-info">نقطه سربه سر ساده</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <small class="text-muted">حاشیه سود کل</small>
                                            <h5
                                                class="mb-0 text-end {{ $report['margin_dashboard']['summary']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ is_null($report['margin_dashboard']['summary']['margin_percent']) ? '-' : number_format((float) $report['margin_dashboard']['summary']['margin_percent'], 2) . '%' }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <small class="text-muted">سود / زیان تحلیلی</small>
                                            <h5
                                                class="mb-0 text-end {{ $report['margin_dashboard']['summary']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format((float) $report['margin_dashboard']['summary']['net_profit']) }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <small class="text-muted">فاصله تا سربه سر</small>
                                            <h5
                                                class="mb-0 text-end {{ $report['margin_dashboard']['summary']['break_even_gap'] > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format((float) $report['margin_dashboard']['summary']['break_even_gap']) }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="border rounded p-3 h-100">
                                            <small class="text-muted">موارد زیان ده / نزدیک سربه سر</small>
                                            <h5 class="mb-0 text-end">
                                                {{ number_format((int) $report['margin_dashboard']['summary']['loss_makers_count']) }}
                                                /
                                                {{ number_format((int) $report['margin_dashboard']['summary']['near_break_even_count']) }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-12 col-xl-4">
                                        <h6 class="mb-2">زیان ده ترین ابعاد</h6>
                                        <div class="table-responsive border rounded">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>بعد</th>
                                                        <th class="text-end">حاشیه</th>
                                                        <th class="text-end">زیان</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($report['margin_dashboard']['loss_makers'] as $row)
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="badge bg-label-danger mb-1">{{ $row['dashboard_dimension_label'] }}</span>
                                                                <div class="fw-semibold">{{ $row['dimension_label'] }}
                                                                </div>
                                                                <small
                                                                    class="text-muted">{{ $row['dimension_code'] ?: '-' }}</small>
                                                            </td>
                                                            <td class="text-end">
                                                                {{ is_null($row['margin_percent']) ? '-' : number_format((float) $row['margin_percent'], 2) . '%' }}
                                                            </td>
                                                            <td class="text-end text-danger">
                                                                {{ number_format(abs((float) $row['net_profit'])) }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted py-3">
                                                                مورد زیان دهی پیدا نشد.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12 col-xl-4">
                                        <h6 class="mb-2">نزدیک نقطه سربه سر</h6>
                                        <div class="table-responsive border rounded">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>بعد</th>
                                                        <th class="text-end">حاشیه</th>
                                                        <th class="text-end">فاصله</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($report['margin_dashboard']['near_break_even'] as $row)
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="badge bg-label-warning mb-1">{{ $row['dashboard_dimension_label'] }}</span>
                                                                <div class="fw-semibold">{{ $row['dimension_label'] }}
                                                                </div>
                                                                <small
                                                                    class="text-muted">{{ $row['dimension_code'] ?: '-' }}</small>
                                                            </td>
                                                            <td class="text-end">
                                                                {{ is_null($row['margin_percent']) ? '-' : number_format((float) $row['margin_percent'], 2) . '%' }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ number_format((float) $row['break_even_gap']) }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted py-3">
                                                                مورد نزدیک سربه سر پیدا نشد.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12 col-xl-4">
                                        <h6 class="mb-2">سودآورترین ابعاد</h6>
                                        <div class="table-responsive border rounded">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>بعد</th>
                                                        <th class="text-end">حاشیه</th>
                                                        <th class="text-end">سود</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($report['margin_dashboard']['top_profit'] as $row)
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="badge bg-label-success mb-1">{{ $row['dashboard_dimension_label'] }}</span>
                                                                <div class="fw-semibold">{{ $row['dimension_label'] }}
                                                                </div>
                                                                <small
                                                                    class="text-muted">{{ $row['dimension_code'] ?: '-' }}</small>
                                                            </td>
                                                            <td class="text-end">
                                                                {{ is_null($row['margin_percent']) ? '-' : number_format((float) $row['margin_percent'], 2) . '%' }}
                                                            </td>
                                                            <td class="text-end text-success">
                                                                {{ number_format((float) $row['net_profit']) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted py-3">
                                                                داده سودآوری پیدا نشد.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card">
                                    <div
                                        class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h5 class="mb-1">گزارش سود چندبعدی</h5>
                                            <small class="text-muted">درآمد سندهای حسابداری در کنار هزینه های تخصیص
                                                یافته به کالا، پروژه، قرارداد یا مرکز درآمد</small>
                                        </div>
                                        <span
                                            class="badge bg-label-primary">{{ [
                                                'revenue_center' => 'مرکز درآمد',
                                                'product' => 'کالا / محصول',
                                                'project' => 'پروژه',
                                                'contract' => 'قرارداد',
                                                'route' => 'مسیر فروش',
                                                'visitor' => 'ویزیتور',
                                            ][$report['multi_dimensional_profit']['dimension']] ?? 'مرکز درآمد' }}</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>بعد تحلیلی</th>
                                                    <th class="text-end">درآمد</th>
                                                    <th class="text-end">هزینه تخصیص یافته</th>
                                                    <th class="text-end">سود / زیان</th>
                                                    <th class="text-end">تعداد ردیف</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($report['multi_dimensional_profit']['rows'] as $row)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $row['dimension_label'] }}
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ $row['dimension_code'] ?: '-' }}</small>
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['income_amount']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['allocated_expense_amount']) }}
                                                        </td>
                                                        <td
                                                            class="text-end {{ $row['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format((float) $row['net_profit']) }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((int) $row['income_items_count'] + (int) $row['expense_allocations_count']) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">برای
                                                            این بعد تحلیلی درآمد یا هزینه تخصیص یافته ای پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr class="fw-bold table-light">
                                                    <td>جمع همین بعد</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['multi_dimensional_profit']['total_income']) }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['multi_dimensional_profit']['total_allocated_expense']) }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['multi_dimensional_profit']['net_profit']) }}
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card">
                                    <div
                                        class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h5 class="mb-1">سود به تفکیک مرکز درآمد</h5>
                                            <small class="text-muted">برای تحلیل شعبه، مسیر، پروژه، قرارداد، کانال فروش
                                                یا گروه مشتری</small>
                                        </div>
                                        <a class="btn btn-sm btn-outline-secondary"
                                            href="{{ route('Accounting.revenueCenters') }}">تعریف مراکز درآمد</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>مرکز درآمد</th>
                                                    <th class="text-end">درآمد</th>
                                                    <th class="text-end">هزینه</th>
                                                    <th class="text-end">سود / زیان</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($report['revenue_center_statement']['rows'] as $row)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $row['label'] }}</div>
                                                            <small
                                                                class="text-muted">{{ optional($row['revenue_center'])->code ?: '-' }}</small>
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['income_amount']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['expense_amount']) }}</td>
                                                        <td
                                                            class="text-end {{ $row['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format((float) $row['net_profit']) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">مرکز
                                                            درآمدی روی ردیف های سند ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr class="fw-bold table-light">
                                                    <td>جمع</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['revenue_center_statement']['total_income']) }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['revenue_center_statement']['total_expense']) }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['revenue_center_statement']['net_profit']) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div id="tour-financial-statements-report" class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-1">صورت سود و زیان</h5>
                                        <small class="text-muted">از {{ $report['from_date'] }} تا
                                            {{ $report['to_date'] }}</small>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>حساب</th>
                                                    <th class="text-end">مبلغ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="table-light">
                                                    <th colspan="2">درآمدها</th>
                                                </tr>
                                                @forelse ($report['income_statement']['income_rows'] as $row)
                                                    <tr>
                                                        <td>{{ $row['account']?->code }} -
                                                            {{ $row['account']?->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted py-3">درآمدی
                                                            ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                                <tr class="fw-bold">
                                                    <td>جمع درآمد</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['income_statement']['total_income']) }}
                                                    </td>
                                                </tr>
                                                <tr class="table-light">
                                                    <th colspan="2">هزینه ها</th>
                                                </tr>
                                                @forelse ($report['income_statement']['expense_rows'] as $row)
                                                    <tr>
                                                        <td>{{ $row['account']?->code }} -
                                                            {{ $row['account']?->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted py-3">هزینه
                                                            ای ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                                <tr class="fw-bold">
                                                    <td>جمع هزینه</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['income_statement']['total_expense']) }}
                                                    </td>
                                                </tr>
                                                <tr class="fw-bold table-primary">
                                                    <td>سود / زیان خالص</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['income_statement']['net_profit']) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-1">صورت وضعیت مالی</h5>
                                        <small class="text-muted">تا تاریخ {{ $report['to_date'] }}</small>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>حساب</th>
                                                    <th class="text-end">مانده</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="table-light">
                                                    <th colspan="2">دارایی ها</th>
                                                </tr>
                                                @foreach ($report['financial_position']['asset_rows'] as $row)
                                                    <tr>
                                                        <td>{{ $row['account']?->code }} -
                                                            {{ $row['account']?->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['amount']) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="fw-bold">
                                                    <td>جمع دارایی</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['financial_position']['total_assets']) }}
                                                    </td>
                                                </tr>
                                                <tr class="table-light">
                                                    <th colspan="2">بدهی ها</th>
                                                </tr>
                                                @foreach ($report['financial_position']['liability_rows'] as $row)
                                                    <tr>
                                                        <td>{{ $row['account']?->code }} -
                                                            {{ $row['account']?->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['amount']) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="fw-bold">
                                                    <td>جمع بدهی</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['financial_position']['total_liabilities']) }}
                                                    </td>
                                                </tr>
                                                <tr class="table-light">
                                                    <th colspan="2">حقوق مالکانه</th>
                                                </tr>
                                                @foreach ($report['financial_position']['equity_rows'] as $row)
                                                    <tr>
                                                        <td>{{ $row['account']?->code }} -
                                                            {{ $row['account']?->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['amount']) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td>سود / زیان دوره</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['income_statement']['net_profit']) }}
                                                    </td>
                                                </tr>
                                                <tr class="fw-bold">
                                                    <td>جمع حقوق مالکانه با سود دوره</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['financial_position']['total_equity'] + (float) $report['income_statement']['net_profit']) }}
                                                    </td>
                                                </tr>
                                                <tr class="fw-bold table-primary">
                                                    <td>جمع بدهی و حقوق مالکانه</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $report['financial_position']['total_liabilities'] + (float) $report['financial_position']['total_equity'] + (float) $report['income_statement']['net_profit']) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($report['summary']['has_uncategorized'])
                            <div class="alert alert-warning mt-4 mb-0">چند حساب بدون طبقه حساب در محاسبه صورت وضعیت
                                مالی نیامده اند. در فرم حساب، طبقه حساب را تکمیل کنید.</div>
                        @endif
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $('.fiscal-year-select, .select2-basic').select2({
            width: '100%'
        });
    </script>
</body>

</html>

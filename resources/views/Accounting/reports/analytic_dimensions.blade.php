<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گزارش تفصیل شناور - دکان دارمینو</title>
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
                        <div id="tour-analytic-dimensions-page" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> گزارش تفصیل
                                شناور</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.vouchers') }}">اسناد
                                حسابداری</a>
                        </div>

                        <form id="tour-analytic-dimensions-filters" method="GET" action="{{ route('Accounting.analyticDimensions') }}" class="card mb-4">
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
                                    <label class="form-label">بعد تحلیلی</label>
                                    <select name="dimension" class="form-select">
                                        @foreach ($report['dimensions'] as $key => $label)
                                            <option value="{{ $key }}" @selected($report['dimension'] === $key)>
                                                {{ $label }}</option>
                                        @endforeach
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
                                    <div class="alert alert-info mb-0 py-2">گردش ردیف های سند بر اساس تفصیل انتخابی
                                        تجمیع می شود.</div>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد ردیف سند</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['items_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">ردیف دارای تفصیل</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['assigned_items_count']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">ردیف ناقص</small>
                                        <h5 class="mb-0 text-end text-danger">
                                            {{ number_format((float) $report['summary']['missing_required_count']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">پوشش الزامات</small>
                                        <h5 class="mb-0 text-end text-success">
                                            {{ number_format((float) $report['summary']['coverage_percent'], 2) }}%
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">جمع بدهکار</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['debit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">جمع بستانکار</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['credit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tour-analytic-dimensions-table" class="card">
                            <div class="card-header">
                                <h5 class="mb-0">گردش به تفکیک {{ $report['dimensions'][$report['dimension']] }}
                                </h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>تفصیل</th>
                                            <th class="text-end">تعداد ردیف</th>
                                            <th class="text-end">بدهکار</th>
                                            <th class="text-end">بستانکار</th>
                                            <th class="text-end">خالص</th>
                                            <th>آخرین سند</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report['rows'] as $row)
                                            <tr>
                                                <td>{{ $row['label'] }}</td>
                                                <td class="text-end">{{ number_format((float) $row['items_count']) }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $row['debit']) }}</td>
                                                <td class="text-end">{{ number_format((float) $row['credit']) }}</td>
                                                <td
                                                    class="text-end {{ $row['net'] < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format((float) $row['net']) }}</td>
                                                <td>{{ $row['latest_voucher'] ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">گردشی برای
                                                    گزارش پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-4 mt-1">
                            <div class="col-12 col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">پوشش ابعاد تحلیلی</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>بعد</th>
                                                    <th class="text-end">ردیف دارای مقدار</th>
                                                    <th class="text-end">درصد</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($report['quality']['dimension_coverage'] as $coverage)
                                                    <tr>
                                                        <td>{{ $coverage['label'] }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $coverage['assigned']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $coverage['percent'], 2) }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ردیف های ناقص بر اساس الزام حساب</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>سند</th>
                                                    <th>حساب</th>
                                                    <th>شرح</th>
                                                    <th class="text-end">بدهکار</th>
                                                    <th class="text-end">بستانکار</th>
                                                    <th>کسری</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($report['quality']['missing_required_rows'] as $row)
                                                    <tr>
                                                        <td>{{ $row['voucher_number'] }}<br><small
                                                                class="text-muted">{{ $row['voucher_date'] }}</small>
                                                        </td>
                                                        <td>{{ $row['account'] }}</td>
                                                        <td>{{ $row['description'] }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['debit']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $row['credit']) }}</td>
                                                        <td><span
                                                                class="badge bg-label-danger">{{ $row['missing'] }}</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">برای
                                                            بازه فعلی ردیف ناقص پیدا نشد.</td>
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

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مانده ارزی و تسعیر - دکان دارمینو</title>
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
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> مانده ارزی و
                                تسعیر</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.vouchers.create') }}">ثبت
                                سند ارزی</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-xl-7">
                                <form method="GET" action="{{ route('Accounting.currencyBalances') }}"
                                    class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">فیلتر گزارش</h5>
                                    </div>
                                    <div class="card-body row g-3 align-items-end">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">از تاریخ</label>
                                            <input type="date" name="from_date" class="form-control"
                                                value="{{ request('from_date', $report['from_date']) }}">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">تا تاریخ</label>
                                            <input type="date" name="to_date" class="form-control"
                                                value="{{ request('to_date', $report['to_date']) }}">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">ارز</label>
                                            <select name="currency_id" class="form-select select2-basic">
                                                <option value="">همه ارزها</option>
                                                @foreach ($report['currencies'] as $currency)
                                                    <option value="{{ $currency->id }}" @selected((string) request('currency_id', $report['currency_id']) === (string) $currency->id)>
                                                        {{ $currency->code }} - {{ $currency->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="permanent_only"
                                                    value="1" id="permanent_only" @checked($report['permanent_only'])>
                                                <label class="form-check-label" for="permanent_only">فقط دائم</label>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">نمایش گزارش</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12 col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف ارز و نرخ روز</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('Accounting.currencies.store') }}"
                                            class="row g-2 mb-3">
                                            @csrf
                                            <div class="col-4"><input type="text" name="code"
                                                    class="form-control" placeholder="USD" required></div>
                                            <div class="col-4"><input type="text" name="title"
                                                    class="form-control" placeholder="دلار" required></div>
                                            <div class="col-2"><input type="text" name="symbol"
                                                    class="form-control" placeholder="$"></div>
                                            <div class="col-2"><button type="submit"
                                                    class="btn btn-outline-primary w-100">ارز</button></div>
                                        </form>
                                        <form method="POST" action="{{ route('Accounting.exchangeRates.store') }}"
                                            class="row g-2">
                                            @csrf
                                            <div class="col-12 col-md-4">
                                                <select name="currency_id" class="form-select select2-basic" required>
                                                    <option value="">انتخاب ارز</option>
                                                    @foreach ($report['currencies'] as $currency)
                                                        <option value="{{ $currency->id }}">{{ $currency->code }} -
                                                            {{ $currency->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-3"><input type="date" name="rate_date"
                                                    class="form-control" value="{{ $report['to_date'] }}" required>
                                            </div>
                                            <div class="col-6 col-md-3"><input type="number" min="0"
                                                    step="0.000001" name="rate" class="form-control text-end"
                                                    placeholder="نرخ" required></div>
                                            <div class="col-12 col-md-2"><button type="submit"
                                                    class="btn btn-outline-success w-100">نرخ</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد ارز فعال در گزارش</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['currencies_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مانده ریالی ثبت شده</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['local_balance']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مانده ریالی با نرخ روز</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['revalued_balance']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">اثر تسعیر</small>
                                        <h5
                                            class="mb-0 text-end {{ $report['summary']['revaluation_difference'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format((float) $report['summary']['revaluation_difference']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-1">مانده ارزی حساب ها</h5>
                                <small class="text-muted">مبلغ ریالی سند تغییر نمی کند؛ ستون تسعیر فقط اختلاف مانده با
                                    آخرین نرخ ثبت شده تا تاریخ گزارش را نشان می دهد.</small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>ارز</th>
                                            <th>حساب</th>
                                            <th class="text-end">ارزی بدهکار</th>
                                            <th class="text-end">ارزی بستانکار</th>
                                            <th class="text-end">مانده ارزی</th>
                                            <th class="text-end">مانده ریالی ثبت شده</th>
                                            <th class="text-end">نرخ تسعیر</th>
                                            <th class="text-end">مانده با نرخ روز</th>
                                            <th class="text-end">اختلاف تسعیر</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report['rows'] as $row)
                                            <tr>
                                                <td>{{ $row['currency']?->code }} - {{ $row['currency']?->title }}
                                                </td>
                                                <td>{{ $row['account']?->code }} - {{ $row['account']?->name }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['foreign_debit'], 4) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['foreign_credit'], 4) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['foreign_balance'], 4) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['local_balance']) }}</td>
                                                <td class="text-end">
                                                    {{ $row['latest_rate'] ? number_format((float) $row['latest_rate'], 6) : '-' }}
                                                    @if ($row['latest_rate_date'])
                                                        <div class="small text-muted">{{ $row['latest_rate_date'] }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['revalued_balance']) }}</td>
                                                <td
                                                    class="text-end {{ $row['revaluation_difference'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format((float) $row['revaluation_difference']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">ردیف ارزی برای
                                                    بازه انتخابی پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">آخرین نرخ های ثبت شده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>ارز</th>
                                            <th class="text-end">نرخ</th>
                                            <th>منبع</th>
                                            <th>شرح</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report['recent_rates'] as $rate)
                                            <tr>
                                                <td>{{ optional($rate->rate_date)->format('Y-m-d') }}</td>
                                                <td>{{ $rate->currency?->code }} - {{ $rate->currency?->title }}</td>
                                                <td class="text-end">{{ number_format((float) $rate->rate, 6) }}</td>
                                                <td>{{ $rate->source ?: '-' }}</td>
                                                <td>{{ $rate->description ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">نرخی ثبت نشده
                                                    است.</td>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $(function() {
            $('.select2-basic').select2({
                width: '100%'
            });
        });
    </script>
</body>

</html>

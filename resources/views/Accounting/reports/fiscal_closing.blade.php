<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>بستن دوره مالی - دکان دارمینو</title>
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
                        <div id="tour-fiscal-closing-page" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> بستن دوره مالی
                            </h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.legalLedgers') }}">دفاتر و
                                تراز آزمایشی</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        @php
                            $currentJalaliYear = (int) verta()->format('Y');
                            $hasCurrentYear = $fiscalYears->contains(
                                fn ($fiscalYear) => (int) verta($fiscalYear->starts_at)->format('Y') === $currentJalaliYear
                            );
                        @endphp

                        @if ($fiscalYears->isEmpty())
                            <div class="alert alert-warning">
                                هنوز سال مالی برای پنل شما ثبت نشده است. برای شروع حسابداری (از جمله سند افتتاحیه)، ابتدا سال مالی جاری را ایجاد کنید.
                            </div>
                        @elseif (!$hasCurrentYear)
                            <div class="alert alert-info">
                                سال مالی {{ $currentJalaliYear }} هنوز ثبت نشده است. در فرم زیر می‌توانید آن را اضافه کنید.
                            </div>
                        @endif

                        @if ($fiscalYears->isEmpty() || !$hasCurrentYear)
                            <form id="create-fiscal-year" method="POST" action="{{ route('Accounting.fiscalYears.store') }}" class="card mb-4">
                                @csrf
                                <div class="card-header">
                                    <h5 class="mb-0">ایجاد سال مالی</h5>
                                </div>
                                <div class="card-body row g-3 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">سال شمسی</label>
                                        <input type="number" name="jalali_year" class="form-control"
                                            value="{{ old('jalali_year', $currentJalaliYear) }}" min="1300" max="1500" required>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <div class="small text-muted mb-1">بازه پیشنهادی</div>
                                        <div>{{ $suggestedTitle ?? ('سال مالی ' . $currentJalaliYear) }}</div>
                                        <div class="small text-muted">
                                            {{ isset($suggestedStart) ? verta_date($suggestedStart) : '' }}
                                            تا
                                            {{ isset($suggestedEnd) ? verta_date($suggestedEnd) : '' }}
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">ایجاد سال مالی</button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        <form method="GET" action="{{ route('Accounting.fiscalClosing') }}" class="card mb-4">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label">سال مالی</label>
                                    <select name="fiscal_year_id" class="form-select fiscal-year-select">
                                        @foreach ($fiscalYears as $fiscalYear)
                                            <option value="{{ $fiscalYear->id }}" @selected($selectedFiscalYear && (int) $selectedFiscalYear->id === (int) $fiscalYear->id)>
                                                {{ $fiscalYear->title }} |
                                                {{ verta_date($fiscalYear->starts_at) }} تا
                                                {{ verta_date($fiscalYear->ends_at) }} |
                                                {{ $fiscalYear->status === 'closed' ? 'بسته' : 'باز' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">نمایش پیش نمایش</button>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="alert alert-warning mb-0 py-2">بستن دوره سند جدید می سازد و اطلاعات قبلی
                                        را تغییر نمی دهد.</div>
                                </div>
                            </div>
                        </form>

                        @if ($preview)
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body"><small class="text-muted">حساب های دارای مانده</small>
                                            <h5 class="mb-0 text-end">
                                                {{ number_format($preview['balances']->count()) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body"><small class="text-muted">بدهکار سند اختتامیه</small>
                                            <h5 class="mb-0 text-end">
                                                {{ number_format((float) $preview['closing_totals']['debit']) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body"><small class="text-muted">بستانکار سند اختتامیه</small>
                                            <h5 class="mb-0 text-end">
                                                {{ number_format((float) $preview['closing_totals']['credit']) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body"><small class="text-muted">افتتاحیه سال بعد</small>
                                            <h5 class="mb-0 text-end">
                                                {{ number_format((float) $preview['opening_totals']['debit']) }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div
                                    class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <h5 class="mb-1">پیش نمایش سند اختتامیه</h5>
                                        <small class="text-muted">درآمد و هزینه بسته می شود و مانده های دائمی به
                                            افتتاحیه سال بعد منتقل می شود.</small>
                                    </div>
                                    @if ($selectedFiscalYear && $selectedFiscalYear->status !== 'closed')
                                        <form id="tour-fiscal-closing-actions" method="POST"
                                            action="{{ route('Accounting.fiscalClosing.close', $selectedFiscalYear) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-danger"
                                                @disabled(!$preview['can_close'] || $preview['balances']->isEmpty())>بستن دوره و ساخت اسناد</button>
                                        </form>
                                    @else
                                        <span class="badge bg-label-secondary">سال مالی بسته است</span>
                                    @endif
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>کد حساب</th>
                                                <th>نام حساب</th>
                                                <th class="text-end">گردش بدهکار</th>
                                                <th class="text-end">گردش بستانکار</th>
                                                <th class="text-end">مانده</th>
                                                <th>نوع انتقال</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($preview['balances'] as $row)
                                                <tr>
                                                    <td>{{ $row['account']?->code }}</td>
                                                    <td>{{ $row['account']?->name }}</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $row['debit_amount']) }}</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $row['credit_amount']) }}</td>
                                                    <td
                                                        class="text-end {{ $row['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format(abs((float) $row['balance'])) }}
                                                        {{ $row['balance'] >= 0 ? 'بد' : 'بس' }}</td>
                                                    <td>{{ in_array($row['account']?->account_category, ['income', 'expense'], true) ? 'بستن به سود و زیان' : 'انتقال به افتتاحیه' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">مانده ای برای
                                                        بستن دوره پیدا نشد.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div id="tour-fiscal-closing-years" class="card">
                            <div class="card-header">
                                <h5 class="mb-0">آخرین دوره های بسته شده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>سال مالی</th>
                                            <th>سال بعد</th>
                                            <th>سند اختتامیه</th>
                                            <th>سند افتتاحیه</th>
                                            <th class="text-end">مبلغ</th>
                                            <th>تاریخ بستن</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($closings as $closing)
                                            <tr>
                                                <td>{{ $closing->fiscalYear?->title }}</td>
                                                <td>{{ $closing->nextFiscalYear?->title }}</td>
                                                <td>{{ $closing->closingVoucher?->voucher_number }}</td>
                                                <td>{{ $closing->openingVoucher?->voucher_number }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $closing->total_debit) }}</td>
                                                <td>{{ verta_datetime($closing->closed_at) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">هنوز دوره ای
                                                    بسته نشده است.</td>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $('.fiscal-year-select').select2({
            width: '100%'
        });
    </script>
</body>

</html>

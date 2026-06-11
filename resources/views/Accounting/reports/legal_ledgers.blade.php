<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>دفاتر و تراز آزمایشی - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> دفاتر و تراز
                                آزمایشی</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.vouchers') }}">اسناد
                                حسابداری</a>
                        </div>

                        <form method="GET" action="{{ route('Accounting.legalLedgers') }}" class="card mb-4">
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
                                <div class="col-12 col-md-4">
                                    <label class="form-label">حساب برای دفتر معین</label>
                                    <select name="account_id" class="form-select account-select">
                                        <option value="">همه حساب ها</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" @selected((int) $report['account_id'] === (int) $account->id)>
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
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
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">بدهکار روزنامه</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['journal_debit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">بستانکار روزنامه</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((float) $report['summary']['journal_credit']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">حساب های تراز</small>
                                        <h5 class="mb-0 text-end">
                                            {{ number_format((int) $report['summary']['accounts_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">اختلاف تراز</small>
                                        <h5
                                            class="mb-0 text-end {{ round((float) $report['summary']['trial_debit'] - (float) $report['summary']['trial_credit'], 2) == 0.0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format(abs((float) $report['summary']['trial_debit'] - (float) $report['summary']['trial_credit'])) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">دفتر روزنامه</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>سند</th>
                                            <th>حساب</th>
                                            <th>شرح</th>
                                            <th class="text-end">بدهکار</th>
                                            <th class="text-end">بستانکار</th>
                                            <th>مرکز هزینه</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report['journal_rows'] as $item)
                                            <tr>
                                                <td>{{ optional($item->voucher?->voucher_date_en)->format('Y-m-d') ?: optional($item->voucher)->voucher_date_fa }}
                                                </td>
                                                <td>{{ optional($item->voucher)->voucher_number ?: '-' }}</td>
                                                <td>{{ optional($item->account)->code }} -
                                                    {{ optional($item->account)->name }}</td>
                                                <td>{{ $item->description ?: optional($item->voucher)->description }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $item->debit_amount) }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $item->credit_amount) }}
                                                </td>
                                                <td>{{ optional($item->costCenter)->title ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">ردیف سندی در
                                                    بازه انتخاب شده پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if ($report['account_id'])
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">دفتر معین حساب انتخاب شده</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>تاریخ</th>
                                                <th>سند</th>
                                                <th>شرح</th>
                                                <th class="text-end">بدهکار</th>
                                                <th class="text-end">بستانکار</th>
                                                <th class="text-end">مانده</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($report['ledger_rows'] as $item)
                                                <tr>
                                                    <td>{{ optional($item->voucher?->voucher_date_en)->format('Y-m-d') ?: optional($item->voucher)->voucher_date_fa }}
                                                    </td>
                                                    <td>{{ optional($item->voucher)->voucher_number ?: '-' }}</td>
                                                    <td>{{ $item->description ?: optional($item->voucher)->description }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $item->debit_amount) }}</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $item->credit_amount) }}</td>
                                                    <td class="text-end">
                                                        {{ number_format((float) $item->running_balance) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">گردشی برای
                                                        حساب انتخاب شده وجود ندارد.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">تراز آزمایشی</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>کد حساب</th>
                                            <th>نام حساب</th>
                                            <th class="text-end">گردش بدهکار</th>
                                            <th class="text-end">گردش بستانکار</th>
                                            <th class="text-end">مانده بدهکار</th>
                                            <th class="text-end">مانده بستانکار</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report['trial_balance'] as $row)
                                            <tr>
                                                <td>{{ optional($row['account'])->code ?: '-' }}</td>
                                                <td>{{ optional($row['account'])->name ?: 'حساب حذف شده' }}</td>
                                                <td class="text-end">{{ number_format((float) $row['debit_amount']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['credit_amount']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['debit_balance']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row['credit_balance']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">ترازی برای بازه
                                                    انتخاب شده وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="2">جمع</td>
                                            <td class="text-end">
                                                {{ number_format((float) $report['summary']['trial_debit']) }}</td>
                                            <td class="text-end">
                                                {{ number_format((float) $report['summary']['trial_credit']) }}</td>
                                            <td class="text-end">
                                                {{ number_format((float) $report['summary']['trial_balance_debit']) }}
                                            </td>
                                            <td class="text-end">
                                                {{ number_format((float) $report['summary']['trial_balance_credit']) }}
                                            </td>
                                        </tr>
                                    </tfoot>
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
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $(function() {
            $('.account-select').select2({
                width: '100%'
            });
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مغایرت بانکی - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> مغایرت بانکی
                            </h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-dark"
                                    href="{{ route('Accounting.treasury.liquidity') }}">مانده
                                    نقدینگی</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury') }}">بازگشت</a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('Accounting.treasury.bankStatements.store') }}"
                            class="card mb-4">
                            @csrf
                            <div class="card-header">
                                <h5 class="mb-0">ثبت ردیف صورت حساب بانک</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">حساب بانک/خزانه</label>
                                        <select name="account_id" class="form-select">
                                            <option value="">انتخاب حساب</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}" @selected(old('account_id', request('account_id')) == $account->id)>
                                                    {{ $account->code }} - {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">تاریخ</label>
                                        <input type="date" name="statement_date" class="form-control"
                                            value="{{ old('statement_date', $today) }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">شماره پیگیری</label>
                                        <input type="text" name="reference_no" class="form-control"
                                            value="{{ old('reference_no') }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">واریز / بدهکار</label>
                                        <input type="number" min="0" step="0.01" name="debit_amount"
                                            class="form-control text-end" value="{{ old('debit_amount') }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">برداشت / بستانکار</label>
                                        <input type="number" min="0" step="0.01" name="credit_amount"
                                            class="form-control text-end" value="{{ old('credit_amount') }}">
                                    </div>
                                    <div class="col-12 col-md-1 d-grid">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">شرح</label>
                                        <input type="text" name="description" class="form-control"
                                            value="{{ old('description') }}">
                                    </div>
                                </div>
                            </div>
                        </form>

                        <form method="GET" action="{{ route('Accounting.treasury.bankReconciliation') }}"
                            class="card mb-4">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">حساب</label>
                                        <select name="account_id" class="form-select">
                                            <option value="">همه حساب ها</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}" @selected(request('account_id') == $account->id)>
                                                    {{ $account->code }} - {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">وضعیت</label>
                                        <select name="status" class="form-select">
                                            <option value="">همه</option>
                                            @foreach ($statuses as $status => $label)
                                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">از تاریخ</label>
                                        <input type="date" name="date_from" class="form-control"
                                            value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">تا تاریخ</label>
                                        <input type="date" name="date_to" class="form-control"
                                            value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">فیلتر</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>تاریخ</th>
                                            <th>حساب</th>
                                            <th>پیگیری</th>
                                            <th class="text-end">واریز</th>
                                            <th class="text-end">برداشت</th>
                                            <th>وضعیت</th>
                                            <th>سند تطبیق</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lines as $line)
                                            <tr>
                                                <td>{{ $loop->iteration + ($lines->currentPage() - 1) * $lines->perPage() }}
                                                </td>
                                                <td>{{ optional($line->statement_date)->format('Y-m-d') ?: '-' }}</td>
                                                <td>{{ optional($line->account)->code }} -
                                                    {{ optional($line->account)->name }}
                                                </td>
                                                <td>{{ $line->reference_no ?: '-' }}</td>
                                                <td class="text-end">{{ number_format((float) $line->debit_amount) }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $line->credit_amount) }}
                                                </td>
                                                <td>{{ $statuses[$line->status] ?? $line->status }}</td>
                                                <td>{{ optional($line->voucher)->voucher_number ?: '-' }}</td>
                                                <td style="min-width: 280px">
                                                    <form method="POST"
                                                        action="{{ route('Accounting.treasury.bankStatements.reconcile', $line) }}"
                                                        class="d-flex flex-wrap gap-1">
                                                        @csrf
                                                        <select name="voucher_id" class="form-select form-select-sm"
                                                            style="min-width: 170px">
                                                            <option value="">انتخاب سند</option>
                                                            @foreach ($candidateVouchers as $voucher)
                                                                <option value="{{ $voucher->id }}"
                                                                    @selected($line->voucher_id == $voucher->id)>
                                                                    {{ $voucher->voucher_number }} -
                                                                    {{ number_format((float) $voucher->total_debit) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" name="action" value="match"
                                                            class="btn btn-sm btn-outline-success">تطبیق</button>
                                                        <button type="submit" name="action" value="ignore"
                                                            class="btn btn-sm btn-outline-warning">نادیده</button>
                                                        <button type="submit" name="action" value="reset"
                                                            class="btn btn-sm btn-outline-secondary">بازگشت</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @if ($line->description)
                                                <tr>
                                                    <td></td>
                                                    <td colspan="8" class="text-muted small">
                                                        {{ $line->description }}</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">ردیف صورت حسابی
                                                    ثبت
                                                    نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            {{ $lines->links() }}
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

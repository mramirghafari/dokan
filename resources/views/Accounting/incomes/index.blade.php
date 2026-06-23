<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>درآمدها - دکان دارمینو</title>
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
                        <div id="tour-incomes-page-header" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> درآمدها</h4>
                            <a id="tour-incomes-profit-link" class="btn btn-outline-secondary" href="{{ route('Accounting.financialStatements') }}">
                                <x-ui.icon name="chart-pie" class="me-1" /> گزارش سود
                            </a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-4">
                                <form id="tour-incomes-type-form" class="card mb-4" method="POST"
                                    action="{{ route('Accounting.incomes.types.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف نوع درآمد</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">کد</label>
                                            <input type="text" name="code" class="form-control"
                                                value="{{ old('code') }}">
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <label class="form-label">نام نوع درآمد</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name') }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">گروه درآمد</label>
                                            <select name="income_group" class="form-select">
                                                @foreach ($incomeGroups as $group => $label)
                                                    <option value="{{ $group }}" @selected(old('income_group', 'operational') === $group)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">حساب درآمد پیش فرض</label>
                                            <select name="account_id" class="form-select select2-basic">
                                                <option value="">حساب خودکار سیستم</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}" @selected((string) old('account_id') === (string) $account->id)>
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description') }}">
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-outline-primary" type="submit">ثبت نوع درآمد</button>
                                    </div>
                                </form>

                                <div id="tour-incomes-center-summary" class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">خلاصه مرکز درآمد</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>مرکز</th>
                                                    <th class="text-center">تعداد</th>
                                                    <th class="text-end">مبلغ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($revenueCenterSummaries as $summary)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $summary['revenue_center'] }}
                                                            </div>
                                                            <small class="text-muted">{{ $summary['store'] }}</small>
                                                        </td>
                                                        <td class="text-center">{{ $summary['count'] }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $summary['total']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-3">هنوز
                                                            درآمدی ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-8">
                                <form id="tour-incomes-entry-form" class="card mb-4" method="POST" action="{{ route('Accounting.incomes.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div
                                        class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <h5 class="mb-0">ثبت درآمد ساده</h5>
                                        <span
                                            class="badge bg-label-primary">{{ number_format((float) $totals['amount']) }}
                                            جمع فیلتر</span>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">تاریخ</label>
                                            <input type="date" name="income_date_en" class="form-control"
                                                value="{{ old('income_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">نوع درآمد</label>
                                            <select name="income_type_id" class="form-select select2-basic" required>
                                                <option value="">انتخاب نوع درآمد</option>
                                                @foreach ($incomeTypes as $type)
                                                    <option value="{{ $type->id }}" @selected((string) old('income_type_id') === (string) $type->id)>
                                                        {{ $type->code }} - {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">مرکز درآمد</label>
                                            <select name="revenue_center_id" class="form-select select2-basic"
                                                required>
                                                <option value="">انتخاب مرکز درآمد</option>
                                                @foreach ($revenueCenters as $center)
                                                    <option value="{{ $center->id }}" @selected((string) old('revenue_center_id') === (string) $center->id)>
                                                        {{ $center->code }} - {{ $center->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">حساب دریافت/نقد</label>
                                            <select name="receipt_account_id" class="form-select select2-basic"
                                                required>
                                                <option value="">انتخاب حساب</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}" @selected((string) old('receipt_account_id') === (string) $account->id)>
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">حساب درآمد</label>
                                            <select name="income_account_id" class="form-select select2-basic">
                                                <option value="">از نوع درآمد/سیستم</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}" @selected((string) old('income_account_id') === (string) $account->id)>
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">شعبه/انبار</label>
                                            <select name="store_id" class="form-select select2-basic">
                                                <option value="">از مرکز درآمد</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}" @selected((string) old('store_id') === (string) $store->id)>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">مبلغ</label>
                                            <input type="number" step="0.01" min="0.01" name="amount"
                                                class="form-control" value="{{ old('amount') }}" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">شماره مرجع</label>
                                            <input type="text" name="reference_number" class="form-control"
                                                value="{{ old('reference_number') }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">فایل مدرک</label>
                                            <input type="file" name="attachment_file" class="form-control"
                                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">یادداشت مدرک</label>
                                            <input type="text" name="attachment_note" class="form-control"
                                                value="{{ old('attachment_note') }}"
                                                placeholder="مثلا رسید، قرارداد، قبض یا حواله">
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" type="submit">ثبت درآمد و سند</button>
                                    </div>
                                </form>

                                <form class="card mb-4" method="GET" action="{{ route('Accounting.incomes') }}">
                                    <div class="card-body row g-3 align-items-end">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">نوع درآمد</label>
                                            <select name="income_type_id" class="form-select select2-basic">
                                                <option value="">همه</option>
                                                @foreach ($incomeTypes as $type)
                                                    <option value="{{ $type->id }}" @selected((string) request('income_type_id') === (string) $type->id)>
                                                        {{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">مرکز درآمد</label>
                                            <select name="revenue_center_id" class="form-select select2-basic">
                                                <option value="">همه</option>
                                                @foreach ($revenueCenters as $center)
                                                    <option value="{{ $center->id }}" @selected((string) request('revenue_center_id') === (string) $center->id)>
                                                        {{ $center->name }}</option>
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
                                        <div class="col-12 col-md-2 text-end">
                                            <button class="btn btn-outline-primary w-100"
                                                type="submit">فیلتر</button>
                                        </div>
                                    </div>
                                </form>

                                <div id="tour-incomes-table" class="card">
                                    <div class="card-header d-flex flex-wrap justify-content-between gap-2">
                                        <h5 class="mb-0">لیست درآمدها</h5>
                                        <span class="text-muted">{{ number_format((float) $totals['count']) }}
                                            ردیف</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>شماره</th>
                                                    <th>تاریخ</th>
                                                    <th>نوع/مرکز</th>
                                                    <th class="text-end">مبلغ</th>
                                                    <th>سند</th>
                                                    <th>مدارک</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($incomes as $income)
                                                    <tr>
                                                        <td>{{ $income->income_number }}</td>
                                                        <td>{{ $income->income_date_fa ?: verta_date($income->income_date_en) }}</td>
                                                        <td>
                                                            <div class="fw-semibold">
                                                                {{ optional($income->incomeType)->name ?: '-' }}</div>
                                                            <small
                                                                class="text-muted">{{ optional($income->revenueCenter)->name ?: '-' }}</small>
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $income->amount) }}</td>
                                                        <td>{{ optional($income->voucher)->voucher_number ?: '-' }}
                                                        </td>
                                                        <td style="min-width: 260px">
                                                            @forelse($income->financialAttachments as $attachment)
                                                                <a class="badge bg-label-secondary mb-1"
                                                                    target="_blank" href="{{ $attachment->url }}">
                                                                    {{ $attachment->original_name ?: 'مشاهده مدرک' }}
                                                                </a>
                                                            @empty
                                                                <span class="text-muted d-block mb-1">بدون مدرک</span>
                                                            @endforelse
                                                            <form method="POST"
                                                                action="{{ route('Accounting.incomes.attachments.store', $income) }}"
                                                                enctype="multipart/form-data"
                                                                class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                                                @csrf
                                                                <input type="file" name="attachment_file"
                                                                    class="form-control form-control-sm" required
                                                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                                                <input type="text" name="attachment_note"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="یادداشت">
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">افزودن</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">درآمدی
                                                            ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $incomes->links() }}</div>
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

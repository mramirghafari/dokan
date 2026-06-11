<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>حقوق و دستمزد - دکان دارمینو</title>
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
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="mb-4"><span class="text-muted fw-light">مالی و حسابداری /</span> حقوق و دستمزد</h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md">
                                <div class="card p-3"><small
                                        class="text-muted">ناخالص</small><strong>{{ number_format($totals['gross']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="card p-3"><small class="text-muted">خالص
                                        پرداختنی</small><strong>{{ number_format($totals['net']) }}</strong></div>
                            </div>
                            <div class="col-md">
                                <div class="card p-3"><small
                                        class="text-muted">مالیات</small><strong>{{ number_format($totals['tax']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="card p-3"><small
                                        class="text-muted">بیمه</small><strong>{{ number_format($totals['insurance']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="card p-3"><small class="text-muted">پرداخت
                                        شده</small><strong>{{ number_format($totals['paid']) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">قرارداد حقوقی پرسنل</h5>
                                    </div>
                                    <form method="POST" action="{{ route('Accounting.payroll.contracts.store') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">پرسنل</label>
                                                    <select class="form-select" name="employee_id" required>
                                                        @foreach ($employees as $employee)
                                                            <option value="{{ $employee->id }}">{{ $employee->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">نوع قرارداد</label>
                                                    <select class="form-select" name="contract_type" required>
                                                        <option value="monthly">ماهیانه</option>
                                                        <option value="daily">روزانه</option>
                                                        <option value="hourly">ساعتی</option>
                                                        <option value="project">پروژه ای</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">عنوان شغلی</label>
                                                    <input class="form-control" name="job_title">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">شروع قرارداد</label>
                                                    <input class="form-control" name="start_date_en" type="date"
                                                        value="{{ $today }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">حقوق ماهانه</label>
                                                    <input class="form-control" name="base_salary" type="number"
                                                        min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">مزد روزانه</label>
                                                    <input class="form-control" name="daily_wage" type="number"
                                                        min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">مزد ساعتی</label>
                                                    <input class="form-control" name="hourly_wage" type="number"
                                                        min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">مزایای ثابت</label>
                                                    <input class="form-control" name="fixed_allowance_amount"
                                                        type="number" min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">معافیت مالیاتی</label>
                                                    <input class="form-control" name="tax_exemption_amount"
                                                        type="number" min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">نرخ مالیات %</label>
                                                    <input class="form-control" name="tax_rate" type="number"
                                                        min="0" max="100" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">بیمه کارمند %</label>
                                                    <input class="form-control" name="employee_insurance_rate"
                                                        type="number" min="0" max="100" step="0.01"
                                                        value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">بیمه کارفرما %</label>
                                                    <input class="form-control" name="employer_insurance_rate"
                                                        type="number" min="0" max="100" step="0.01"
                                                        value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">روز کار استاندارد</label>
                                                    <input class="form-control" name="work_days_per_month"
                                                        type="number" min="0" max="31" step="0.01"
                                                        value="30">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">وضعیت</label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="active">فعال</option>
                                                        <option value="closed">خاتمه یافته</option>
                                                        <option value="suspended">تعلیق</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">شرح</label>
                                                    <input class="form-control" name="description">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button class="btn btn-outline-primary" type="submit"
                                                @disabled($employees->isEmpty())>ثبت قرارداد</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">خلاصه کارکرد ماهانه</h5>
                                    </div>
                                    <form method="POST" action="{{ route('Accounting.payroll.attendance.store') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">پرسنل</label>
                                                    <select class="form-select" name="employee_id" required>
                                                        @foreach ($employees as $employee)
                                                            <option value="{{ $employee->id }}">{{ $employee->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">سال</label>
                                                    <input class="form-control" name="period_year" type="number"
                                                        value="{{ $periodYear }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">ماه</label>
                                                    <input class="form-control" name="period_month" type="number"
                                                        min="1" max="12" value="{{ $periodMonth }}"
                                                        required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">روز کارکرد</label>
                                                    <input class="form-control" name="work_days" type="number"
                                                        min="0" max="31" step="0.01" value="30">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">ساعت کارکرد</label>
                                                    <input class="form-control" name="work_hours" type="number"
                                                        min="0" step="0.01" value="220">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">اضافه کاری</label>
                                                    <input class="form-control" name="overtime_hours" type="number"
                                                        min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">غیبت</label>
                                                    <input class="form-control" name="absence_days" type="number"
                                                        min="0" max="31" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">مرخصی</label>
                                                    <input class="form-control" name="leave_days" type="number"
                                                        min="0" max="31" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">ماموریت</label>
                                                    <input class="form-control" name="mission_days" type="number"
                                                        min="0" max="31" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">وضعیت</label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="approved">تایید شده</option>
                                                        <option value="draft">پیش نویس</option>
                                                        <option value="locked">قفل شده</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">شرح</label>
                                                    <input class="form-control" name="description">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button class="btn btn-outline-primary" type="submit"
                                                @disabled($employees->isEmpty())>ثبت کارکرد</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">ثبت لیست حقوق</h5>
                            </div>
                            <form method="POST" action="{{ route('Accounting.payroll.store') }}">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">عنوان</label>
                                            <input class="form-control" name="title"
                                                value="{{ old('title', 'حقوق ' . $periodYear . '/' . $periodMonth) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">سال</label>
                                            <input class="form-control" name="period_year" type="number"
                                                value="{{ old('period_year', $periodYear) }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">ماه</label>
                                            <input class="form-control" name="period_month" type="number"
                                                min="1" max="12"
                                                value="{{ old('period_month', $periodMonth) }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">تاریخ سند</label>
                                            <input class="form-control" name="payroll_date_en" type="date"
                                                value="{{ old('payroll_date_en', $today) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">شرح</label>
                                            <input class="form-control" name="description"
                                                value="{{ old('description') }}">
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>پرسنل</th>
                                                    <th>حقوق پایه</th>
                                                    <th>کارکرد</th>
                                                    <th>حقوق پایه</th>
                                                    <th>مزایا/اضافه کاری</th>
                                                    <th>پاداش/ماموریت</th>
                                                    <th>بیمه سهم کارمند</th>
                                                    <th>بیمه سهم کارفرما</th>
                                                    <th>مالیات/کسورات</th>
                                                    <th>شرح ردیف</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($employees as $employee)
                                                    <tr>
                                                        <td>
                                                            {{ $employee->name }}
                                                            @if ($employee->activePayrollContract)
                                                                <br><small
                                                                    class="text-muted">{{ $employee->activePayrollContract->contract_number }}</small>
                                                            @endif
                                                            <input type="hidden" name="employee_id[]"
                                                                value="{{ $employee->id }}">
                                                        </td>
                                                        <td style="min-width:180px">
                                                            <input class="form-control form-control-sm mb-1"
                                                                name="work_days[]" type="number" min="0"
                                                                step="0.01" placeholder="روز کارکرد">
                                                            <input class="form-control form-control-sm mb-1"
                                                                name="work_hours[]" type="number" min="0"
                                                                step="0.01" placeholder="ساعت کارکرد">
                                                            <input class="form-control form-control-sm"
                                                                name="overtime_hours[]" type="number" min="0"
                                                                step="0.01" placeholder="ساعت اضافه کاری">
                                                        </td>
                                                        <td><input class="form-control form-control-sm"
                                                                name="base_salary[]" type="number" min="0"
                                                                step="0.01" placeholder="خودکار از قرارداد"></td>
                                                        <td style="min-width:170px"><input
                                                                class="form-control form-control-sm mb-1"
                                                                name="benefits_amount[]" type="number"
                                                                min="0" step="0.01" placeholder="مزایا">
                                                            <input class="form-control form-control-sm"
                                                                name="overtime_amount[]" type="number"
                                                                min="0" step="0.01"
                                                                placeholder="مبلغ اضافه کاری">
                                                        </td>
                                                        <td style="min-width:170px"><input
                                                                class="form-control form-control-sm mb-1"
                                                                name="bonus_amount[]" type="number" min="0"
                                                                step="0.01" placeholder="پاداش">
                                                            <input class="form-control form-control-sm"
                                                                name="mission_amount[]" type="number" min="0"
                                                                step="0.01" placeholder="ماموریت">
                                                        </td>
                                                        <td><input class="form-control form-control-sm"
                                                                name="employee_insurance_amount[]" type="number"
                                                                min="0" step="0.01" placeholder="خودکار">
                                                        </td>
                                                        <td><input class="form-control form-control-sm"
                                                                name="employer_insurance_amount[]" type="number"
                                                                min="0" step="0.01" placeholder="خودکار">
                                                        </td>
                                                        <td style="min-width:170px"><input
                                                                class="form-control form-control-sm mb-1"
                                                                name="tax_amount[]" type="number" min="0"
                                                                step="0.01" placeholder="مالیات">
                                                            <input class="form-control form-control-sm mb-1"
                                                                name="other_deductions_amount[]" type="number"
                                                                min="0" step="0.01"
                                                                placeholder="سایر کسورات">
                                                            <input class="form-control form-control-sm mb-1"
                                                                name="loan_deduction_amount[]" type="number"
                                                                min="0" step="0.01" placeholder="وام">
                                                            <input class="form-control form-control-sm"
                                                                name="advance_deduction_amount[]" type="number"
                                                                min="0" step="0.01" placeholder="مساعده">
                                                        </td>
                                                        <td><input class="form-control form-control-sm"
                                                                name="item_description[]"
                                                                value="حقوق {{ $employee->name }}"></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted py-3">پرسنل
                                                            فعالی برای ثبت حقوق پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit" @disabled($employees->isEmpty())>ثبت
                                        لیست حقوق و سند حسابداری</button>
                                </div>
                            </form>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">لیست های حقوق</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره</th>
                                            <th>دوره</th>
                                            <th>وضعیت</th>
                                            <th>ناخالص</th>
                                            <th>خالص</th>
                                            <th>پرداخت</th>
                                            <th>سند حسابداری</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payrollRuns as $run)
                                            @php
                                                $payable = (float) ($run->payable_amount ?: $run->net_pay_amount);
                                                $paid = (float) $run->paid_amount;
                                                $remaining = max(0, $payable - $paid);
                                            @endphp
                                            <tr>
                                                <td>{{ $run->number }}<br><small
                                                        class="text-muted">{{ $run->title }}</small></td>
                                                <td>{{ $run->period_year }}/{{ $run->period_month }}</td>
                                                <td>{{ $run->status === 'canceled' ? 'باطل شده' : 'تایید شده' }}</td>
                                                <td>{{ number_format((float) $run->gross_salary) }}</td>
                                                <td>{{ number_format((float) $run->net_pay_amount) }}</td>
                                                <td>
                                                    {{ $run->payment_status === 'paid' ? 'تسویه شده' : ($run->payment_status === 'partial' ? 'نیمه پرداخت' : 'پرداخت نشده') }}<br>
                                                    <small class="text-muted">{{ number_format($paid) }} /
                                                        {{ number_format($payable) }}</small>
                                                </td>
                                                <td>
                                                    @if ($run->accountingVoucher)
                                                        {{ $run->accountingVoucher->voucher_number }}<br>
                                                        <small
                                                            class="text-muted">{{ $run->accountingVoucher->status === 'draft' ? 'موقت' : $run->accountingVoucher->status }}</small>
                                                    @else
                                                        <span class="text-muted">ثبت نشده</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($run->status !== 'canceled' && $remaining <= 0)
                                                        <span class="badge bg-label-success">تکمیل</span>
                                                    @endif
                                                    @if ($run->status !== 'canceled' && $paid <= 0)
                                                        <form method="POST"
                                                            action="{{ route('Accounting.payroll.cancel', $run) }}"
                                                            onsubmit="return confirm('لیست حقوق ابطال شود؟')">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                type="submit">ابطال</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="8" class="bg-light">
                                                    <div class="row g-3">
                                                        <div class="col-lg-7">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>پرسنل</th>
                                                                            <th>کارکرد</th>
                                                                            <th>اجزای فیش</th>
                                                                            <th>خالص</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($run->items as $item)
                                                                            <tr>
                                                                                <td>{{ optional($item->employee)->name }}<br><small
                                                                                        class="text-muted">{{ optional($item->contract)->contract_number ?: 'بدون قرارداد' }}</small>
                                                                                </td>
                                                                                <td>{{ number_format((float) $item->work_days, 2) }}
                                                                                    روز<br><small
                                                                                        class="text-muted">{{ number_format((float) $item->overtime_hours, 2) }}
                                                                                        ساعت اضافه کاری</small></td>
                                                                                <td>
                                                                                    @foreach ($item->components as $component)
                                                                                        <span
                                                                                            class="badge bg-label-{{ $component->component_type === 'earning' ? 'success' : 'warning' }} mb-1">{{ $component->title }}:
                                                                                            {{ number_format((float) $component->amount) }}</span>
                                                                                    @endforeach
                                                                                </td>
                                                                                <td>{{ number_format((float) $item->net_pay_amount) }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-5">
                                                            <div class="mb-2">
                                                                <strong>گزارش بیمه و مالیات</strong><br>
                                                                <small class="text-muted">مشمول بیمه:
                                                                    {{ number_format((float) data_get($run->legal_report_json, 'insurance_subject_amount', 0)) }}
                                                                    | مشمول مالیات:
                                                                    {{ number_format((float) data_get($run->legal_report_json, 'taxable_amount', 0)) }}</small>
                                                            </div>
                                                            @if ($run->status !== 'canceled' && $remaining > 0)
                                                                <form method="POST"
                                                                    action="{{ route('Accounting.payroll.payments.store', $run) }}">
                                                                    @csrf
                                                                    <div class="row g-2 align-items-end">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">مبلغ
                                                                                پرداخت</label>
                                                                            <input class="form-control form-control-sm"
                                                                                name="amount" type="number"
                                                                                min="0.01" step="0.01"
                                                                                value="{{ $remaining }}" required>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">روش</label>
                                                                            <select class="form-select form-select-sm"
                                                                                name="payment_method" required>
                                                                                <option value="3">بانک</option>
                                                                                <option value="1">نقد</option>
                                                                                <option value="2">کارتخوان
                                                                                </option>
                                                                                <option value="4">چک</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">تاریخ</label>
                                                                            <input class="form-control form-control-sm"
                                                                                name="payment_date_en" type="date"
                                                                                value="{{ $today }}">
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <input class="form-control form-control-sm"
                                                                                name="description"
                                                                                value="پرداخت حقوق {{ $run->period_year }}/{{ $run->period_month }}">
                                                                        </div>
                                                                        <div class="col-12 text-end">
                                                                            <button class="btn btn-sm btn-primary"
                                                                                type="submit">ثبت پرداخت و
                                                                                سند</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            @endif
                                                            @if ($run->payments->isNotEmpty())
                                                                <div class="mt-3">
                                                                    @foreach ($run->payments as $payment)
                                                                        <div
                                                                            class="d-flex justify-content-between border-top py-1">
                                                                            <span>{{ $payment->payment_number }}</span>
                                                                            <span>{{ number_format((float) $payment->amount) }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">هنوز لیست حقوق
                                                    ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $payrollRuns->links() }}</div>
                        </div>
                    </div>
                    @include('sections.footer')
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

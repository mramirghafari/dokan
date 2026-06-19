<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>اموال شرکت - دکان دارمینو</title>
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
                        <div id="tour-company-assets-page" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> اموال شرکت
                            </h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-primary"
                                    href="{{ route('Accounting.companyAssets.report') }}">
                                    <x-ui.icon name="chart-bar" class="me-1" /> گزارش تکمیلی
                                </a>
                                <a class="btn btn-outline-secondary" href="{{ route('Accounting.expenses') }}">
                                    <x-ui.icon name="receipt" class="me-1" /> هزینه ها
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.financialStatements') }}">
                                    <x-ui.icon name="report-money" class="me-1" /> صورت های مالی
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد اموال</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">فعال در بهره برداری</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['active']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">ارزش تحصیل</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['cost']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">ارزش دفتری</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['book_value']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-5">
                                <form id="tour-company-assets-form" class="card h-100" method="POST"
                                    action="{{ route('Accounting.companyAssets.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت اموال جدید</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">کد دارایی</label>
                                            <input type="text" name="asset_code" class="form-control"
                                                value="{{ old('asset_code') }}" placeholder="خالی بماند خودکار می شود">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شماره پلاک</label>
                                            <input type="text" name="plaque_number" class="form-control"
                                                value="{{ old('plaque_number') }}">
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <label class="form-label">نام دارایی</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name') }}" required>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">طبقه دارایی</label>
                                            <select name="asset_category" class="form-select" required>
                                                @foreach ($assetCategories as $category => $label)
                                                    <option value="{{ $category }}" @selected(old('asset_category', 'office_equipment') === $category)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">تاریخ تحصیل</label>
                                            <input type="date" name="acquisition_date_en" class="form-control"
                                                value="{{ old('acquisition_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">تاریخ بهره برداری</label>
                                            <input type="date" name="in_service_date_en" class="form-control"
                                                value="{{ old('in_service_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">محل/انبار استقرار</label>
                                            <select name="store_id" class="form-select select2-basic">
                                                <option value="">بدون اتصال</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}" @selected((string) old('store_id') === (string) $store->id)>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">مرکز هزینه</label>
                                            <select name="cost_center_id" class="form-select select2-basic">
                                                <option value="">بدون مرکز هزینه</option>
                                                @foreach ($costCenters as $costCenter)
                                                    <option value="{{ $costCenter->id }}"
                                                        @selected((string) old('cost_center_id') === (string) $costCenter->id)>{{ $costCenter->code }} -
                                                        {{ $costCenter->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">تحویل گیرنده</label>
                                            <select name="custodian_employee_id" class="form-select select2-basic">
                                                <option value="">ثبت نشده</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}" @selected((string) old('custodian_employee_id') === (string) $employee->id)>
                                                        {{ $employee->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">موقعیت دقیق</label>
                                            <input type="text" name="location" class="form-control"
                                                value="{{ old('location') }}" placeholder="مثلا طبقه ۲، اتاق فروش">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">ارزش تحصیل</label>
                                            <input type="number" min="0" step="0.01"
                                                name="acquisition_cost" class="form-control text-end"
                                                value="{{ old('acquisition_cost') }}" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">ارزش اسقاط</label>
                                            <input type="number" min="0" step="0.01" name="salvage_value"
                                                class="form-control text-end" value="{{ old('salvage_value', 0) }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">استهلاک انباشته</label>
                                            <input type="number" min="0" step="0.01"
                                                name="accumulated_depreciation" class="form-control text-end"
                                                value="{{ old('accumulated_depreciation', 0) }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">عمر مفید - ماه</label>
                                            <input type="number" min="1" step="1"
                                                name="useful_life_months" class="form-control text-end"
                                                value="{{ old('useful_life_months') }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">روش استهلاک</label>
                                            <select name="depreciation_method" class="form-select">
                                                <option value="straight_line">خط مستقیم</option>
                                                <option value="none">بدون محاسبه</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">وضعیت</label>
                                            <select name="status" class="form-select">
                                                @foreach ($assetStatuses as $status => $label)
                                                    <option value="{{ $status }}" @selected(old('status', 'active') === $status)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شماره سریال</label>
                                            <input type="text" name="serial_number" class="form-control"
                                                value="{{ old('serial_number') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">حساب دارایی</label>
                                            <select name="asset_account_id" class="form-select account-select">
                                                <option value="">انتخاب نشده</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">حساب استهلاک انباشته</label>
                                            <select name="accumulated_depreciation_account_id"
                                                class="form-select account-select">
                                                <option value="">انتخاب نشده</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">حساب هزینه استهلاک</label>
                                            <select name="depreciation_expense_account_id"
                                                class="form-select account-select">
                                                <option value="">انتخاب نشده</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
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
                                        <button class="btn btn-primary" type="submit">ثبت در دفتر اموال</button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-7">
                                <form class="card mb-4" method="POST"
                                    action="{{ route('Accounting.companyAssets.depreciation.post') }}">
                                    @csrf
                                    <div
                                        class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h5 class="mb-1">سند خودکار استهلاک</h5>
                                            <small class="text-muted">برای دارایی های فیلتر شده و دارای حساب استهلاک،
                                                سند موقت دوبل ساخته می شود.</small>
                                        </div>
                                        <span
                                            class="badge bg-label-primary">{{ number_format($depreciationSummary['count']) }}
                                            دارایی آماده</span>
                                    </div>
                                    <div class="card-body row g-3 align-items-end">
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                        <input type="hidden" name="asset_category"
                                            value="{{ request('asset_category') }}">
                                        <input type="hidden" name="cost_center_id"
                                            value="{{ request('cost_center_id') }}">
                                        <input type="hidden" name="store_id" value="{{ request('store_id') }}">
                                        <input type="hidden" name="q" value="{{ request('q') }}">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">از تاریخ</label>
                                            <input type="date" name="depreciation_from" class="form-control"
                                                value="{{ $depreciationFrom }}" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">تا تاریخ</label>
                                            <input type="date" name="depreciation_to" class="form-control"
                                                value="{{ $depreciationTo }}" required>
                                        </div>
                                        <div class="col-12 col-md-4 text-end">
                                            <div class="small text-muted mb-1">جمع استهلاک قابل ثبت</div>
                                            <h5 class="mb-2">{{ number_format($depreciationSummary['amount']) }}
                                            </h5>
                                            <button class="btn btn-success" type="submit"
                                                @disabled($depreciationSummary['count'] === 0)>
                                                صدور سند استهلاک
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <div class="table-responsive border rounded">
                                                <table class="table table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>دارایی</th>
                                                            <th class="text-end">استهلاک دوره</th>
                                                            <th class="text-end">مانده اول</th>
                                                            <th class="text-end">مانده آخر</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($depreciationPreview->take(5) as $row)
                                                            <tr>
                                                                <td>{{ $row['asset']->asset_code }} -
                                                                    {{ $row['asset']->name }}
                                                                    @if ($row['policy'])
                                                                        <span class="badge bg-label-info ms-1">سیاست
                                                                            تاریخ دار</span>
                                                                    @endif
                                                                    <div class="small text-muted">
                                                                        {{ $row['depreciation_method'] === 'rate_percent' ? 'نرخ سالانه ' . number_format((float) $row['annual_rate_percent'], 2) . '%' : 'خط مستقیم / عمر ' . number_format((int) $row['useful_life_months']) . ' ماه' }}
                                                                    </div>
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($row['period_amount']) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($row['book_value_before']) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($row['book_value_after']) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4"
                                                                    class="text-center text-muted py-3">دارایی آماده
                                                                    صدور سند استهلاک در این فیلتر وجود ندارد.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <form class="card mb-4" method="GET"
                                    action="{{ route('Accounting.companyAssets') }}">
                                    <div class="card-body row g-3 align-items-end">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">جستجو</label>
                                            <input type="text" name="q" class="form-control"
                                                value="{{ request('q') }}" placeholder="نام، کد، پلاک یا سریال">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">طبقه</label>
                                            <select name="asset_category" class="form-select">
                                                <option value="">همه</option>
                                                @foreach ($assetCategories as $category => $label)
                                                    <option value="{{ $category }}" @selected(request('asset_category') === $category)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">وضعیت</label>
                                            <select name="status" class="form-select">
                                                <option value="">همه</option>
                                                @foreach ($assetStatuses as $status => $label)
                                                    <option value="{{ $status }}" @selected(request('status') === $status)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">مرکز هزینه</label>
                                            <select name="cost_center_id" class="form-select select2-basic">
                                                <option value="">همه</option>
                                                @foreach ($costCenters as $costCenter)
                                                    <option value="{{ $costCenter->id }}"
                                                        @selected((string) request('cost_center_id') === (string) $costCenter->id)>{{ $costCenter->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">محل/انبار</label>
                                            <select name="store_id" class="form-select select2-basic">
                                                <option value="">همه</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}" @selected((string) request('store_id') === (string) $store->id)>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-2 text-end">
                                            <button class="btn btn-outline-primary w-100"
                                                type="submit">فیلتر</button>
                                        </div>
                                    </div>
                                </form>

                                <div id="tour-company-assets-table" class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">دفتر اموال</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>کد/پلاک</th>
                                                    <th>دارایی</th>
                                                    <th>محل و تحویل گیرنده</th>
                                                    <th class="text-end">ارزش دفتری</th>
                                                    <th>مدارک و رخدادها</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($assets as $asset)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $asset->asset_code ?: '-' }}
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ $asset->plaque_number ?: 'بدون پلاک' }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="fw-semibold">{{ $asset->name }}</div>
                                                            <small class="text-muted">
                                                                {{ $assetCategories[$asset->asset_category] ?? $asset->asset_category }}
                                                                {{ $asset->serial_number ? ' / سریال: ' . $asset->serial_number : '' }}
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                {{ optional($asset->store)->title ?: ($asset->location ?: '-') }}
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ optional($asset->custodian)->name ?: 'تحویل گیرنده ثبت نشده' }}
                                                                {{ optional($asset->costCenter)->name ? ' / ' . optional($asset->costCenter)->name : '' }}
                                                            </small>
                                                        </td>
                                                        <td class="text-end">
                                                            <div>{{ number_format($asset->bookValue()) }}</div>
                                                            <small class="text-muted">ماهانه:
                                                                {{ number_format($asset->monthlyDepreciationEstimate()) }}</small>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge bg-label-info">{{ number_format($asset->attachments_count) }}
                                                                مدرک</span>
                                                            <span
                                                                class="badge bg-label-secondary">{{ number_format($asset->events_count) }}
                                                                رخداد</span>
                                                            <span
                                                                class="badge bg-label-warning">{{ number_format($asset->disposals_count) }}
                                                                خروج</span>
                                                            <span
                                                                class="badge bg-label-success">{{ number_format($asset->capital_additions_count) }}
                                                                سرمایه ای</span>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge bg-label-primary">{{ $assetStatuses[$asset->status] ?? $asset->status }}</span>
                                                            <div class="small text-muted mt-1">
                                                                {{ $asset->in_service_date_fa ?: optional($asset->in_service_date_en)->format('Y-m-d') }}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="bg-light">
                                                            <div class="row g-3 py-2">
                                                                <div class="col-12 col-xl-3">
                                                                    <div class="border rounded p-3 h-100 bg-white">
                                                                        <h6 class="mb-3">افزودن مدرک</h6>
                                                                        <form method="POST"
                                                                            enctype="multipart/form-data"
                                                                            action="{{ route('Accounting.companyAssets.attachments.store', $asset) }}"
                                                                            class="row g-2">
                                                                            @csrf
                                                                            <div class="col-12">
                                                                                <select name="attachment_type"
                                                                                    class="form-select form-select-sm">
                                                                                    @foreach ($assetAttachmentTypes as $type => $label)
                                                                                        <option
                                                                                            value="{{ $type }}">
                                                                                            {{ $label }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <input type="file"
                                                                                    name="attachment_file"
                                                                                    class="form-control form-control-sm"
                                                                                    required>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <input type="text"
                                                                                    name="attachment_note"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="توضیح مدرک">
                                                                            </div>
                                                                            <div class="col-12 text-end">
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-outline-primary">ثبت
                                                                                    مدرک</button>
                                                                            </div>
                                                                        </form>
                                                                        <div class="mt-3">
                                                                            @forelse($asset->attachments as $attachment)
                                                                                <div
                                                                                    class="small border-top pt-2 mt-2">
                                                                                    <a href="{{ Storage::disk($attachment->disk ?: 'public')->url($attachment->file_path) }}"
                                                                                        target="_blank">
                                                                                        {{ $attachment->original_name ?: 'مشاهده فایل' }}
                                                                                    </a>
                                                                                    <div class="text-muted">
                                                                                        {{ $assetAttachmentTypes[$attachment->attachment_type] ?? $attachment->attachment_type }}
                                                                                        {{ $attachment->note ? ' / ' . $attachment->note : '' }}
                                                                                    </div>
                                                                                </div>
                                                                            @empty
                                                                                <div class="small text-muted">مدرکی ثبت
                                                                                    نشده است.</div>
                                                                            @endforelse
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-xl-3">
                                                                    <div class="border rounded p-3 h-100 bg-white">
                                                                        <h6 class="mb-3">ثبت رخداد عملیاتی</h6>
                                                                        <form method="POST"
                                                                            action="{{ route('Accounting.companyAssets.events.store', $asset) }}"
                                                                            class="row g-2">
                                                                            @csrf
                                                                            <div class="col-12 col-md-6">
                                                                                <select name="event_type"
                                                                                    class="form-select form-select-sm">
                                                                                    @foreach ($assetEventTypes as $type => $label)
                                                                                        <option
                                                                                            value="{{ $type }}">
                                                                                            {{ $label }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <input type="date"
                                                                                    name="event_date_en"
                                                                                    class="form-control form-control-sm"
                                                                                    value="{{ $today }}">
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <input type="text" name="title"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="عنوان رخداد">
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <select name="to_store_id"
                                                                                    class="form-select form-select-sm select2-basic">
                                                                                    <option value="">محل جدید
                                                                                    </option>
                                                                                    @foreach ($stores as $store)
                                                                                        <option
                                                                                            value="{{ $store->id }}">
                                                                                            {{ $store->title }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <select name="to_employee_id"
                                                                                    class="form-select form-select-sm select2-basic">
                                                                                    <option value="">تحویل گیرنده
                                                                                        جدید</option>
                                                                                    @foreach ($employees as $employee)
                                                                                        <option
                                                                                            value="{{ $employee->id }}">
                                                                                            {{ $employee->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <select name="status_after"
                                                                                    class="form-select form-select-sm">
                                                                                    <option value="">بدون تغییر
                                                                                        وضعیت</option>
                                                                                    @foreach ($assetStatuses as $status => $label)
                                                                                        <option
                                                                                            value="{{ $status }}">
                                                                                            {{ $label }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <input type="number" min="0"
                                                                                    step="0.01" name="amount"
                                                                                    class="form-control form-control-sm text-end"
                                                                                    placeholder="مبلغ مرتبط">
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <input type="text"
                                                                                    name="description"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="شرح رخداد">
                                                                            </div>
                                                                            <div class="col-12 text-end">
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-outline-success">ثبت
                                                                                    رخداد</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-xl-3">
                                                                    <div class="border rounded p-3 h-100 bg-white">
                                                                        <h6 class="mb-3">فروش/اسقاط دارایی</h6>
                                                                        <form method="POST"
                                                                            action="{{ route('Accounting.companyAssets.disposal.post', $asset) }}"
                                                                            class="row g-2">
                                                                            @csrf
                                                                            <div class="col-12 col-md-6">
                                                                                <select name="disposal_type"
                                                                                    class="form-select form-select-sm">
                                                                                    <option value="sale">فروش
                                                                                    </option>
                                                                                    <option value="scrap">اسقاط
                                                                                    </option>
                                                                                    <option value="retirement">خروج از
                                                                                        سرویس</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <input type="date"
                                                                                    name="disposal_date_en"
                                                                                    class="form-control form-control-sm"
                                                                                    value="{{ $today }}">
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <input type="number" min="0"
                                                                                    step="0.01"
                                                                                    name="proceeds_amount"
                                                                                    class="form-control form-control-sm text-end"
                                                                                    placeholder="مبلغ فروش/دریافت">
                                                                            </div>
                                                                            <div class="col-12 col-md-6">
                                                                                <select name="proceeds_account_id"
                                                                                    class="form-select form-select-sm account-select">
                                                                                    <option value="">حساب
                                                                                        نقد/دریافتنی</option>
                                                                                    @foreach ($accounts as $account)
                                                                                        <option
                                                                                            value="{{ $account->id }}">
                                                                                            {{ $account->code }} -
                                                                                            {{ $account->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <input type="text"
                                                                                    name="buyer_name"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="خریدار/شرح طرف حساب">
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <input type="text"
                                                                                    name="description"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="توضیح خروج دارایی">
                                                                            </div>
                                                                            <div class="col-12 small text-muted">
                                                                                ارزش دفتری:
                                                                                {{ number_format($asset->bookValue()) }}
                                                                                / بهای تمام شده:
                                                                                {{ number_format((float) $asset->acquisition_cost) }}
                                                                            </div>
                                                                            <div class="col-12 text-end">
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-outline-danger"
                                                                                    @disabled(in_array($asset->status, ['sold', 'scrapped'], true))>
                                                                                    ثبت خروج و سند
                                                                                </button>
                                                                            </div>
                                                                        </form>
                                                                        @if ($asset->disposals->isNotEmpty())
                                                                            @php($lastDisposal = $asset->disposals->sortByDesc('disposal_date_en')->first())
                                                                            @php($taxInvoice = $lastDisposal->taxInvoice)
                                                                            <div
                                                                                class="small border-top pt-2 mt-2 text-muted">
                                                                                آخرین خروج:
                                                                                {{ optional($lastDisposal->disposal_date_en)->format('Y-m-d') }}
                                                                                / سود:
                                                                                {{ number_format((float) $lastDisposal->gain_amount) }}
                                                                                / زیان:
                                                                                {{ number_format((float) $lastDisposal->loss_amount) }}
                                                                            </div>
                                                                            @if ($lastDisposal->disposal_type === 'sale')
                                                                                <div class="border-top pt-2 mt-2">
                                                                                    <div
                                                                                        class="small fw-semibold mb-2">
                                                                                        صورت حساب مودیان فروش دارایی
                                                                                    </div>
                                                                                    @if ($taxInvoice)
                                                                                        <div
                                                                                            class="small text-muted mb-2">
                                                                                            شماره:
                                                                                            {{ $taxInvoice->invoice_number }}
                                                                                            / وضعیت:
                                                                                            {{ $assetTaxInvoiceStatuses[$taxInvoice->status] ?? $taxInvoice->status }}
                                                                                            / جمع:
                                                                                            {{ number_format((float) $taxInvoice->total_amount) }}
                                                                                        </div>
                                                                                    @endif
                                                                                    @if (!$taxInvoice || in_array($taxInvoice->status, ['draft', 'failed', 'rejected'], true))
                                                                                        <form method="POST"
                                                                                            action="{{ route('Accounting.companyAssets.taxInvoice.prepare', $lastDisposal) }}"
                                                                                            class="row g-2 align-items-end mb-2">
                                                                                            @csrf
                                                                                            <input type="hidden"
                                                                                                name="status"
                                                                                                value="{{ request('status') }}">
                                                                                            <input type="hidden"
                                                                                                name="asset_category"
                                                                                                value="{{ request('asset_category') }}">
                                                                                            <input type="hidden"
                                                                                                name="cost_center_id"
                                                                                                value="{{ request('cost_center_id') }}">
                                                                                            <input type="hidden"
                                                                                                name="store_id"
                                                                                                value="{{ request('store_id') }}">
                                                                                            <input type="hidden"
                                                                                                name="q"
                                                                                                value="{{ request('q') }}">
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <label
                                                                                                    class="form-label small">تاریخ</label>
                                                                                                <input type="date"
                                                                                                    name="issue_date_en"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ optional($taxInvoice?->issue_date_en ?: $lastDisposal->disposal_date_en)->format('Y-m-d') }}">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <label
                                                                                                    class="form-label small">نرخ
                                                                                                    مالیات %</label>
                                                                                                <input type="number"
                                                                                                    min="0"
                                                                                                    max="100"
                                                                                                    step="0.0001"
                                                                                                    name="tax_rate"
                                                                                                    class="form-control form-control-sm text-end"
                                                                                                    value="{{ $taxInvoice ? (float) $taxInvoice->tax_rate : 0 }}">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <label
                                                                                                    class="form-label small">کد
                                                                                                    اقتصادی
                                                                                                    خریدار</label>
                                                                                                <input type="text"
                                                                                                    name="buyer_economic_number"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ $taxInvoice->buyer_economic_number ?? '' }}">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <label
                                                                                                    class="form-label small">شناسه/کد
                                                                                                    ملی</label>
                                                                                                <input type="text"
                                                                                                    name="buyer_national_id"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ $taxInvoice->buyer_national_id ?? '' }}">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <input type="text"
                                                                                                    name="buyer_name"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ $taxInvoice->buyer_name ?? $lastDisposal->buyer_name }}"
                                                                                                    placeholder="نام خریدار">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <input type="text"
                                                                                                    name="buyer_postal_code"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ $taxInvoice->buyer_postal_code ?? '' }}"
                                                                                                    placeholder="کد پستی">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <input type="text"
                                                                                                    name="reference_number"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ $taxInvoice->reference_number ?? '' }}"
                                                                                                    placeholder="مرجع/شناسه حافظه">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3 text-end">
                                                                                                <button type="submit"
                                                                                                    class="btn btn-sm btn-outline-primary w-100">آماده
                                                                                                    سازی</button>
                                                                                            </div>
                                                                                        </form>
                                                                                    @endif
                                                                                    @if ($taxInvoice)
                                                                                        <form method="POST"
                                                                                            action="{{ route('Accounting.companyAssets.taxInvoice.status', $taxInvoice) }}"
                                                                                            class="row g-2 align-items-end">
                                                                                            @csrf
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <select name="status"
                                                                                                    class="form-select form-select-sm">
                                                                                                    @foreach (['sent', 'failed', 'accepted', 'rejected'] as $statusKey)
                                                                                                        <option
                                                                                                            value="{{ $statusKey }}"
                                                                                                            @selected($taxInvoice->status === $statusKey)>
                                                                                                            {{ $assetTaxInvoiceStatuses[$statusKey] }}
                                                                                                        </option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <input type="text"
                                                                                                    name="tax_id"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ $taxInvoice->tax_id }}"
                                                                                                    placeholder="شماره مالیاتی">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3">
                                                                                                <input type="text"
                                                                                                    name="error_message"
                                                                                                    class="form-control form-control-sm"
                                                                                                    value="{{ $taxInvoice->error_message }}"
                                                                                                    placeholder="خطا/پاسخ سامانه">
                                                                                            </div>
                                                                                            <div
                                                                                                class="col-12 col-md-3 text-end">
                                                                                                <button type="submit"
                                                                                                    class="btn btn-sm btn-outline-success w-100">ثبت
                                                                                                    وضعیت</button>
                                                                                            </div>
                                                                                        </form>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-xl-3">
                                                                    <div class="border rounded p-3 h-100 bg-white">
                                                                        <h6 class="mb-3">آخرین رخدادها</h6>
                                                                        @forelse($asset->events->sortByDesc('event_date_en')->take(5) as $event)
                                                                            <div class="border-top pt-2 mt-2 small">
                                                                                <div class="fw-semibold">
                                                                                    {{ $event->title }}</div>
                                                                                <div class="text-muted">
                                                                                    {{ $assetEventTypes[$event->event_type] ?? $event->event_type }}
                                                                                    |
                                                                                    {{ $event->event_date_fa ?: optional($event->event_date_en)->format('Y-m-d') }}
                                                                                </div>
                                                                                <div class="text-muted">
                                                                                    {{ optional($event->fromStore)->title ? 'از ' . optional($event->fromStore)->title : '' }}
                                                                                    {{ optional($event->toStore)->title ? ' به ' . optional($event->toStore)->title : '' }}
                                                                                    {{ optional($event->toEmployee)->name ? ' / تحویل: ' . optional($event->toEmployee)->name : '' }}
                                                                                </div>
                                                                                @if ($event->description)
                                                                                    <div>{{ $event->description }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @empty
                                                                            <div class="small text-muted">رخدادی ثبت
                                                                                نشده است.</div>
                                                                        @endforelse
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <div class="border rounded p-3 bg-white">
                                                                        <h6 class="mb-3">تعمیرات اساسی و الحاق سرمایه
                                                                            ای</h6>
                                                                        <form method="POST"
                                                                            action="{{ route('Accounting.companyAssets.capitalAddition.post', $asset) }}"
                                                                            class="row g-2 align-items-end">
                                                                            @csrf
                                                                            <input type="hidden" name="status"
                                                                                value="{{ request('status') }}">
                                                                            <input type="hidden"
                                                                                name="asset_category"
                                                                                value="{{ request('asset_category') }}">
                                                                            <input type="hidden"
                                                                                name="cost_center_id"
                                                                                value="{{ request('cost_center_id') }}">
                                                                            <input type="hidden" name="store_id"
                                                                                value="{{ request('store_id') }}">
                                                                            <input type="hidden" name="q"
                                                                                value="{{ request('q') }}">
                                                                            <input type="hidden"
                                                                                name="depreciation_from"
                                                                                value="{{ $depreciationFrom }}">
                                                                            <input type="hidden"
                                                                                name="depreciation_to"
                                                                                value="{{ $depreciationTo }}">
                                                                            <div class="col-12 col-md-2">
                                                                                <label class="form-label small">نوع
                                                                                    عملیات</label>
                                                                                <select name="addition_type"
                                                                                    class="form-select form-select-sm">
                                                                                    @foreach ($assetCapitalAdditionTypes as $type => $label)
                                                                                        <option
                                                                                            value="{{ $type }}">
                                                                                            {{ $label }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <label
                                                                                    class="form-label small">تاریخ</label>
                                                                                <input type="date"
                                                                                    name="addition_date_en"
                                                                                    class="form-control form-control-sm"
                                                                                    value="{{ $today }}">
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <label class="form-label small">مبلغ
                                                                                    سرمایه ای</label>
                                                                                <input type="number" min="0.01"
                                                                                    step="0.01" name="amount"
                                                                                    class="form-control form-control-sm text-end"
                                                                                    required>
                                                                            </div>
                                                                            <div class="col-12 col-md-3">
                                                                                <label class="form-label small">حساب
                                                                                    دارایی</label>
                                                                                <select name="asset_account_id"
                                                                                    class="form-select form-select-sm account-select">
                                                                                    <option value="">حساب فعلی
                                                                                        دارایی</option>
                                                                                    @foreach ($accounts as $account)
                                                                                        <option
                                                                                            value="{{ $account->id }}"
                                                                                            @selected((string) $asset->asset_account_id === (string) $account->id)>
                                                                                            {{ $account->code }} -
                                                                                            {{ $account->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-3">
                                                                                <label class="form-label small">حساب
                                                                                    بستانکار</label>
                                                                                <select name="credit_account_id"
                                                                                    class="form-select form-select-sm account-select"
                                                                                    required>
                                                                                    <option value="">
                                                                                        پرداختنی/بانک/صندوق</option>
                                                                                    @foreach ($accounts as $account)
                                                                                        <option
                                                                                            value="{{ $account->id }}">
                                                                                            {{ $account->code }} -
                                                                                            {{ $account->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-3">
                                                                                <label class="form-label small">تامین
                                                                                    کننده/مجری</label>
                                                                                <input type="text"
                                                                                    name="supplier_name"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="نام طرف حساب">
                                                                            </div>
                                                                            <div class="col-12 col-md-3">
                                                                                <label class="form-label small">شماره
                                                                                    مرجع</label>
                                                                                <input type="text"
                                                                                    name="reference_number"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="فاکتور/قرارداد/درخواست">
                                                                            </div>
                                                                            <div class="col-12 col-md-4">
                                                                                <label
                                                                                    class="form-label small">شرح</label>
                                                                                <input type="text"
                                                                                    name="description"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="شرح تعمیر اساسی یا الحاق">
                                                                            </div>
                                                                            <div class="col-12 col-md-2 text-end">
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-outline-success"
                                                                                    @disabled(in_array($asset->status, ['sold', 'scrapped'], true))>
                                                                                    ثبت و صدور سند
                                                                                </button>
                                                                            </div>
                                                                            <div class="col-12 small text-muted">
                                                                                بهای تمام شده فعلی:
                                                                                {{ number_format((float) $asset->acquisition_cost) }}
                                                                            </div>
                                                                            <div class="col-12">
                                                                                @forelse($asset->capitalAdditions->take(3) as $addition)
                                                                                    <span
                                                                                        class="badge bg-label-success me-1">
                                                                                        {{ $addition->addition_date_fa ?: optional($addition->addition_date_en)->format('Y-m-d') }}
                                                                                        /
                                                                                        {{ $assetCapitalAdditionTypes[$addition->addition_type] ?? $addition->addition_type }}
                                                                                        /
                                                                                        {{ number_format((float) $addition->amount) }}
                                                                                    </span>
                                                                                @empty
                                                                                    <span
                                                                                        class="small text-muted">الحاق
                                                                                        سرمایه ای برای این دارایی ثبت
                                                                                        نشده است.</span>
                                                                                @endforelse
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <div class="border rounded p-3 bg-white">
                                                                        <h6 class="mb-3">تغییر سیاست استهلاک با تاریخ
                                                                            موثر</h6>
                                                                        <form method="POST"
                                                                            action="{{ route('Accounting.companyAssets.depreciationPolicy.store', $asset) }}"
                                                                            class="row g-2 align-items-end">
                                                                            @csrf
                                                                            <input type="hidden" name="status"
                                                                                value="{{ request('status') }}">
                                                                            <input type="hidden"
                                                                                name="asset_category"
                                                                                value="{{ request('asset_category') }}">
                                                                            <input type="hidden"
                                                                                name="cost_center_id"
                                                                                value="{{ request('cost_center_id') }}">
                                                                            <input type="hidden" name="store_id"
                                                                                value="{{ request('store_id') }}">
                                                                            <input type="hidden" name="q"
                                                                                value="{{ request('q') }}">
                                                                            <input type="hidden"
                                                                                name="depreciation_from"
                                                                                value="{{ $depreciationFrom }}">
                                                                            <input type="hidden"
                                                                                name="depreciation_to"
                                                                                value="{{ $depreciationTo }}">
                                                                            <div class="col-12 col-md-2">
                                                                                <label class="form-label small">تاریخ
                                                                                    موثر</label>
                                                                                <input type="date"
                                                                                    name="effective_date_en"
                                                                                    class="form-control form-control-sm"
                                                                                    value="{{ $today }}"
                                                                                    required>
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <label
                                                                                    class="form-label small">روش</label>
                                                                                <select name="depreciation_method"
                                                                                    class="form-select form-select-sm">
                                                                                    <option value="straight_line">خط
                                                                                        مستقیم</option>
                                                                                    <option value="rate_percent">نرخ
                                                                                        سالانه</option>
                                                                                    <option value="none">توقف استهلاک
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <label class="form-label small">عمر
                                                                                    مفید ماه</label>
                                                                                <input type="number" min="1"
                                                                                    step="1"
                                                                                    name="useful_life_months"
                                                                                    class="form-control form-control-sm text-end"
                                                                                    value="{{ $asset->useful_life_months }}">
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <label class="form-label small">ارزش
                                                                                    اسقاط</label>
                                                                                <input type="number" min="0"
                                                                                    step="0.01"
                                                                                    name="salvage_value"
                                                                                    class="form-control form-control-sm text-end"
                                                                                    value="{{ (float) $asset->salvage_value }}">
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <label class="form-label small">نرخ
                                                                                    سالانه %</label>
                                                                                <input type="number" min="0.0001"
                                                                                    max="100" step="0.0001"
                                                                                    name="annual_rate_percent"
                                                                                    class="form-control form-control-sm text-end"
                                                                                    placeholder="مثلا 25">
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <label class="form-label small">دلیل
                                                                                    تغییر</label>
                                                                                <input type="text" name="reason"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="بازنگری/تعمیر/تصمیم مدیریت">
                                                                            </div>
                                                                            <div class="col-12 col-md-4">
                                                                                <label class="form-label small">حساب
                                                                                    هزینه استهلاک</label>
                                                                                <select
                                                                                    name="depreciation_expense_account_id"
                                                                                    class="form-select form-select-sm account-select">
                                                                                    <option value="">حساب فعلی
                                                                                        دارایی</option>
                                                                                    @foreach ($accounts as $account)
                                                                                        <option
                                                                                            value="{{ $account->id }}"
                                                                                            @selected((string) $asset->depreciation_expense_account_id === (string) $account->id)>
                                                                                            {{ $account->code }} -
                                                                                            {{ $account->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-4">
                                                                                <label class="form-label small">حساب
                                                                                    استهلاک انباشته</label>
                                                                                <select
                                                                                    name="accumulated_depreciation_account_id"
                                                                                    class="form-select form-select-sm account-select">
                                                                                    <option value="">حساب فعلی
                                                                                        دارایی</option>
                                                                                    @foreach ($accounts as $account)
                                                                                        <option
                                                                                            value="{{ $account->id }}"
                                                                                            @selected((string) $asset->accumulated_depreciation_account_id === (string) $account->id)>
                                                                                            {{ $account->code }} -
                                                                                            {{ $account->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12 col-md-4 text-end">
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-outline-dark">
                                                                                    ثبت سیاست تاریخ دار
                                                                                </button>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                @forelse($asset->depreciationPolicies->take(3) as $policy)
                                                                                    <span
                                                                                        class="badge bg-label-secondary me-1">
                                                                                        {{ $policy->effective_date_fa ?: optional($policy->effective_date_en)->format('Y-m-d') }}
                                                                                        /
                                                                                        {{ $policy->depreciation_method }}
                                                                                        {{ $policy->annual_rate_percent ? ' / ' . number_format((float) $policy->annual_rate_percent, 2) . '%' : '' }}
                                                                                    </span>
                                                                                @empty
                                                                                    <span
                                                                                        class="small text-muted">سیاست
                                                                                        تاریخ دار جداگانه ثبت نشده است؛
                                                                                        تنظیم پایه دارایی استفاده می
                                                                                        شود.</span>
                                                                                @endforelse
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">اموالی
                                                            ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $assets->links() }}</div>
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
            $('.select2-basic, .account-select').select2({
                width: '100%'
            });
        });
    </script>
</body>

</html>

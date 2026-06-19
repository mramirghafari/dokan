<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گزارش تکمیلی دارایی ثابت - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> گزارش تکمیلی
                                دارایی ثابت</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.companyAssets') }}">
                                <x-ui.icon name="list-details" class="me-1" /> دفتر اموال
                            </a>
                        </div>

                        <form method="GET" action="{{ route('Accounting.companyAssets.report') }}" class="card mb-4">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-3">
                                    <label class="form-label">جستجو</label>
                                    <input type="text" name="q" class="form-control"
                                        value="{{ $report['filters']['q'] }}" placeholder="نام، کد، پلاک یا سریال">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">مبنای تاریخ</label>
                                    <select name="date_basis" class="form-select">
                                        <option value="in_service" @selected($report['filters']['date_basis'] === 'in_service')>تاریخ بهره برداری
                                        </option>
                                        <option value="acquisition" @selected($report['filters']['date_basis'] === 'acquisition')>تاریخ تحصیل</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">از تاریخ</label>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ $report['filters']['from_date'] }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">تا تاریخ</label>
                                    <input type="date" name="to_date" class="form-control"
                                        value="{{ $report['filters']['to_date'] }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">طبقه</label>
                                    <select name="asset_category" class="form-select">
                                        <option value="">همه</option>
                                        @foreach ($assetCategories as $category => $label)
                                            <option value="{{ $category }}" @selected($report['filters']['asset_category'] === $category)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">وضعیت</label>
                                    <select name="status" class="form-select">
                                        <option value="">همه</option>
                                        @foreach ($assetStatuses as $status => $label)
                                            <option value="{{ $status }}" @selected($report['filters']['status'] === $status)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">محل/انبار</label>
                                    <select name="store_id" class="form-select select2-basic">
                                        <option value="">همه</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}" @selected((string) $report['filters']['store_id'] === (string) $store->id)>
                                                {{ $store->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">مرکز هزینه</label>
                                    <select name="cost_center_id" class="form-select select2-basic">
                                        <option value="">همه</option>
                                        @foreach ($costCenters as $costCenter)
                                            <option value="{{ $costCenter->id }}" @selected((string) $report['filters']['cost_center_id'] === (string) $costCenter->id)>
                                                {{ $costCenter->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">تحویل گیرنده</label>
                                    <select name="custodian_employee_id" class="form-select select2-basic">
                                        <option value="">همه</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}" @selected((string) $report['filters']['custodian_employee_id'] === (string) $employee->id)>
                                                {{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 text-end">
                                    <button type="submit" class="btn btn-primary w-100">بروزرسانی گزارش</button>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد دارایی</small>
                                        <h4 class="mb-0 text-end">{{ number_format($report['summary']['count']) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">بهای تمام شده</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($report['summary']['acquisition_cost']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">استهلاک انباشته</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($report['summary']['accumulated_depreciation']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">ارزش دفتری</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($report['summary']['book_value']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">الحاق سرمایه ای</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($report['summary']['capital_addition_amount']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">استهلاک ثبت شده</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format($report['summary']['posted_depreciation']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">فعال</small>
                                        <h4 class="mb-0 text-end">{{ number_format($report['summary']['active']) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">خارج شده</small>
                                        <h4 class="mb-0 text-end">{{ number_format($report['summary']['disposed']) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            @foreach (['category' => 'تفکیک طبقه', 'status' => 'تفکیک وضعیت', 'store' => 'تفکیک محل', 'cost_center' => 'تفکیک مرکز هزینه', 'custodian' => 'تفکیک تحویل گیرنده'] as $groupKey => $groupTitle)
                                <div class="col-12 col-xl-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ $groupTitle }}</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>گروه</th>
                                                        <th class="text-end">تعداد</th>
                                                        <th class="text-end">بهای تمام شده</th>
                                                        <th class="text-end">ارزش دفتری</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($report['groups'][$groupKey]->take(6) as $group)
                                                        <tr>
                                                            <td>{{ $group['label'] }}</td>
                                                            <td class="text-end">{{ number_format($group['count']) }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ number_format($group['acquisition_cost']) }}</td>
                                                            <td class="text-end">
                                                                {{ number_format($group['book_value']) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-3">
                                                                داده ای وجود ندارد.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">ریز گزارش دارایی ثابت</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>دارایی</th>
                                            <th>طبقه/وضعیت</th>
                                            <th>محل/مرکز هزینه</th>
                                            <th>تحویل گیرنده</th>
                                            <th class="text-end">بهای تمام شده</th>
                                            <th class="text-end">استهلاک</th>
                                            <th class="text-end">ارزش دفتری</th>
                                            <th class="text-end">الحاق سرمایه ای</th>
                                            <th>آخرین policy/خروج</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report['rows'] as $row)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $row['asset_code'] ?: '-' }} -
                                                        {{ $row['name'] }}</div>
                                                    <small
                                                        class="text-muted">{{ $row['plaque_number'] ?: 'بدون پلاک' }}
                                                        / بهره برداری: {{ $row['in_service_date'] ?: '-' }}</small>
                                                </td>
                                                <td>
                                                    <div>{{ $row['asset_category_label'] }}</div>
                                                    <span
                                                        class="badge bg-label-primary">{{ $row['status_label'] }}</span>
                                                </td>
                                                <td>
                                                    <div>{{ $row['store_title'] }}</div>
                                                    <small class="text-muted">{{ $row['cost_center_title'] }}</small>
                                                </td>
                                                <td>{{ $row['custodian_name'] }}</td>
                                                <td class="text-end">{{ number_format($row['acquisition_cost']) }}
                                                </td>
                                                <td class="text-end">
                                                    <div>{{ number_format($row['accumulated_depreciation']) }}</div>
                                                    <small class="text-muted">ثبت شده:
                                                        {{ number_format($row['posted_depreciation']) }}</small>
                                                </td>
                                                <td class="text-end">{{ number_format($row['book_value']) }}</td>
                                                <td class="text-end">
                                                    <div>{{ number_format($row['capital_addition_amount']) }}</div>
                                                    @if ($row['last_addition'])
                                                        <small class="text-muted">{{ $row['last_addition']['date'] }}
                                                            /
                                                            {{ number_format($row['last_addition']['amount']) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($row['last_policy'])
                                                        <div class="small">policy: {{ $row['last_policy']['date'] }}
                                                            / {{ $row['last_policy']['method'] }}</div>
                                                    @endif
                                                    @if ($row['last_disposal'])
                                                        <div class="small text-muted">خروج:
                                                            {{ $row['last_disposal']['date'] }} / سود
                                                            {{ number_format($row['last_disposal']['gain']) }} / زیان
                                                            {{ number_format($row['last_disposal']['loss']) }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">برای فیلتر
                                                    انتخاب شده دارایی پیدا نشد.</td>
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

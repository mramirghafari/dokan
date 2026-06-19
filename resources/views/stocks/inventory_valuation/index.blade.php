<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گزارش ریالی انبار - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> گزارش ریالی انبار
                        </h4>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="get" action="{{ route('stocks.inventoryValuation') }}">
                                    <div class="col-md-2"><label class="form-label">از تاریخ</label><input
                                            class="form-control" type="date" name="from_date"
                                            value="{{ request('from_date', $report['from_date']) }}"></div>
                                    <div class="col-md-2"><label class="form-label">تا تاریخ</label><input
                                            class="form-control" type="date" name="to_date"
                                            value="{{ request('to_date', $report['to_date']) }}"></div>
                                    <div class="col-md-2">
                                        <label class="form-label">انبار</label>
                                        <select class="select2 form-select" name="store_id">
                                            <option value="">همه</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">کالا</label>
                                        @include('partials.forms.erp-product-filter-select', ['placeholder' => 'همه'])
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">مکان</label>
                                        <select class="select2 form-select" name="warehouse_location_id">
                                            <option value="">همه</option>
                                            <option value="0" @selected(request('warehouse_location_id') === '0')>بدون مکان</option>
                                            @foreach ($warehouseLocations as $location)
                                                <option value="{{ $location->id }}" @selected(request('warehouse_location_id') == $location->id)>
                                                    {{ $location->store->title ?? 'انبار' }} -
                                                    {{ $location->path ?: $location->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100"
                                            type="submit">فیلتر</button></div>
                                </form>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">اول دوره</span>
                                        <h5>{{ number_format($report['totals']['opening_cost']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">ورود دوره</span>
                                        <h5>{{ number_format($report['totals']['in_cost']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">خروج دوره</span>
                                        <h5>{{ number_format($report['totals']['out_cost']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">پایان محاسباتی</span>
                                        <h5>{{ number_format($report['totals']['ending_cost']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">مانده لحظه ای</span>
                                        <h5>{{ number_format($report['totals']['current_cost']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">مغایرت</span>
                                        <h5>{{ number_format($report['totals']['cost_variance']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">ارزش موجودی به تفکیک کالا و انبار</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>انبار/مکان</th>
                                            <th>کالا</th>
                                            <th>مقدار اول</th>
                                            <th>ارزش اول</th>
                                            <th>ورود</th>
                                            <th>ارزش ورود</th>
                                            <th>خروج</th>
                                            <th>ارزش خروج</th>
                                            <th>مقدار پایان</th>
                                            <th>ارزش پایان</th>
                                            <th>بهای پایان</th>
                                            <th>مغایرت ریالی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report['rows'] as $row)
                                            <tr>
                                                <td>{{ $row['store_title'] }}<br><small>{{ $row['location_title'] }}</small>
                                                </td>
                                                <td>{{ $row['product_title'] }}</td>
                                                <td>{{ number_format($row['opening_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['opening_cost']) }}</td>
                                                <td>{{ number_format($row['in_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['in_cost']) }}</td>
                                                <td>{{ number_format($row['out_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['out_cost']) }}</td>
                                                <td>{{ number_format($row['ending_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['ending_cost']) }}</td>
                                                <td>{{ number_format($row['ending_unit_cost']) }}</td>
                                                <td>{{ number_format($row['cost_variance']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted" colspan="12">گردش یا مانده ریالی
                                                    برای فیلتر فعلی وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div
                                        class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h5 class="mb-1">انحراف قیمت خرید</h5>
                                            <small class="text-muted">مقایسه قیمت واقعی خرید با قیمت مرجع کالا</small>
                                        </div>
                                        <span
                                            class="badge bg-label-{{ $report['variance']['totals']['purchase_variance_amount'] > 0 ? 'danger' : 'success' }}">
                                            {{ number_format($report['variance']['totals']['purchase_variance_amount']) }}
                                        </span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>کالا / تامین کننده</th>
                                                    <th class="text-end">مقدار</th>
                                                    <th class="text-end">واقعی</th>
                                                    <th class="text-end">مرجع</th>
                                                    <th class="text-end">انحراف</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($report['variance']['purchase_price_rows'] as $row)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $row['product_title'] }}</div>
                                                            <small class="text-muted">{{ $row['supplier_title'] }} |
                                                                {{ $row['store_title'] }}</small>
                                                        </td>
                                                        <td class="text-end">{{ number_format($row['quantity'], 3) }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format($row['actual_unit_price']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($row['reference_unit_price']) }}</td>
                                                        <td
                                                            class="text-end {{ $row['variance_amount'] > 0 ? 'text-danger' : 'text-success' }}">
                                                            {{ number_format($row['variance_amount']) }}
                                                            <br><small>{{ is_null($row['variance_percent']) ? '-' : number_format($row['variance_percent'], 2) . '%' }}</small>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">برای
                                                            فیلتر فعلی خرید قابل تحلیل پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div
                                        class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h5 class="mb-1">انحراف مصرف مواد</h5>
                                            <small class="text-muted">مقایسه مصرف واقعی با BOM یا برنامه تولید</small>
                                        </div>
                                        <span
                                            class="badge bg-label-{{ $report['variance']['totals']['material_variance_amount'] > 0 ? 'danger' : 'success' }}">
                                            {{ number_format($report['variance']['totals']['material_variance_amount']) }}
                                        </span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>سفارش / ماده</th>
                                                    <th class="text-end">واقعی</th>
                                                    <th class="text-end">استاندارد</th>
                                                    <th class="text-end">انحراف مقدار</th>
                                                    <th class="text-end">اثر ریالی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($report['variance']['material_consumption_rows'] as $row)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $row['production_number'] }}
                                                            </div>
                                                            <small class="text-muted">{{ $row['product_title'] }} |
                                                                {{ $row['reference_source'] }}</small>
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format($row['actual_quantity'], 3) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($row['expected_quantity'], 3) }}</td>
                                                        <td
                                                            class="text-end {{ $row['quantity_variance'] > 0 ? 'text-danger' : 'text-success' }}">
                                                            {{ number_format($row['quantity_variance'], 3) }}</td>
                                                        <td
                                                            class="text-end {{ $row['variance_amount'] > 0 ? 'text-danger' : 'text-success' }}">
                                                            {{ number_format($row['variance_amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-3">برای
                                                            فیلتر فعلی مصرف تولید قابل تحلیل پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($report['cardex']->isNotEmpty())
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">کاردکس ریالی روان کالای انتخاب شده</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>تاریخ</th>
                                                <th>انبار/مکان</th>
                                                <th>نوع</th>
                                                <th>شماره</th>
                                                <th>ورود/خروج</th>
                                                <th>مقدار</th>
                                                <th>بهای واحد</th>
                                                <th>مبلغ</th>
                                                <th>مانده مقدار</th>
                                                <th>مانده ریالی</th>
                                                <th>بهای مانده</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($report['cardex'] as $movement)
                                                <tr>
                                                    <td>{{ optional($movement['occurred_at'])->format('Y-m-d H:i') ?: '-' }}
                                                    </td>
                                                    <td>{{ $movement['store_title'] }}<br><small>{{ $movement['location_title'] }}</small>
                                                    </td>
                                                    <td>{{ $movement['movement_type'] }}</td>
                                                    <td>{{ $movement['reference_no'] ?: '-' }}</td>
                                                    <td><span
                                                            class="badge bg-label-{{ $movement['direction'] === 'in' ? 'success' : 'danger' }}">{{ $movement['direction'] === 'in' ? 'ورود' : 'خروج' }}</span>
                                                    </td>
                                                    <td>{{ number_format($movement['quantity'], 3) }}</td>
                                                    <td>{{ number_format($movement['unit_cost']) }}</td>
                                                    <td>{{ number_format($movement['total_cost']) }}</td>
                                                    <td>{{ number_format($movement['running_quantity'], 3) }}</td>
                                                    <td>{{ number_format($movement['running_cost']) }}</td>
                                                    <td>{{ number_format($movement['running_unit_cost']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                    @include('sections.footer')
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    <script>
        $('.select2:not(.erp-remote-select)').select2({
            dir: 'rtl',
            width: '100%'
        });
    </script>
</body>

</html>

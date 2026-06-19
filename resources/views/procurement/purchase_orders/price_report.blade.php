<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>کنترل قیمت خرید - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> کنترل قیمت خرید
                            </h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.commitmentReport') }}">تعهد و دریافت</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.supplierLedger') }}">گردش تامین کننده</a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">بازگشت
                                    به سفارش خرید</a>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('purchase-orders.priceReport') }}"
                                    class="row g-3 align-items-end">
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
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">کالا</label>
                                        @include('partials.forms.erp-product-filter-select', [
                                            'placeholder' => 'همه',
                                            'class' => 'form-select erp-remote-select',
                                        ])
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">تامین کننده</label>
                                        <select name="supplier_id" class="form-select">
                                            <option value="">همه</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>
                                                    {{ $supplier->title ?: $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">انبار</label>
                                        <select name="store_id" class="form-select">
                                            <option value="">همه</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" @selected((string) request('store_id') === (string) $store->id)>
                                                    {{ $store->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-1">
                                        <label class="form-label">هشدار</label>
                                        <select name="price_alert" class="form-select">
                                            <option value="">همه</option>
                                            <option value="increased" @selected(request('price_alert') === 'increased')>افزایش</option>
                                            <option value="decreased" @selected(request('price_alert') === 'decreased')>کاهش</option>
                                            <option value="stable" @selected(request('price_alert') === 'stable')>بدون تغییر</option>
                                            <option value="no_previous" @selected(request('price_alert') === 'no_previous')>بدون سابقه</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-1">
                                        <button class="btn btn-primary w-100" type="submit">فیلتر</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">کالاهای دارای خرید</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['products_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">ردیف خرید</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['rows_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">میانگین وزنی</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['average_price']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">افزایش قیمت</small>
                                        <h5 class="mb-0 text-end text-danger">
                                            {{ number_format($totals['increased_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">بدون سابقه قبلی</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['no_previous_count']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">آخرین قیمت خرید کالا</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>کالا</th>
                                            <th>آخرین تامین کننده</th>
                                            <th>آخرین سفارش</th>
                                            <th>تاریخ</th>
                                            <th class="text-end">آخرین قیمت</th>
                                            <th class="text-end">قیمت قبلی</th>
                                            <th class="text-end">میانگین</th>
                                            <th class="text-end">حداقل</th>
                                            <th class="text-end">حداکثر</th>
                                            <th class="text-end">درصد تغییر</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($productSummaries as $summary)
                                            <tr>
                                                <td>{{ $summary['product'] }}</td>
                                                <td>{{ $summary['latest_supplier'] }}</td>
                                                <td>{{ $summary['latest_order_number'] }}</td>
                                                <td>{{ $summary['latest_order_date'] }}</td>
                                                <td class="text-end">{{ number_format($summary['latest_price']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ $summary['previous_price'] !== null ? number_format($summary['previous_price']) : '-' }}
                                                </td>
                                                <td class="text-end">{{ number_format($summary['average_price']) }}
                                                </td>
                                                <td class="text-end">{{ number_format($summary['min_price']) }}</td>
                                                <td class="text-end">{{ number_format($summary['max_price']) }}</td>
                                                <td class="text-end">
                                                    {{ number_format($summary['change_percent'], 2) }}%</td>
                                                <td>
                                                    @if ($summary['price_alert'] === 'increased')
                                                        <span class="badge bg-label-danger">افزایش</span>
                                                    @elseif ($summary['price_alert'] === 'decreased')
                                                        <span class="badge bg-label-success">کاهش</span>
                                                    @elseif ($summary['price_alert'] === 'stable')
                                                        <span class="badge bg-label-secondary">بدون تغییر</span>
                                                    @else
                                                        <span class="badge bg-label-warning">بدون سابقه</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">قیمتی برای این
                                                    فیلتر وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">مقایسه تامین کننده ها</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>کالا</th>
                                            <th>تامین کننده</th>
                                            <th class="text-end">تعداد خرید</th>
                                            <th class="text-end">آخرین قیمت</th>
                                            <th class="text-end">میانگین</th>
                                            <th class="text-end">حداقل</th>
                                            <th class="text-end">حداکثر</th>
                                            <th>آخرین تاریخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($supplierComparisons as $row)
                                            <tr>
                                                <td>{{ $row['product'] }}</td>
                                                <td>{{ $row['supplier'] }}</td>
                                                <td class="text-end">{{ number_format($row['purchases_count']) }}</td>
                                                <td class="text-end">{{ number_format($row['latest_price']) }}</td>
                                                <td class="text-end">{{ number_format($row['average_price']) }}</td>
                                                <td class="text-end">{{ number_format($row['min_price']) }}</td>
                                                <td class="text-end">{{ number_format($row['max_price']) }}</td>
                                                <td>{{ $row['latest_order_date'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">مقایسه ای برای
                                                    نمایش وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">آخرین ردیف های خرید</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره سفارش</th>
                                            <th>تاریخ</th>
                                            <th>کالا</th>
                                            <th>تامین کننده</th>
                                            <th>انبار</th>
                                            <th class="text-end">تعداد</th>
                                            <th class="text-end">قیمت واحد</th>
                                            <th class="text-end">مبلغ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($latestRows as $row)
                                            <tr>
                                                <td>{{ $row['order_number'] }}</td>
                                                <td>{{ $row['order_date'] }}</td>
                                                <td>{{ $row['product'] }}</td>
                                                <td>{{ $row['supplier'] }}</td>
                                                <td>{{ $row['store'] }}</td>
                                                <td class="text-end">{{ number_format($row['quantity'], 3) }}</td>
                                                <td class="text-end">{{ number_format($row['unit_price']) }}</td>
                                                <td class="text-end">{{ number_format($row['total_amount']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">ردیف خریدی برای
                                                    نمایش وجود ندارد.</td>
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
    @include('partials.erp-remote-select-assets')
</body>

</html>

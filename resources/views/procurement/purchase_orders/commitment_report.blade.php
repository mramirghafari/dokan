<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>تعهد و دریافت خرید - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> تعهد و دریافت
                                خرید</h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.priceReport') }}">کنترل قیمت</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.supplierLedger') }}">گردش تامین کننده</a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.report') }}">گزارش
                                    خرید</a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">بازگشت
                                    به سفارش خرید</a>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('purchase-orders.commitmentReport') }}"
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
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">وضعیت دریافت</label>
                                        <select name="fulfillment_status" class="form-select">
                                            <option value="">همه</option>
                                            <option value="open" @selected(request('fulfillment_status') === 'open')>باز</option>
                                            <option value="not_received" @selected(request('fulfillment_status') === 'not_received')>دریافت نشده
                                            </option>
                                            <option value="partial" @selected(request('fulfillment_status') === 'partial')>دریافت ناقص</option>
                                            <option value="received" @selected(request('fulfillment_status') === 'received')>دریافت کامل</option>
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
                                    <div class="card-body"><small class="text-muted">اقلام گزارش</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['items_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">اقلام باز</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['open_items_count']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد سفارش</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['ordered_quantity'], 3) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد دریافت</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['received_quantity'], 3) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">ارزش مانده دریافت</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['remaining_amount']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">تعهد به تفکیک تامین کننده</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>تامین کننده</th>
                                                    <th class="text-end">اقلام</th>
                                                    <th class="text-end">ارزش سفارش</th>
                                                    <th class="text-end">ارزش دریافت</th>
                                                    <th class="text-end">مانده دریافت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($supplierSummaries as $summary)
                                                    <tr>
                                                        <td>{{ $summary['supplier'] }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($summary['items_count']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($summary['ordered_amount']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($summary['received_amount']) }}</td>
                                                        <td class="text-end fw-semibold">
                                                            {{ number_format($summary['remaining_amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">تعهدی
                                                            برای این فیلتر وجود ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">تعهد به تفکیک انبار</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>انبار</th>
                                                    <th class="text-end">اقلام</th>
                                                    <th class="text-end">ارزش سفارش</th>
                                                    <th class="text-end">ارزش دریافت</th>
                                                    <th class="text-end">مانده دریافت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($storeSummaries as $summary)
                                                    <tr>
                                                        <td>{{ $summary['store'] }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($summary['items_count']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($summary['ordered_amount']) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($summary['received_amount']) }}</td>
                                                        <td class="text-end fw-semibold">
                                                            {{ number_format($summary['remaining_amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">تعهدی
                                                            برای این فیلتر وجود ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">ریز تعهد و دریافت اقلام</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره سفارش</th>
                                            <th>تامین کننده</th>
                                            <th>انبار</th>
                                            <th>کالا</th>
                                            <th>تاریخ سفارش</th>
                                            <th>تاریخ رسید</th>
                                            <th class="text-end">سفارش</th>
                                            <th class="text-end">دریافت</th>
                                            <th class="text-end">مانده</th>
                                            <th class="text-end">ارزش مانده</th>
                                            <th class="text-end">فاصله روز</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rows as $row)
                                            <tr>
                                                <td>{{ $row['order_number'] }}</td>
                                                <td>{{ $row['supplier'] }}</td>
                                                <td>{{ $row['store'] }}</td>
                                                <td>{{ $row['product'] }}</td>
                                                <td>{{ $row['order_date'] }}</td>
                                                <td>{{ $row['receipt_date'] ?: '-' }}</td>
                                                <td class="text-end">{{ number_format($row['ordered_quantity'], 3) }}
                                                </td>
                                                <td class="text-end">{{ number_format($row['received_quantity'], 3) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($row['remaining_quantity'], 3) }}</td>
                                                <td class="text-end">{{ number_format($row['remaining_amount']) }}
                                                </td>
                                                <td class="text-end">{{ number_format($row['delay_days']) }}</td>
                                                <td>
                                                    @if ($row['status'] === 'received')
                                                        <span class="badge bg-label-success">دریافت کامل</span>
                                                    @elseif ($row['status'] === 'partial')
                                                        <span class="badge bg-label-warning">دریافت ناقص</span>
                                                    @else
                                                        <span class="badge bg-label-danger">دریافت نشده</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center text-muted py-4">ردیفی برای این
                                                    فیلتر وجود ندارد.</td>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>

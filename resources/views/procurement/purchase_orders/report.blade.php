<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گزارش خرید - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> گزارش خرید</h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.priceReport') }}">کنترل قیمت</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.commitmentReport') }}">تعهد و دریافت</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.supplierLedger') }}">گردش تامین کننده</a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">بازگشت
                                    به
                                    سفارش خرید</a>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('purchase-orders.report') }}"
                                    class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">از تاریخ</label>
                                        <input type="date" name="date_from" class="form-control"
                                            value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">تا تاریخ</label>
                                        <input type="date" name="date_to" class="form-control"
                                            value="{{ request('date_to') }}">
                                    </div>
                                    <div class="col-12 col-md-3">
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
                                        <label class="form-label">وضعیت پرداخت</label>
                                        <select name="payment_status" class="form-select">
                                            <option value="">همه</option>
                                            <option value="unpaid" @selected(request('payment_status') === 'unpaid')>پرداخت نشده</option>
                                            <option value="partial" @selected(request('payment_status') === 'partial')>پرداخت جزئی</option>
                                            <option value="paid" @selected(request('payment_status') === 'paid')>تسویه شده</option>
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
                                    <div class="card-body"><small class="text-muted">خرید ناخالص</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['gross']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مرجوعی</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['returns']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">خرید خالص</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['net']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">پرداخت شده</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['paid']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مانده</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['remaining']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">خلاصه تامین کننده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>تامین کننده</th>
                                            <th class="text-end">تعداد سفارش</th>
                                            <th class="text-end">ناخالص</th>
                                            <th class="text-end">مرجوعی</th>
                                            <th class="text-end">خالص</th>
                                            <th class="text-end">پرداخت</th>
                                            <th class="text-end">مانده</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($supplierSummaries as $summary)
                                            <tr>
                                                <td>{{ $summary['supplier'] }}</td>
                                                <td class="text-end">{{ number_format($summary['orders_count']) }}
                                                </td>
                                                <td class="text-end">{{ number_format($summary['gross']) }}</td>
                                                <td class="text-end">{{ number_format($summary['returns']) }}</td>
                                                <td class="text-end">{{ number_format($summary['net']) }}</td>
                                                <td class="text-end">{{ number_format($summary['paid']) }}</td>
                                                <td class="text-end">{{ number_format($summary['remaining']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">گزارشی برای این
                                                    فیلتر وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">ریز سفارش ها</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره</th>
                                            <th>تامین کننده</th>
                                            <th>تاریخ</th>
                                            <th class="text-end">ناخالص</th>
                                            <th class="text-end">مرجوعی</th>
                                            <th class="text-end">خالص</th>
                                            <th class="text-end">پرداخت</th>
                                            <th class="text-end">مانده</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchaseOrders as $purchaseOrder)
                                            <tr>
                                                <td>{{ $purchaseOrder->order_number }}</td>
                                                <td>{{ optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name }}
                                                </td>
                                                <td>{{ $purchaseOrder->order_date_fa ?: verta_date($purchaseOrder->order_date_en) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->total_amount) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->returned_amount) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->net_amount) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->paid_amount) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->remaining_amount) }}</td>
                                                <td>{{ $purchaseOrder->payment_status === 'paid' ? 'تسویه شده' : ($purchaseOrder->payment_status === 'partial' ? 'پرداخت جزئی' : 'پرداخت نشده') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">سفارشی برای
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
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>

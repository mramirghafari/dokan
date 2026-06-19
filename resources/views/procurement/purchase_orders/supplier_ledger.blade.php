<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گردش تامین کننده - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> گردش تامین کننده
                            </h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.priceReport') }}">کنترل قیمت</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.commitmentReport') }}">تعهد و دریافت</a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.report') }}">گزارش
                                    خرید</a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">بازگشت
                                    به سفارش خرید</a>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('purchase-orders.supplierLedger') }}"
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
                                    <div class="card-body"><small class="text-muted">جمع خرید</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['purchases']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">کاهش بدهی</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['payments_and_returns']) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مانده تامین کننده</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['balance']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">سفارش باز</small>
                                        <h5 class="mb-0 text-end">{{ number_format($totals['open_orders']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">سن مانده تامین کننده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>تامین کننده</th>
                                            <th class="text-end">سفارش باز</th>
                                            <th class="text-end">تا ۳۰ روز</th>
                                            <th class="text-end">۳۱ تا ۶۰</th>
                                            <th class="text-end">۶۱ تا ۹۰</th>
                                            <th class="text-end">بیش از ۹۰</th>
                                            <th class="text-end">جمع مانده</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($supplierAging as $row)
                                            <tr>
                                                <td>{{ $row['supplier'] }}</td>
                                                <td class="text-end">{{ number_format($row['orders_count']) }}</td>
                                                <td class="text-end">{{ number_format($row['current']) }}</td>
                                                <td class="text-end">{{ number_format($row['days_31_60']) }}</td>
                                                <td class="text-end">{{ number_format($row['days_61_90']) }}</td>
                                                <td class="text-end">{{ number_format($row['days_over_90']) }}</td>
                                                <td class="text-end fw-semibold">{{ number_format($row['total']) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">مانده بازی برای
                                                    این فیلتر وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">جمع</th>
                                            <th class="text-end">{{ number_format($totals['aging']['current']) }}</th>
                                            <th class="text-end">{{ number_format($totals['aging']['days_31_60']) }}
                                            </th>
                                            <th class="text-end">{{ number_format($totals['aging']['days_61_90']) }}
                                            </th>
                                            <th class="text-end">{{ number_format($totals['aging']['days_over_90']) }}
                                            </th>
                                            <th class="text-end">{{ number_format(array_sum($totals['aging'])) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">کاردکس مالی تامین کننده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>تامین کننده</th>
                                            <th>سفارش</th>
                                            <th>نوع رویداد</th>
                                            <th class="text-end">افزایش بدهی</th>
                                            <th class="text-end">کاهش بدهی</th>
                                            <th class="text-end">مانده تجمعی</th>
                                            <th>شرح</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ledgerRows as $row)
                                            <tr>
                                                <td>{{ $row['date_fa'] ?: $row['date_en'] }}</td>
                                                <td>{{ $row['supplier'] }}</td>
                                                <td>{{ $row['order_number'] }}</td>
                                                <td>{{ $row['type'] }}</td>
                                                <td class="text-end">{{ number_format($row['debit']) }}</td>
                                                <td class="text-end">{{ number_format($row['credit']) }}</td>
                                                <td class="text-end fw-semibold">{{ number_format($row['balance']) }}
                                                </td>
                                                <td>{{ $row['description'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">گردشی برای این
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>

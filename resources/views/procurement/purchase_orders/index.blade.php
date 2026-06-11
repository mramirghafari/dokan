<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>سفارش خرید - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> سفارش خرید</h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-requisitions.index') }}">
                                    <i class="ti ti-list-check me-1"></i> استعلام بها
                                </a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.approvals') }}">
                                    <i class="ti ti-checkup-list me-1"></i> تایید و بودجه
                                </a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.priceReport') }}">
                                    <i class="ti ti-chart-line me-1"></i> کنترل قیمت
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.foreignImports') }}">
                                    <i class="ti ti-ship me-1"></i> واردات
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.commitmentReport') }}">
                                    <i class="ti ti-clipboard-check me-1"></i> تعهد و دریافت
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.supplierLedger') }}">
                                    <i class="ti ti-file-analytics me-1"></i> گردش تامین کننده
                                </a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.report') }}">
                                    <i class="ti ti-report-analytics me-1"></i> گزارش خرید
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.directSupply') }}">
                                    <i class="ti ti-bolt me-1"></i> تامین مستقیم
                                </a>
                                <a class="btn btn-primary" href="{{ route('purchase-orders.create') }}">
                                    <i class="ti ti-plus me-1"></i> ثبت سفارش خرید
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

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>شماره</th>
                                            <th>تامین کننده</th>
                                            <th>انبار</th>
                                            <th>تاریخ</th>
                                            <th class="text-end">مبلغ</th>
                                            <th class="text-end">مرجوعی</th>
                                            <th class="text-end">پرداخت شده</th>
                                            <th class="text-end">مانده</th>
                                            <th>وضعیت</th>
                                            <th>بودجه</th>
                                            <th>رسید</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchaseOrders as $purchaseOrder)
                                            <tr>
                                                <td>{{ $loop->iteration + ($purchaseOrders->currentPage() - 1) * $purchaseOrders->perPage() }}
                                                </td>
                                                <td>
                                                    {{ $purchaseOrder->order_number }}
                                                    @if ($purchaseOrder->procurement_source === 'direct_supply')
                                                        <br><span class="badge bg-label-warning">تامین مستقیم</span>
                                                    @endif
                                                    @if ($purchaseOrder->foreignImport)
                                                        <br><span
                                                            class="badge bg-label-info">{{ $purchaseOrder->foreignImport->import_number }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name }}
                                                </td>
                                                <td>{{ optional($purchaseOrder->store)->title }}</td>
                                                <td>{{ $purchaseOrder->order_date_fa ?: optional($purchaseOrder->order_date_en)->format('Y-m-d') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->total_amount) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->returned_amount) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->paid_amount) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->remaining_amount) }}</td>
                                                <td>
                                                    @if ($purchaseOrder->payment_status === 'paid')
                                                        <span class="badge bg-label-primary">تسویه شده</span>
                                                    @elseif ($purchaseOrder->payment_status === 'partial')
                                                        <span class="badge bg-label-info">پرداخت جزئی</span>
                                                    @elseif ($purchaseOrder->status === 'received')
                                                        <span class="badge bg-label-success">رسید شده</span>
                                                    @elseif ($purchaseOrder->status === 'partial_received')
                                                        <span class="badge bg-label-warning">دریافت ناقص</span>
                                                    @elseif ($purchaseOrder->approval_status === 'pending_approval')
                                                        <span class="badge bg-label-warning">در انتظار تایید</span>
                                                    @elseif ($purchaseOrder->approval_status === 'rejected')
                                                        <span class="badge bg-label-danger">برگشت تایید</span>
                                                    @elseif ($purchaseOrder->status === 'approved')
                                                        <span class="badge bg-label-info">تایید خرید</span>
                                                    @else
                                                        <span class="badge bg-label-warning">پیش نویس</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($purchaseOrder->budget_status === 'within_budget')
                                                        <span class="badge bg-label-success">داخل بودجه</span>
                                                    @elseif ($purchaseOrder->budget_status === 'over_budget')
                                                        <span class="badge bg-label-danger">خارج از بودجه</span>
                                                    @elseif ($purchaseOrder->budget_status === 'no_budget')
                                                        <span class="badge bg-label-secondary">بودجه ندارد</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($purchaseOrder->receiveDocuments->isNotEmpty())
                                                        {{ $purchaseOrder->receiveDocuments->count() }} رسید
                                                    @else
                                                        {{ optional($purchaseOrder->receipt)->number ?: '-' }}
                                                    @endif
                                                    @if ($purchaseOrder->invoices->isNotEmpty())
                                                        <br><span
                                                            class="badge bg-label-primary">{{ $purchaseOrder->invoices->where('status', '<>', 'canceled')->count() }}
                                                            فاکتور خرید</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (in_array($purchaseOrder->status, ['draft'], true) || $purchaseOrder->approval_status === 'rejected')
                                                        <form method="POST"
                                                            action="{{ route('purchase-orders.requestApproval', $purchaseOrder) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-warning"
                                                                type="submit">ارسال برای تایید</button>
                                                        </form>
                                                    @elseif ($purchaseOrder->approval_status === 'pending_approval')
                                                        <a class="btn btn-sm btn-outline-info"
                                                            href="{{ route('purchase-orders.approvals') }}">کارتابل
                                                            تایید</a>
                                                    @elseif ($purchaseOrder->status === 'approved')
                                                        <form method="POST"
                                                            action="{{ route('purchase-orders.approve', $purchaseOrder) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-success"
                                                                type="submit">دریافت کامل مانده</button>
                                                        </form>
                                                    @elseif ($purchaseOrder->status === 'received')
                                                        @if ($purchaseOrder->remaining_amount > 0)
                                                            <form method="POST"
                                                                action="{{ route('purchase-orders.pay', $purchaseOrder) }}"
                                                                class="row g-2 align-items-end">
                                                                @csrf
                                                                <div class="col-12 col-md-4">
                                                                    <input type="number" min="0.01"
                                                                        step="0.01" name="amount"
                                                                        class="form-control form-control-sm text-end"
                                                                        value="{{ $purchaseOrder->remaining_amount }}">
                                                                </div>
                                                                <div class="col-12 col-md-4">
                                                                    <select name="payment_method"
                                                                        class="form-select form-select-sm">
                                                                        <option value="3">بانک / کارت به کارت
                                                                        </option>
                                                                        <option value="1">نقدی</option>
                                                                        <option value="2">چک پرداختنی</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 col-md-4">
                                                                    <button class="btn btn-sm btn-outline-primary"
                                                                        type="submit">ثبت پرداخت</button>
                                                                </div>
                                                            </form>
                                                        @else
                                                            <span class="text-muted">ثبت شده</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($purchaseOrder->items->isNotEmpty())
                                                <tr>
                                                    <td></td>
                                                    <td colspan="12">
                                                        <div class="table-responsive">
                                                            @if (in_array($purchaseOrder->status, ['approved', 'partial_received'], true))
                                                                <form method="POST"
                                                                    action="{{ route('purchase-orders.receive', $purchaseOrder) }}"
                                                                    class="mb-3">
                                                                    @csrf
                                                                    <table class="table table-sm mb-0">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>کالا</th>
                                                                                <th class="text-end">سفارش</th>
                                                                                <th class="text-end">دریافت شده</th>
                                                                                <th class="text-end">مانده</th>
                                                                                <th class="text-end">فی</th>
                                                                                <th class="text-end">مقدار این رسید
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($purchaseOrder->items as $item)
                                                                                @php($remainingQuantity = max(0, round((float) $item->quantity - (float) $item->received_quantity, 3)))
                                                                                <tr>
                                                                                    <td>{{ optional($item->product)->title }}
                                                                                        {{ optional($item->product)->display_name }}
                                                                                        <input type="hidden"
                                                                                            name="purchase_order_item_id[]"
                                                                                            value="{{ $item->id }}">
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        {{ number_format((float) $item->quantity, 3) }}
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        {{ number_format((float) $item->received_quantity, 3) }}
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        {{ number_format($remainingQuantity, 3) }}
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        {{ number_format((float) $item->unit_price) }}
                                                                                    </td>
                                                                                    <td class="text-end"
                                                                                        style="max-width: 160px">
                                                                                        <input type="number"
                                                                                            min="0"
                                                                                            step="0.001"
                                                                                            max="{{ $remainingQuantity }}"
                                                                                            name="receive_quantity[]"
                                                                                            class="form-control form-control-sm text-end"
                                                                                            value="{{ $remainingQuantity }}">
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                    <div class="row g-2 mt-2 align-items-end">
                                                                        <div class="col-12 col-md-3">
                                                                            <input type="date"
                                                                                name="receive_date_en"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ now()->toDateString() }}">
                                                                        </div>
                                                                        <div class="col-12 col-md-6">
                                                                            <input type="text" name="description"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="شرح رسید مرحله ای خرید">
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-outline-success w-100">ثبت
                                                                                دریافت مرحله ای</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            @endif

                                                            @if (in_array($purchaseOrder->status, ['partial_received', 'received'], true))
                                                                @php
                                                                    $invoicedQuantities = $purchaseOrder->invoices
                                                                        ->where('status', '<>', 'canceled')
                                                                        ->flatMap->items->groupBy(
                                                                            'purchase_order_item_id',
                                                                        )
                                                                        ->map(
                                                                            fn($items) => (float) $items->sum(
                                                                                'quantity',
                                                                            ),
                                                                        );
                                                                    $hasInvoiceableQuantity = $purchaseOrder->items->contains(
                                                                        function ($item) use ($invoicedQuantities) {
                                                                            return max(
                                                                                0,
                                                                                round(
                                                                                    (float) $item->received_quantity -
                                                                                        (float) ($invoicedQuantities[
                                                                                            $item->id
                                                                                        ] ?? 0),
                                                                                    3,
                                                                                ),
                                                                            ) > 0;
                                                                        },
                                                                    );
                                                                @endphp
                                                                @if ($hasInvoiceableQuantity)
                                                                    <form method="POST"
                                                                        action="{{ route('purchase-orders.invoice.store', $purchaseOrder) }}"
                                                                        class="mb-3 border rounded p-2 bg-light">
                                                                        @csrf
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                                            <strong>ثبت فاکتور خرید و تطبیق با
                                                                                رسید</strong>
                                                                            <span class="text-muted small">فقط مقدار
                                                                                دریافت شده و صورتحساب نشده قابل ثبت
                                                                                است.</span>
                                                                        </div>
                                                                        <table class="table table-sm mb-0">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>کالا</th>
                                                                                    <th class="text-end">دریافت شده
                                                                                    </th>
                                                                                    <th class="text-end">فاکتور شده
                                                                                    </th>
                                                                                    <th class="text-end">قابل فاکتور
                                                                                    </th>
                                                                                    <th class="text-end">فی سفارش</th>
                                                                                    <th class="text-end">مقدار فاکتور
                                                                                    </th>
                                                                                    <th class="text-end">فی فاکتور</th>
                                                                                    <th class="text-end">مالیات</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($purchaseOrder->items as $item)
                                                                                    @php
                                                                                        $alreadyInvoiced =
                                                                                            (float) ($invoicedQuantities[
                                                                                                $item->id
                                                                                            ] ?? 0);
                                                                                        $invoiceableQuantity = max(
                                                                                            0,
                                                                                            round(
                                                                                                (float) $item->received_quantity -
                                                                                                    $alreadyInvoiced,
                                                                                                3,
                                                                                            ),
                                                                                        );
                                                                                    @endphp
                                                                                    <tr>
                                                                                        <td>{{ optional($item->product)->title }}
                                                                                            {{ optional($item->product)->display_name }}
                                                                                            <input type="hidden"
                                                                                                name="purchase_order_item_id[]"
                                                                                                value="{{ $item->id }}">
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format((float) $item->received_quantity, 3) }}
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format($alreadyInvoiced, 3) }}
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format($invoiceableQuantity, 3) }}
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format((float) $item->unit_price) }}
                                                                                        </td>
                                                                                        <td class="text-end"
                                                                                            style="max-width: 130px">
                                                                                            <input type="number"
                                                                                                min="0"
                                                                                                step="0.001"
                                                                                                max="{{ $invoiceableQuantity }}"
                                                                                                name="invoice_quantity[]"
                                                                                                class="form-control form-control-sm text-end"
                                                                                                value="{{ $invoiceableQuantity }}">
                                                                                        </td>
                                                                                        <td class="text-end"
                                                                                            style="max-width: 130px">
                                                                                            <input type="number"
                                                                                                min="0"
                                                                                                step="0.01"
                                                                                                name="invoice_unit_price[]"
                                                                                                class="form-control form-control-sm text-end"
                                                                                                value="{{ (float) $item->unit_price }}">
                                                                                        </td>
                                                                                        <td class="text-end"
                                                                                            style="max-width: 130px">
                                                                                            <input type="number"
                                                                                                min="0"
                                                                                                step="0.01"
                                                                                                name="tax_amount[]"
                                                                                                class="form-control form-control-sm text-end"
                                                                                                value="0">
                                                                                            <input type="hidden"
                                                                                                name="item_description[]"
                                                                                                value="">
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                        <div class="row g-2 mt-2 align-items-end">
                                                                            <div class="col-12 col-md-3">
                                                                                <input type="text"
                                                                                    name="supplier_invoice_number"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="شماره صورتحساب تامین کننده">
                                                                            </div>
                                                                            <div class="col-12 col-md-3">
                                                                                <input type="date"
                                                                                    name="invoice_date_en"
                                                                                    class="form-control form-control-sm"
                                                                                    value="{{ now()->toDateString() }}">
                                                                            </div>
                                                                            <div class="col-12 col-md-4">
                                                                                <input type="text"
                                                                                    name="description"
                                                                                    class="form-control form-control-sm"
                                                                                    placeholder="شرح فاکتور خرید">
                                                                            </div>
                                                                            <div class="col-12 col-md-2">
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-outline-primary w-100">ثبت
                                                                                    فاکتور خرید</button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                @endif

                                                                @if ($purchaseOrder->invoices->isNotEmpty())
                                                                    <div class="table-responsive mb-3">
                                                                        <table class="table table-sm mb-0">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>فاکتور خرید</th>
                                                                                    <th>صورتحساب تامین کننده</th>
                                                                                    <th class="text-end">کالا</th>
                                                                                    <th class="text-end">مالیات</th>
                                                                                    <th class="text-end">جمع</th>
                                                                                    <th class="text-end">انحراف قیمت
                                                                                    </th>
                                                                                    <th>سند</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($purchaseOrder->invoices as $invoice)
                                                                                    <tr>
                                                                                        <td>{{ $invoice->invoice_number }}
                                                                                        </td>
                                                                                        <td>{{ $invoice->supplier_invoice_number ?: '-' }}
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format((float) $invoice->goods_amount) }}
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format((float) $invoice->tax_amount) }}
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format((float) $invoice->total_amount) }}
                                                                                        </td>
                                                                                        <td class="text-end">
                                                                                            {{ number_format((float) $invoice->price_variance_amount) }}
                                                                                        </td>
                                                                                        <td>{{ optional($invoice->accountingVoucher)->voucher_number ?: '-' }}
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @endif

                                                                <form method="POST"
                                                                    action="{{ route('purchase-orders.returns', $purchaseOrder) }}">
                                                                    @csrf
                                                                    <table class="table table-sm mb-0">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>کالا</th>
                                                                                <th class="text-end">دریافت شده</th>
                                                                                <th class="text-end">فی</th>
                                                                                <th class="text-end">ارزش دریافت</th>
                                                                                <th class="text-end">تعداد مرجوعی</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($purchaseOrder->items as $item)
                                                                                <tr>
                                                                                    <td>{{ optional($item->product)->title }}
                                                                                        {{ optional($item->product)->display_name }}
                                                                                        <input type="hidden"
                                                                                            name="purchase_order_item_id[]"
                                                                                            value="{{ $item->id }}">
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        {{ number_format((float) $item->received_quantity, 3) }}
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        {{ number_format((float) $item->unit_price) }}
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        {{ number_format((float) $item->received_quantity * (float) $item->unit_price) }}
                                                                                    </td>
                                                                                    <td class="text-end"
                                                                                        style="max-width: 150px">
                                                                                        <input type="number"
                                                                                            min="0"
                                                                                            step="0.001"
                                                                                            name="return_quantity[]"
                                                                                            class="form-control form-control-sm text-end"
                                                                                            value="0">
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                    <div class="row g-2 mt-2 align-items-end">
                                                                        <div class="col-12 col-md-3">
                                                                            <input type="date"
                                                                                name="return_date_en"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ now()->toDateString() }}">
                                                                        </div>
                                                                        <div class="col-12 col-md-6">
                                                                            <input type="text" name="description"
                                                                                class="form-control form-control-sm"
                                                                                placeholder="شرح مرجوعی خرید">
                                                                        </div>
                                                                        <div class="col-12 col-md-3">
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-outline-danger w-100">ثبت
                                                                                مرجوعی</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="13" class="text-center text-muted py-4">سفارش خریدی ثبت
                                                    نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">{{ $purchaseOrders->links() }}</div>
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

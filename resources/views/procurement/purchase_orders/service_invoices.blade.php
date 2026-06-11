<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>فاکتور خدمات خرید - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> فاکتور خدمات و
                                هزینه جانبی خرید</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.supplierLedger') }}">
                                <i class="ti ti-file-analytics me-1"></i> گردش تامین کننده
                            </a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">تعداد
                                        فاکتور</small><strong>{{ number_format($totals['count']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">خالص
                                        خدمات/هزینه</small><strong>{{ number_format($totals['subtotal']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small
                                        class="text-muted">مالیات</small><strong>{{ number_format($totals['tax']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">جمع
                                        پرداختنی</small><strong>{{ number_format($totals['total']) }}</strong></div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">ثبت فاکتور خدمات یا هزینه جانبی</h5>
                            </div>
                            <form method="POST" action="{{ route('purchase-service-invoices.store') }}">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">تامین کننده</label>
                                            <select class="form-select" name="supplier_id" required>
                                                <option value="">انتخاب کنید</option>
                                                @foreach ($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">
                                                        {{ $supplier->title ?: $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">سفارش خرید مرتبط</label>
                                            <select class="form-select" name="purchase_order_id">
                                                <option value="">بدون سفارش</option>
                                                @foreach ($purchaseOrders as $order)
                                                    <option value="{{ $order->id }}">{{ $order->order_number }} -
                                                        {{ optional($order->supplier)->title ?: optional($order->supplier)->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">نوع فاکتور</label>
                                            <select class="form-select" name="invoice_type" required>
                                                <option value="service">خرید خدمات</option>
                                                <option value="additional_cost">هزینه جانبی خرید</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">تاریخ سند</label>
                                            <input class="form-control" name="invoice_date_en" type="date"
                                                value="{{ $today }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">شرح</label>
                                            <input class="form-control" name="description">
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>نوع هزینه</th>
                                                    <th>اثر حسابداری</th>
                                                    <th>حساب بدهکار اختیاری</th>
                                                    <th class="text-end">مبلغ</th>
                                                    <th class="text-end">مالیات</th>
                                                    <th>شرح</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @for ($index = 0; $index < 5; $index++)
                                                    <tr>
                                                        <td><input class="form-control form-control-sm"
                                                                name="item_title[]"
                                                                placeholder="حمل، نصب، بیمه، گمرک یا خدمت"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm"
                                                                name="cost_type[]">
                                                                <option value="service">خدمت</option>
                                                                <option value="freight">حمل</option>
                                                                <option value="insurance">بیمه</option>
                                                                <option value="customs">گمرک</option>
                                                                <option value="commission">کارمزد</option>
                                                                <option value="other">سایر</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm"
                                                                name="allocation_type[]">
                                                                <option value="expense">هزینه مستقیم</option>
                                                                <option value="landed_cost">هزینه جانبی قابل تخصیص
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm"
                                                                name="expense_account_id[]">
                                                                <option value="">خودکار</option>
                                                                @foreach ($accounts as $account)
                                                                    <option value="{{ $account->id }}">
                                                                        {{ $account->code }} - {{ $account->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td><input class="form-control form-control-sm text-end"
                                                                name="amount[]" type="number" min="0"
                                                                step="0.01" value="0"></td>
                                                        <td><input class="form-control form-control-sm text-end"
                                                                name="tax_amount[]" type="number" min="0"
                                                                step="0.01" value="0"></td>
                                                        <td><input class="form-control form-control-sm"
                                                                name="item_description[]"></td>
                                                    </tr>
                                                @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">ثبت فاکتور و سند حسابداری</button>
                                </div>
                            </form>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">فاکتورهای ثبت شده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره</th>
                                            <th>تامین کننده</th>
                                            <th>نوع</th>
                                            <th>سفارش مرتبط</th>
                                            <th class="text-end">جمع</th>
                                            <th>سند حسابداری</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($serviceInvoices as $invoice)
                                            <tr>
                                                <td>{{ $invoice->invoice_number }}<br><small
                                                        class="text-muted">{{ $invoice->invoice_date_fa ?: optional($invoice->invoice_date_en)->format('Y-m-d') }}</small>
                                                </td>
                                                <td>{{ optional($invoice->supplier)->title ?: optional($invoice->supplier)->name }}
                                                </td>
                                                <td>{{ $invoice->invoice_type === 'additional_cost' ? 'هزینه جانبی' : 'خدمت' }}
                                                </td>
                                                <td>{{ optional($invoice->purchaseOrder)->order_number ?: '-' }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $invoice->total_amount) }}</td>
                                                <td>
                                                    @if ($invoice->accountingVoucher)
                                                        {{ $invoice->accountingVoucher->voucher_number }}<br>
                                                        <small
                                                            class="text-muted">{{ $invoice->accountingVoucher->status === 'draft' ? 'موقت' : $invoice->accountingVoucher->status }}</small>
                                                    @else
                                                        <span class="text-muted">ثبت نشده</span>
                                                    @endif
                                                </td>
                                                <td>{{ $invoice->status === 'canceled' ? 'باطل شده' : 'تایید شده' }}
                                                </td>
                                                <td>
                                                    @if ($invoice->status !== 'canceled')
                                                        <form method="POST"
                                                            action="{{ route('purchase-service-invoices.cancel', $invoice) }}"
                                                            onsubmit="return confirm('فاکتور ابطال شود؟')">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                type="submit">ابطال</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">هنوز فاکتور
                                                    خدمات خرید ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $serviceInvoices->links() }}</div>
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

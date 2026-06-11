<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>سفارشات خارجی و واردات - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> سفارشات خارجی و
                                واردات</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">
                                    <i class="ti ti-arrow-right me-1"></i> سفارش خرید
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-orders.supplierLedger') }}">
                                    <i class="ti ti-file-analytics me-1"></i> گردش تامین کننده
                                </a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.report') }}">
                                    <i class="ti ti-report-analytics me-1"></i> گزارش خرید
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
                                    <div class="card-body">
                                        <div class="text-muted small">پرونده فعال</div>
                                        <h4 class="mb-0">{{ number_format($totals['count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">ارزش ارزی کالا</div>
                                        <h4 class="mb-0">{{ number_format((float) $totals['foreign_goods'], 4) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">هزینه های واردات</div>
                                        <h4 class="mb-0">{{ number_format((float) $totals['costs']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted small">بهای تمام شده وارداتی</div>
                                        <h4 class="mb-0">{{ number_format((float) $totals['landed']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('purchase-orders.foreignImports') }}"
                                    class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">وضعیت</label>
                                        <select name="status" class="form-select">
                                            <option value="">همه وضعیت ها</option>
                                            @foreach ($statusLabels as $statusKey => $statusTitle)
                                                <option value="{{ $statusKey }}" @selected(request('status') === $statusKey)>
                                                    {{ $statusTitle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">تامین کننده</label>
                                        <select name="supplier_id" class="form-select">
                                            <option value="">همه تامین کننده ها</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>
                                                    {{ $supplier->title ?: $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">ارز</label>
                                        <select name="currency_id" class="form-select">
                                            <option value="">همه ارزها</option>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}" @selected((string) request('currency_id') === (string) $currency->id)>
                                                    {{ $currency->code }} - {{ $currency->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3 d-flex gap-2">
                                        <button class="btn btn-primary w-100" type="submit">اعمال فیلتر</button>
                                        <a class="btn btn-outline-secondary"
                                            href="{{ route('purchase-orders.foreignImports') }}">پاکسازی</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">ثبت یا بروزرسانی پرونده واردات از سفارش خرید</h5>
                                <span class="text-muted small">اطلاعات خرید اصلی تغییر نمی کند؛ محاسبه landed cost جدا
                                    نگهداری می شود.</span>
                            </div>
                            <div class="accordion accordion-flush" id="foreignImportForms">
                                @forelse($purchaseOrders as $purchaseOrder)
                                    @php($foreignImport = $purchaseOrder->foreignImport)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="orderHeading{{ $purchaseOrder->id }}">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#orderForm{{ $purchaseOrder->id }}"
                                                aria-expanded="false"
                                                aria-controls="orderForm{{ $purchaseOrder->id }}">
                                                {{ $purchaseOrder->order_number }} /
                                                {{ optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name ?: 'بدون تامین کننده' }}
                                                @if ($foreignImport)
                                                    <span
                                                        class="badge bg-label-info ms-2">{{ $foreignImport->import_number }}
                                                        -
                                                        {{ $statusLabels[$foreignImport->status] ?? $foreignImport->status }}</span>
                                                @endif
                                            </button>
                                        </h2>
                                        <div id="orderForm{{ $purchaseOrder->id }}"
                                            class="accordion-collapse collapse"
                                            aria-labelledby="orderHeading{{ $purchaseOrder->id }}"
                                            data-bs-parent="#foreignImportForms">
                                            <div class="accordion-body">
                                                <form method="POST"
                                                    action="{{ route('purchase-orders.foreignImports.store') }}">
                                                    @csrf
                                                    <input type="hidden" name="purchase_order_id"
                                                        value="{{ $purchaseOrder->id }}">
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">ارز</label>
                                                            <select name="currency_id" class="form-select" required>
                                                                <option value="">انتخاب ارز</option>
                                                                @foreach ($currencies as $currency)
                                                                    <option value="{{ $currency->id }}"
                                                                        @selected((string) old('currency_id', $foreignImport?->currency_id) === (string) $currency->id)>
                                                                        {{ $currency->code }} - {{ $currency->title }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-md-2">
                                                            <label class="form-label">نرخ ارز</label>
                                                            <input type="number" min="0.000001" step="0.000001"
                                                                name="exchange_rate" class="form-control text-end"
                                                                value="{{ old('exchange_rate', $foreignImport?->exchange_rate ?: 1) }}"
                                                                required>
                                                        </div>
                                                        <div class="col-12 col-md-2">
                                                            <label class="form-label">وضعیت</label>
                                                            <select name="status" class="form-select">
                                                                @foreach ($statusLabels as $statusKey => $statusTitle)
                                                                    <option value="{{ $statusKey }}"
                                                                        @selected(old('status', $foreignImport?->status ?: 'draft') === $statusKey)>
                                                                        {{ $statusTitle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-md-2">
                                                            <label class="form-label">تاریخ سفارش خارجی</label>
                                                            <input type="date" name="order_date_en"
                                                                class="form-control"
                                                                value="{{ old('order_date_en', optional($foreignImport?->order_date_en ?: $purchaseOrder->order_date_en)->format('Y-m-d') ?: $today) }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">شماره پرونده</label>
                                                            <input type="text" name="import_number"
                                                                class="form-control"
                                                                value="{{ old('import_number', $foreignImport?->import_number) }}"
                                                                placeholder="خالی باشد، خودکار ساخته می شود">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">پروفرما</label>
                                                            <input type="text" name="proforma_number"
                                                                class="form-control"
                                                                value="{{ old('proforma_number', $foreignImport?->proforma_number) }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">قرارداد/ثبت سفارش</label>
                                                            <input type="text" name="contract_number"
                                                                class="form-control"
                                                                value="{{ old('contract_number', $foreignImport?->contract_number) }}">
                                                        </div>
                                                        <div class="col-12 col-md-2">
                                                            <label class="form-label">LC/حواله</label>
                                                            <input type="text" name="lc_number"
                                                                class="form-control"
                                                                value="{{ old('lc_number', $foreignImport?->lc_number) }}">
                                                        </div>
                                                        <div class="col-12 col-md-2">
                                                            <label class="form-label">کشور مبدا</label>
                                                            <input type="text" name="origin_country"
                                                                class="form-control"
                                                                value="{{ old('origin_country', $foreignImport?->origin_country) }}">
                                                        </div>
                                                        <div class="col-12 col-md-2">
                                                            <label class="form-label">روش حمل</label>
                                                            <input type="text" name="shipment_method"
                                                                class="form-control"
                                                                value="{{ old('shipment_method', $foreignImport?->shipment_method) }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">شماره اظهارنامه</label>
                                                            <input type="text" name="customs_declaration_number"
                                                                class="form-control"
                                                                value="{{ old('customs_declaration_number', $foreignImport?->customs_declaration_number) }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">بارنامه</label>
                                                            <input type="text" name="bill_of_lading_number"
                                                                class="form-control"
                                                                value="{{ old('bill_of_lading_number', $foreignImport?->bill_of_lading_number) }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">ETA</label>
                                                            <input type="date" name="expected_arrival_date_en"
                                                                class="form-control"
                                                                value="{{ old('expected_arrival_date_en', optional($foreignImport?->expected_arrival_date_en)->format('Y-m-d')) }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">تاریخ گمرک</label>
                                                            <input type="date" name="customs_date_en"
                                                                class="form-control"
                                                                value="{{ old('customs_date_en', optional($foreignImport?->customs_date_en)->format('Y-m-d')) }}">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">شرح</label>
                                                            <input type="text" name="description"
                                                                class="form-control"
                                                                value="{{ old('description', $foreignImport?->description) }}">
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-sm align-middle">
                                                            <thead>
                                                                <tr>
                                                                    <th>کالا</th>
                                                                    <th class="text-end">تعداد</th>
                                                                    <th class="text-end">فی ارزی</th>
                                                                    <th class="text-end">سهم دستی هزینه</th>
                                                                    <th>شرح ردیف</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($purchaseOrder->items as $item)
                                                                    @php($importItem = $foreignImport?->items?->firstWhere('purchase_order_item_id', $item->id))
                                                                    <tr>
                                                                        <td>{{ optional($item->product)->title }}
                                                                            {{ optional($item->product)->display_name }}
                                                                        </td>
                                                                        <td class="text-end">
                                                                            {{ number_format((float) $item->quantity, 3) }}
                                                                        </td>
                                                                        <td style="max-width: 160px">
                                                                            <input type="number" min="0"
                                                                                step="0.000001"
                                                                                name="foreign_unit_price[]"
                                                                                class="form-control form-control-sm text-end"
                                                                                value="{{ old('foreign_unit_price.' . $loop->index, $importItem?->foreign_unit_price) }}">
                                                                        </td>
                                                                        <td style="max-width: 160px">
                                                                            <input type="number" min="0"
                                                                                step="0.01"
                                                                                name="manual_allocation_amount[]"
                                                                                class="form-control form-control-sm text-end"
                                                                                value="{{ old('manual_allocation_amount.' . $loop->index, $importItem?->manual_allocation_amount) }}">
                                                                        </td>
                                                                        <td><input type="text"
                                                                                name="item_description[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('item_description.' . $loop->index, $importItem?->description ?: $item->description) }}">
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <h6 class="mb-2">هزینه های واردات و تخصیص</h6>
                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-sm align-middle">
                                                            <thead>
                                                                <tr>
                                                                    <th>عنوان</th>
                                                                    <th>نوع</th>
                                                                    <th>تاریخ</th>
                                                                    <th class="text-end">مبلغ ارزی</th>
                                                                    <th class="text-end">نرخ</th>
                                                                    <th class="text-end">مبلغ ریالی</th>
                                                                    <th>مبنای تخصیص</th>
                                                                    <th>شماره سند</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @for ($i = 0; $i < 5; $i++)
                                                                    @php($cost = $foreignImport?->costs?->values()->get($i))
                                                                    <tr>
                                                                        <td><input type="text" name="cost_title[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('cost_title.' . $i, $cost?->title) }}">
                                                                        </td>
                                                                        <td>
                                                                            <select name="cost_type[]"
                                                                                class="form-select form-select-sm">
                                                                                @foreach ($costTypes as $costKey => $costTitle)
                                                                                    <option
                                                                                        value="{{ $costKey }}"
                                                                                        @selected(old('cost_type.' . $i, $cost?->cost_type ?: 'other') === $costKey)>
                                                                                        {{ $costTitle }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="date"
                                                                                name="cost_date_en[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('cost_date_en.' . $i, optional($cost?->cost_date_en)->format('Y-m-d')) }}">
                                                                        </td>
                                                                        <td><input type="number" min="0"
                                                                                step="0.0001"
                                                                                name="cost_foreign_amount[]"
                                                                                class="form-control form-control-sm text-end"
                                                                                value="{{ old('cost_foreign_amount.' . $i, $cost?->foreign_amount) }}">
                                                                        </td>
                                                                        <td><input type="number" min="0"
                                                                                step="0.000001"
                                                                                name="cost_exchange_rate[]"
                                                                                class="form-control form-control-sm text-end"
                                                                                value="{{ old('cost_exchange_rate.' . $i, $cost?->exchange_rate ?: $foreignImport?->exchange_rate ?: 1) }}">
                                                                        </td>
                                                                        <td><input type="number" min="0"
                                                                                step="0.01"
                                                                                name="cost_base_amount[]"
                                                                                class="form-control form-control-sm text-end"
                                                                                value="{{ old('cost_base_amount.' . $i, $cost?->base_amount) }}">
                                                                        </td>
                                                                        <td>
                                                                            <select name="allocation_basis[]"
                                                                                class="form-select form-select-sm">
                                                                                @foreach ($allocationBases as $basisKey => $basisTitle)
                                                                                    <option
                                                                                        value="{{ $basisKey }}"
                                                                                        @selected(old('allocation_basis.' . $i, $cost?->allocation_basis ?: 'value') === $basisKey)>
                                                                                        {{ $basisTitle }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text"
                                                                                name="cost_document_number[]"
                                                                                class="form-control form-control-sm mb-1"
                                                                                value="{{ old('cost_document_number.' . $i, $cost?->document_number) }}"
                                                                                placeholder="شماره سند">
                                                                            <input type="text"
                                                                                name="cost_reference_number[]"
                                                                                class="form-control form-control-sm mb-1"
                                                                                value="{{ old('cost_reference_number.' . $i, $cost?->reference_number) }}"
                                                                                placeholder="مرجع">
                                                                            <input type="text"
                                                                                name="cost_description[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('cost_description.' . $i, $cost?->description) }}"
                                                                                placeholder="شرح">
                                                                        </td>
                                                                    </tr>
                                                                @endfor
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <h6 class="mb-2">اسناد واردات و گمرک</h6>
                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-sm align-middle">
                                                            <thead>
                                                                <tr>
                                                                    <th>نوع سند</th>
                                                                    <th>شماره</th>
                                                                    <th>تاریخ</th>
                                                                    <th>مرجع/مسیر فایل</th>
                                                                    <th>شرح</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @for ($i = 0; $i < 4; $i++)
                                                                    @php($document = $foreignImport?->documents?->values()->get($i))
                                                                    <tr>
                                                                        <td>
                                                                            <select name="document_type[]"
                                                                                class="form-select form-select-sm">
                                                                                @foreach ($documentTypes as $documentKey => $documentTitle)
                                                                                    <option
                                                                                        value="{{ $documentKey }}"
                                                                                        @selected(old('document_type.' . $i, $document?->document_type ?: 'other') === $documentKey)>
                                                                                        {{ $documentTitle }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="text"
                                                                                name="document_number[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('document_number.' . $i, $document?->document_number) }}">
                                                                        </td>
                                                                        <td><input type="date"
                                                                                name="document_date_en[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('document_date_en.' . $i, optional($document?->document_date_en)->format('Y-m-d')) }}">
                                                                        </td>
                                                                        <td>
                                                                            <input type="text"
                                                                                name="document_reference_number[]"
                                                                                class="form-control form-control-sm mb-1"
                                                                                value="{{ old('document_reference_number.' . $i, $document?->reference_number) }}"
                                                                                placeholder="مرجع">
                                                                            <input type="text"
                                                                                name="document_file_path[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('document_file_path.' . $i, $document?->file_path) }}"
                                                                                placeholder="مسیر فایل">
                                                                        </td>
                                                                        <td><input type="text"
                                                                                name="document_description[]"
                                                                                class="form-control form-control-sm"
                                                                                value="{{ old('document_description.' . $i, $document?->description) }}">
                                                                        </td>
                                                                    </tr>
                                                                @endfor
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="text-end">
                                                        <button type="submit" class="btn btn-primary">ثبت و محاسبه
                                                            بهای وارداتی</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted">سفارش خرید دارای قلم کالا برای تشکیل پرونده
                                        واردات پیدا نشد.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>پرونده</th>
                                            <th>سفارش خرید</th>
                                            <th>تامین کننده</th>
                                            <th>ارز/نرخ</th>
                                            <th>اسناد</th>
                                            <th class="text-end">ارزش کالا</th>
                                            <th class="text-end">هزینه ها</th>
                                            <th class="text-end">بهای وارداتی</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($foreignOrders as $foreignOrder)
                                            <tr>
                                                <td>
                                                    {{ $foreignOrder->import_number }}
                                                    <div class="text-muted small">
                                                        {{ $foreignOrder->origin_country ?: '-' }} /
                                                        {{ $foreignOrder->shipment_method ?: '-' }}</div>
                                                </td>
                                                <td>{{ optional($foreignOrder->purchaseOrder)->order_number ?: '-' }}
                                                </td>
                                                <td>{{ optional($foreignOrder->supplier)->title ?: optional($foreignOrder->supplier)->name ?: '-' }}
                                                </td>
                                                <td>{{ optional($foreignOrder->currency)->code ?: '-' }} /
                                                    {{ number_format((float) $foreignOrder->exchange_rate, 6) }}</td>
                                                <td>
                                                    <div class="small">پروفرما:
                                                        {{ $foreignOrder->proforma_number ?: '-' }}</div>
                                                    <div class="small">اظهارنامه:
                                                        {{ $foreignOrder->customs_declaration_number ?: '-' }}</div>
                                                    <div class="small">بارنامه:
                                                        {{ $foreignOrder->bill_of_lading_number ?: '-' }}</div>
                                                </td>
                                                <td class="text-end">
                                                    <div>
                                                        {{ number_format((float) $foreignOrder->foreign_goods_amount, 4) }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        {{ number_format((float) $foreignOrder->base_goods_amount) }}
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $foreignOrder->additional_cost_amount) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $foreignOrder->landed_cost_amount) }}</td>
                                                <td>
                                                    <form method="POST"
                                                        action="{{ route('purchase-orders.foreignImports.status', $foreignOrder) }}"
                                                        class="d-flex gap-2">
                                                        @csrf
                                                        <select name="status" class="form-select form-select-sm">
                                                            @foreach ($statusLabels as $statusKey => $statusTitle)
                                                                <option value="{{ $statusKey }}"
                                                                    @selected($foreignOrder->status === $statusKey)>{{ $statusTitle }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            type="submit">ثبت</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td colspan="8">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm mb-2">
                                                            <thead>
                                                                <tr>
                                                                    <th>کالا</th>
                                                                    <th class="text-end">تعداد</th>
                                                                    <th class="text-end">فی ارزی</th>
                                                                    <th class="text-end">کالا ریالی</th>
                                                                    <th class="text-end">سهم هزینه</th>
                                                                    <th class="text-end">بهای کل</th>
                                                                    <th class="text-end">فی تمام شده</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($foreignOrder->items as $item)
                                                                    <tr>
                                                                        <td>{{ optional($item->product)->title }}
                                                                            {{ optional($item->product)->display_name }}
                                                                        </td>
                                                                        <td class="text-end">
                                                                            {{ number_format((float) $item->quantity, 3) }}
                                                                        </td>
                                                                        <td class="text-end">
                                                                            {{ number_format((float) $item->foreign_unit_price, 6) }}
                                                                        </td>
                                                                        <td class="text-end">
                                                                            {{ number_format((float) $item->base_goods_amount) }}
                                                                        </td>
                                                                        <td class="text-end">
                                                                            {{ number_format((float) $item->allocated_cost_amount) }}
                                                                        </td>
                                                                        <td class="text-end">
                                                                            {{ number_format((float) $item->landed_total_amount) }}
                                                                        </td>
                                                                        <td class="text-end">
                                                                            {{ number_format((float) $item->landed_unit_cost, 6) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="small text-muted">
                                                        هزینه ها:
                                                        {{ $foreignOrder->costs->map(fn($cost) => $cost->title . ' ' . number_format((float) $cost->base_amount))->implode(' / ') ?: '-' }}
                                                    </div>
                                                    <div class="small text-muted">
                                                        اسناد تکمیلی:
                                                        {{ $foreignOrder->documents->map(fn($document) => ($documentTypes[$document->document_type] ?? $document->document_type) . ' ' . ($document->document_number ?: ''))->implode(' / ') ?: '-' }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">پرونده وارداتی
                                                    ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">{{ $foreignOrders->links() }}</div>
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

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>جزئیات استعلام بها - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> استعلام
                                {{ $purchaseRequisition->request_number }}</h4>
                            <div class="d-flex gap-2">
                                @if ($purchaseRequisition->purchaseOrder)
                                    <a class="btn btn-outline-primary"
                                        href="{{ route('purchase-orders.index') }}">سفارش
                                        {{ $purchaseRequisition->purchaseOrder->order_number }}</a>
                                @endif
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('purchase-requisitions.index') }}">بازگشت</a>
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
                            <div class="col-6 col-lg-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">انبار مقصد</small>
                                        <h6 class="mb-0">{{ optional($purchaseRequisition->store)->title }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تاریخ درخواست</small>
                                        <h6 class="mb-0">
                                            {{ $purchaseRequisition->request_date_fa ?: verta_date($purchaseRequisition->request_date_en) }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">پیشنهادها</small>
                                        <h6 class="mb-0 text-end">
                                            {{ number_format($purchaseRequisition->quotations->count()) }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">وضعیت</small>
                                        <h6 class="mb-0">
                                            {{ ['open' => 'باز', 'quoted' => 'دارای پیشنهاد', 'converted' => 'تبدیل شده'][$purchaseRequisition->status] ?? $purchaseRequisition->status }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">اقلام درخواست خرید</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>کالا</th>
                                            <th class="text-end">تعداد درخواستی</th>
                                            <th>شرح</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchaseRequisition->items as $item)
                                            <tr>
                                                <td>{{ optional($item->product)->title ?: optional($item->product)->display_name }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $item->quantity, 3) }}
                                                </td>
                                                <td>{{ $item->description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if (!$purchaseRequisition->converted_purchase_order_id)
                            <form method="POST"
                                action="{{ route('purchase-requisitions.quotations.store', $purchaseRequisition) }}"
                                class="card mb-4">
                                @csrf
                                <div class="card-header">
                                    <h5 class="mb-0">ثبت پیشنهاد تامین کننده</h5>
                                </div>
                                <div class="card-body row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">تامین کننده</label>
                                        <select name="supplier_id" class="form-select" required>
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                                                    {{ $supplier->title ?: $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">تاریخ پیشنهاد</label>
                                        <input type="date" name="quotation_date_en" class="form-control"
                                            value="{{ old('quotation_date_en', now()->toDateString()) }}">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">اعتبار تا</label>
                                        <input type="date" name="valid_until" class="form-control"
                                            value="{{ old('valid_until') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">شرح</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>کالا</th>
                                                <th class="text-end">تعداد</th>
                                                <th class="text-end">قیمت واحد</th>
                                                <th>شرح قلم</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($purchaseRequisition->items as $index => $item)
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="purchase_requisition_item_id[]"
                                                            value="{{ $item->id }}">
                                                        {{ optional($item->product)->title ?: optional($item->product)->display_name }}
                                                    </td>
                                                    <td><input type="number" step="0.001" min="0"
                                                            name="quantity[]" class="form-control text-end"
                                                            value="{{ old('quantity.' . $index, (float) $item->quantity) }}">
                                                    </td>
                                                    <td><input type="number" step="0.01" min="0"
                                                            name="unit_price[]" class="form-control text-end"
                                                            value="{{ old('unit_price.' . $index) }}"></td>
                                                    <td><input type="text" name="item_description[]"
                                                            class="form-control"
                                                            value="{{ old('item_description.' . $index) }}"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">ثبت پیشنهاد</button>
                                </div>
                            </form>
                        @endif

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">پیشنهادهای تامین کننده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره</th>
                                            <th>تامین کننده</th>
                                            <th>تاریخ</th>
                                            <th>اعتبار</th>
                                            <th class="text-end">مبلغ</th>
                                            <th>وضعیت</th>
                                            <th>اقلام</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchaseRequisition->quotations as $quotation)
                                            <tr>
                                                <td>{{ $quotation->quotation_number }}</td>
                                                <td>{{ optional($quotation->supplier)->title ?: optional($quotation->supplier)->name }}
                                                </td>
                                                <td>{{ $quotation->quotation_date_fa ?: verta_date($quotation->quotation_date_en) }}</td>
                                                <td>{{ verta_date($quotation->valid_until) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $quotation->total_amount) }}</td>
                                                <td>
                                                    @if ($quotation->status === 'selected')
                                                        <span class="badge bg-label-success">انتخاب شده</span>
                                                    @elseif ($quotation->status === 'not_selected')
                                                        <span class="badge bg-label-secondary">انتخاب نشده</span>
                                                    @else
                                                        <span class="badge bg-label-info">ثبت شده</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @foreach ($quotation->items as $item)
                                                        <div class="small text-muted">
                                                            {{ optional($item->product)->title ?: optional($item->product)->display_name }}:
                                                            {{ number_format((float) $item->quantity, 3) }} ×
                                                            {{ number_format((float) $item->unit_price) }}</div>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @if (!$purchaseRequisition->converted_purchase_order_id && $quotation->status !== 'selected')
                                                        <form method="POST"
                                                            action="{{ route('purchase-requisitions.quotations.select', [$purchaseRequisition, $quotation]) }}">
                                                            @csrf
                                                            <button class="btn btn-sm btn-success"
                                                                type="submit">انتخاب و تبدیل</button>
                                                        </form>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">هنوز پیشنهادی
                                                    ثبت نشده است.</td>
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

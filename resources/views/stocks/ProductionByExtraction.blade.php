<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>تولید و فرمول ساخت - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet" />
    <!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <!-- Layout container -->
            <div class="layout-page">
                @include('sections.header')
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">تولید و انبار /</span>
                            تولید و فرمول ساخت
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="row g-4 mb-4">
                                    <div class="col-12 col-xl-7">
                                        <form method="POST" action="{{ route('stocks.productionFormulas.store') }}">
                                            @csrf
                                            <div class="card h-100">
                                                <div
                                                    class="card-header d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="mb-1">تعریف فرمول ساخت / BOM</h5>
                                                        <small class="text-muted">برای هر محصول نهایی، مواد مصرفی و
                                                            ضایعات استاندارد را ثبت کنید.</small>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-sm">ثبت
                                                        فرمول</button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">محصول نهایی</label>
                                                            <select class="js-example-basic-single form-select"
                                                                name="product_id" required>
                                                                <option value="">--انتخاب کنید--</option>
                                                                @foreach ($Products as $product)
                                                                    <option value="{{ $product->id }}"
                                                                        @selected(old('product_id') == $product->id)>
                                                                        {{ $product->title }}
                                                                        {{ $product->display_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">عنوان فرمول</label>
                                                            <input type="text" name="title" class="form-control"
                                                                value="{{ old('title') }}"
                                                                placeholder="فرمول اصلی تولید" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">کد</label>
                                                            <input type="text" name="code" class="form-control"
                                                                value="{{ old('code') }}" placeholder="BOM-001">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">نسخه</label>
                                                            <input type="text" name="version" class="form-control"
                                                                value="{{ old('version', '1') }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">مقدار مبنا</label>
                                                            <input type="number" name="base_quantity"
                                                                class="form-control"
                                                                value="{{ old('base_quantity', 1) }}" step="0.001"
                                                                min="0.001" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">ضایعات استاندارد کل (%)</label>
                                                            <input type="number" name="standard_waste_percent"
                                                                class="form-control"
                                                                value="{{ old('standard_waste_percent', 0) }}"
                                                                step="0.001" min="0" max="100">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">توضیحات</label>
                                                            <input type="text" name="description"
                                                                class="form-control" value="{{ old('description') }}"
                                                                placeholder="شرایط تولید یا کنترل کیفیت">
                                                        </div>
                                                    </div>
                                                    <div class="table-responsive mt-4">
                                                        <table class="table table-sm align-middle">
                                                            <thead>
                                                                <tr>
                                                                    <th>ماده اولیه</th>
                                                                    <th>انبار مصرف</th>
                                                                    <th>مقدار در مبنا</th>
                                                                    <th>ضایعات ردیف (%)</th>
                                                                    <th>توضیح</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @for ($i = 0; $i < 5; $i++)
                                                                    <tr>
                                                                        <td style="min-width: 180px">
                                                                            <select class="form-select form-select-sm"
                                                                                name="material_product_id[]">
                                                                                <option value="">--ماده--
                                                                                </option>
                                                                                @foreach ($Materials as $material)
                                                                                    <option
                                                                                        value="{{ $material->id }}">
                                                                                        {{ $material->title }}
                                                                                        {{ $material->display_name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td style="min-width: 150px">
                                                                            <select class="form-select form-select-sm"
                                                                                name="material_store_id[]">
                                                                                <option value="">انبار تولید
                                                                                </option>
                                                                                @foreach ($Stores as $store)
                                                                                    <option
                                                                                        value="{{ $store->id }}">
                                                                                        {{ $store->title }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="number" name="quantity[]"
                                                                                class="form-control form-control-sm"
                                                                                step="0.001" min="0"></td>
                                                                        <td><input type="number"
                                                                                name="waste_percent[]"
                                                                                class="form-control form-control-sm"
                                                                                step="0.001" min="0"
                                                                                max="100" value="0"></td>
                                                                        <td><input type="text"
                                                                                name="item_description[]"
                                                                                class="form-control form-control-sm">
                                                                        </td>
                                                                    </tr>
                                                                @endfor
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-12 col-xl-5">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5 class="mb-0">ثبت تولید از فرمول</h5>
                                            </div>
                                            <div class="card-body">
                                                <form method="POST"
                                                    action="{{ route('stocks.ProductionByFormulaProcess') }}"
                                                    class="row g-3">
                                                    @csrf
                                                    <div class="col-md-6">
                                                        <label class="form-label">انبار محصول نهایی</label>
                                                        <select class="form-select" name="store_id" required>
                                                            <option value="">--انتخاب کنید--</option>
                                                            @foreach ($Stores as $store)
                                                                <option value="{{ $store->id }}">
                                                                    {{ $store->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">فرمول فعال</label>
                                                        <select class="form-select" name="production_formula_id"
                                                            required>
                                                            <option value="">--انتخاب کنید--</option>
                                                            @foreach ($ProductionFormulas->where('is_active', true) as $formula)
                                                                <option value="{{ $formula->id }}">
                                                                    {{ $formula->title }} -
                                                                    {{ optional($formula->product)->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">مقدار تولید واقعی</label>
                                                        <input type="number" name="actual_quantity"
                                                            class="form-control" step="0.001" min="0.001"
                                                            required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">تاریخ میلادی سند</label>
                                                        <input type="date" name="date_en" class="form-control"
                                                            value="{{ now()->toDateString() }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">یادداشت تولید</label>
                                                        <textarea name="notes" class="form-control" rows="2" placeholder="شماره بچ، شیفت، توضیح کنترل کیفیت"></textarea>
                                                    </div>
                                                    <div class="col-12 text-end">
                                                        <button type="submit" class="btn btn-success">ثبت تولید از
                                                            BOM</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0">فرمول های ثبت شده</h5>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>فرمول</th>
                                                            <th>محصول</th>
                                                            <th>مواد</th>
                                                            <th>وضعیت</th>
                                                            <th>عملیات</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($ProductionFormulas as $formula)
                                                            <tr>
                                                                <td>{{ $formula->title }}<br><small
                                                                        class="text-muted">{{ $formula->code ?: '-' }}
                                                                        / نسخه {{ $formula->version }}</small></td>
                                                                <td>{{ optional($formula->product)->title }}</td>
                                                                <td>{{ $formula->items->count() }}</td>
                                                                <td>{{ $formula->is_active ? 'فعال' : 'غیرفعال' }}</td>
                                                                <td>
                                                                    <form method="POST"
                                                                        action="{{ route('stocks.productionFormulas.toggle', $formula) }}">
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="btn btn-sm btn-outline-secondary">{{ $formula->is_active ? 'غیرفعال' : 'فعال' }}</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center text-muted">
                                                                    فرمولی ثبت نشده است.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('stocks.ProductionByExtractionProcess') }}">
                                    @csrf
                                    @if ($errors->any())
                                        <div class="alert alert-danger mx-3 mt-3">
                                            @foreach ($errors->all() as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="card">
                                        <div class="row my-3 px-3">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <label for="store_id">انبار:</label>
                                                        <select class="js-example-basic-single form-select"
                                                            id="store_id" name="store_id" style="width: 100%;"
                                                            required>
                                                            <option value="">--انتخاب کنید--</option>
                                                            @foreach ($Stores as $store)
                                                                <option value="{{ $store->id }}"
                                                                    @selected(old('store_id') == $store->id)>{{ $store->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="exampleInputEmail12">انتخاب ماده اولیه:</label>
                                                        <select class="js-example-basic-single form-select"
                                                            id="material_id" name="material_id" style="width: 100%;"
                                                            required>
                                                            <option value="">--انتخاب کنید--</option>
                                                            @foreach ($Materials as $material)
                                                                <option value="{{ $material->id }}"
                                                                    data-unit="{{ $material->pr_unit }}"
                                                                    data-subunit="{{ $material->pr_sub_unit }}"
                                                                    data-stock="{{ $material->current_stock > 0 ? $material->current_stock : '0' }}"
                                                                    @selected(old('material_id') == $material->id)>{{ $material->title }}
                                                                    {{ $material->display_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12 entity_box d-none mt-3">
                                                        <div>
                                                            <label for="exampleInputEmail12">مقدار وارده: <span
                                                                    class="unitplace"></span></label>
                                                            <input type="number" id="entity" class="form-control"
                                                                name="entity" placeholder="مقدار وارده"
                                                                step="0.001" min="0.001"
                                                                value="{{ old('entity') }}" />
                                                            <small class="stock_error text-danger"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 d-flex justify-content-end align-items-center">
                                                <button type="button" class="btn btn-primary btn-sm addpr">+ افزودن
                                                    محصول</button>

                                            </div>
                                        </div>
                                        <div class="card-datatable table-responsive py-0 prbox d-none">
                                            <table class="table yellow">
                                                <thead>
                                                    <tr>
                                                        <th width="40">ردیف</th>
                                                        <th>نام کالا</th>
                                                        <th>درصد</th>
                                                        <th>مقدار استحصال</th>
                                                        <th>مقدار طعم دار</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php($x = 1)
                                                    @foreach ($Products as $product)
                                                        <tr>
                                                            <td>{{ $x }}</td>
                                                            <td>
                                                                <input type="text" class="form-control"
                                                                    value="{{ $product->title }} {{ $product->display_name }}"
                                                                    readonly>
                                                                <input type="hidden" name="prs[]"
                                                                    value="{{ $product->id }}">
                                                            </td>
                                                            <td class="darsad"
                                                                style="direction: ltr;text-align:right"></td>
                                                            <td><input type="text" class="form-control estehsal"
                                                                    name="estehsal[]" /></td>
                                                            <td><input type="text" class="form-control taamdar"
                                                                    name="taamdar[]" /></td>
                                                        </tr>
                                                        @php($x++)
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="d-none">
                                                    <tr>
                                                        <th colspan="2">مقدار</th>
                                                        <th class="fullpercent"></th>
                                                        <th class="fullestehsal"></th>
                                                        <th class="fulltaamdar"></th>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="3" class="text-start text-right"
                                                            style="text-align: right !important;">مجموع تولید: <span
                                                                class="jamtolid"></span> <span
                                                                class="unitplace"></span></th>
                                                        <th colspan="2" class="text-end text-left">مجموع کسری
                                                            اضافه: <span class="jamKasri"></span> <span
                                                                class="unitplace"></span></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row justify-content-end mt-3">
                                        <div class="col-12 col-md-3 text-end">
                                            <button type="submit" class="btn btn-primary">ثبت سند تولید</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="card mt-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">آخرین اسناد تولید</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>شماره</th>
                                                    <th>انبار</th>
                                                    <th>فرمول</th>
                                                    <th>وضعیت</th>
                                                    <th>مقدار تولید</th>
                                                    <th>بهای مواد</th>
                                                    <th>بهای واحد خروجی</th>
                                                    <th>سند حسابداری</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ProductionOrders as $productionOrder)
                                                    <tr>
                                                        <td>{{ $productionOrder->number }}</td>
                                                        <td>{{ optional($productionOrder->store)->title }}</td>
                                                        <td>{{ optional($productionOrder->formula)->title ?: 'استحصال دستی' }}
                                                        </td>
                                                        <td>{{ $productionOrder->status === 'canceled' ? 'باطل شده' : 'تایید شده' }}
                                                        </td>
                                                        <td>{{ number_format((float) $productionOrder->actual_quantity, 3) }}
                                                        </td>
                                                        <td>{{ number_format((float) $productionOrder->material_cost) }}
                                                        </td>
                                                        <td>{{ number_format((float) $productionOrder->finished_unit_cost) }}
                                                        </td>
                                                        <td>
                                                            @if ($productionOrder->accountingVoucher)
                                                                {{ $productionOrder->accountingVoucher->voucher_number }}
                                                                <br><small
                                                                    class="text-muted">{{ $productionOrder->accountingVoucher->status === 'draft' ? 'موقت' : $productionOrder->accountingVoucher->status }}</small>
                                                            @else
                                                                <span class="text-muted">ثبت نشده</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($productionOrder->status !== 'canceled')
                                                                <form method="POST"
                                                                    action="{{ route('stocks.productionOrders.cancel', $productionOrder) }}"
                                                                    onsubmit="return confirm('سند تولید ابطال شود؟')">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">ابطال</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">هنوز سند
                                                            تولیدی ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">
                                        {{ $ProductionOrders->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Sticky Actions -->
                    </div>
                    <!-- / Content -->
                    @include('sections.footer')
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <!-- endbuild -->
    <script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <script src="
                https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.all.min.js
                "></script>
    <link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.min.css
" rel="stylesheet">
    <script>
        // datatable (jquery)
        $(function() {
            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: false,
                    pageLength: 5,
                });

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }


        });

        $(document).ready(function() {
            $('#material_id').on('change', function() {
                var dataUnit = $(this).find('option:selected').attr('data-unit');
                $('.unitplace').html("(" + dataUnit + ")");
                var dataSubUnit = $(this).find('option:selected').attr('data-subunit');
                var dataStock = $(this).find('option:selected').attr('data-stock');
                $('.subunitplace').html(dataSubUnit);
                $('#entity').val(dataStock);
                $('.entity_box').removeClass('d-none');

            });


            $('#entity').on('keyup', function() {
                var typin = $(this).val();
                var stock = $('#material_id').find('option:selected').attr('data-stock');
                if (stock == 0) {
                    $(this).val(0)
                } else {
                    if (parseInt(typin) > parseInt(stock)) {
                        $('.stock_error').html('مقدار وارد شده از موجودی محصول بیشتر است.');
                    } else {
                        $('.stock_error').html('');
                    }
                }
            });

            $('.addpr').click(function() {
                    var entity = $('#entity').val();
                    if (entity > 0) {
                        $('.prbox').removeClass('d-none')

                        /*  var counter = $('.table tbody tr').length+1
                         $('.table tbody').append('<tr><td>'+counter+'</td><td><select class="form-control" name="prs[]"><option>انتخاب محصول</option>if ( /*___directives_script_0___*/
                    ) {
                        <
                        option value = "{{ $product->id }}" >
                            {{ $product->title }} {{ $product->display_name }} < /option>/ ** *
                            script_placeholder ** * /} / * ___directives_script_1___ * /' +
                        '</select></td><td class="darsad" style="direction: ltr;text-align:right"></td><td><input type="text" class="form-control estehsal" name="estehsal[]" /></td><td><input type="text" class="form-control taamdar" name="taamdar[]" /></td></tr>'
                    );
                    $('tfoot').removeClass('d-none');*/
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "خطا",
                        text: "مقدار ماده اولیه مشخص نشده",
                        confirmButtonText: 'باشه',
                    });
                }

            });

        // تابع برای به روز رسانی مقادیر کلی (فقط estehsal کل)
        function updateOverallTotals() {
            var totalEstehsal = 0;
            var totalAllowedWeight = parseFloat($('#entity').val());

            $('.table tbody tr').each(function() {
                var currentEstehsal = parseFloat($(this).find('.estehsal').val() || 0);
                totalEstehsal += currentEstehsal;
            });

            // نمایش جمع کل استحصالح در المان .fullestehsal
            $('.fullestehsal').html(totalEstehsal.toFixed(2));

            // چون taamdar محاسبه نمی‌شود (فقط محدودیت ردیفی دارد), مقدارش را 0.00 قرار می‌دهیم
            $('.fulltaamdar').html('0.00');
            var finalOverallPercent = 0;
            if (totalAllowedWeight > 0) {
                // درصد کلی بر اساس مجموع estehsal و وزن کل مجاز
                finalOverallPercent = (totalEstehsal / totalAllowedWeight) * 100;
            }
            $('.fullpercent').html('%' + finalOverallPercent.toFixed(2));
        }


        // Event listener اصلی برای estehsal
        $('.table').on('input', '.estehsal', function() {
            var totalAllowedWeight = parseFloat($('#entity').val());
            var currentRow = $(this).closest('tr');

            var currentEstehsal = parseFloat($(this).val());
            if (isNaN(currentEstehsal)) currentEstehsal = 0;

            var sumOfPreviousRowsEstehsal = 0;
            currentRow.prevAll().each(function() {
                var prevEstehsal = parseFloat($(this).find('.estehsal').val() || 0);
                sumOfPreviousRowsEstehsal += prevEstehsal;
            });

            var remainingOverallAllowedWeight = totalAllowedWeight - sumOfPreviousRowsEstehsal;

            if (currentEstehsal > remainingOverallAllowedWeight) {
                var newEstehsal = remainingOverallAllowedWeight;
                if (newEstehsal < 0) newEstehsal = 0;
                $(this).val(newEstehsal.toFixed(2));
                currentEstehsal = newEstehsal;
            }

            // حالا که estehsal تغییر کرده، محدودیت taamdar را هم بررسی می‌کنیم
            var currentTaamdarField = currentRow.find('.taamdar');
            var currentTaamdar = parseFloat(currentTaamdarField.val() || 0);

            // اینجا از Math.floor() استفاده می‌کنیم تا اعشار را حذف کنیم
            if (currentTaamdar > currentEstehsal) {
                currentTaamdarField.val(Math.floor(currentEstehsal)); // تغییر به Math.floor
            }


            var percent = (currentEstehsal / totalAllowedWeight) * 100;
            var percentText = (percent % 1 === 0) ? percent.toFixed(0) : percent.toFixed(2);
            currentRow.find('.darsad').html('%' + percentText);

            updateOverallTotals();
        });

        // Event listener جدید برای taamdar
        $('.table').on('input', '.taamdar', function() {
            var currentRow = $(this).closest('tr');
            var currentTaamdar = parseFloat($(this).val());
            if (isNaN(currentTaamdar)) currentTaamdar = 0;

            var currentEstehsal = parseFloat(currentRow.find('.estehsal').val() || 0);

            // اعمال محدودیت: taamdar نباید از estehsal بیشتر باشد
            // اینجا از Math.floor() استفاده می‌کنیم تا اعشار را حذف کنیم
            if (currentTaamdar > currentEstehsal) {
                $(this).val(Math.floor(currentEstehsal)); // تغییر به Math.floor
            }
        });


        // هنگام بارگذاری اولیه صفحه (اگر مقادیری از قبل وجود دارد)
        $(document).ready(function() {
            updateOverallTotals();
        });




        });
    </script>
</body>

</html>

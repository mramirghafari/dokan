<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>تامین مستقیم - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> تامین مستقیم
                            </h4>
                            <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">بازگشت</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('purchase-orders.directSupply.store') }}">
                            @csrf
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">اطلاعات تامین مستقیم</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">تامین کننده</label>
                                            <select name="supplier_id" class="form-select select2-basic" required>
                                                <option value="">انتخاب تامین کننده</option>
                                                @foreach ($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                                        {{ $supplier->title ?: $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">انبار مقصد</label>
                                            <select name="store_id" class="form-select select2-basic" required>
                                                <option value="">انتخاب انبار</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">نوع سناریو</label>
                                            <select name="direct_supply_type" class="form-select" required>
                                                <option value="urgent_purchase">خرید فوری</option>
                                                <option value="field_purchase">خرید میدانی</option>
                                                <option value="no_requisition">بدون درخواست قبلی</option>
                                                <option value="manager_order">دستور مدیر</option>
                                                <option value="other">سایر</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">تاریخ سفارش</label>
                                            <input type="date" name="order_date_en" class="form-control"
                                                value="{{ old('order_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">مرجع</label>
                                            <input type="text" name="source_reference" class="form-control"
                                                value="{{ old('source_reference') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">دلیل تامین مستقیم</label>
                                            <input type="text" name="direct_supply_reason" class="form-control"
                                                value="{{ old('direct_supply_reason') }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شرح سفارش</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">اقلام تامین مستقیم</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 280px">کالا</th>
                                                <th class="text-end">تعداد</th>
                                                <th class="text-end">فی خرید</th>
                                                <th>شرح ردیف</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < 8; $i++)
                                                <tr>
                                                    <td>
                                                        <x-erp-remote-select
                                                            entity="products"
                                                            name="product_id[]"
                                                            :value="old('product_id.' . $i)"
                                                            placeholder="انتخاب کالا"
                                                            class="form-select erp-remote-select"
                                                            :filters="config('erp_scale.remote_lookup.product_filters')"
                                                        />
                                                    </td>
                                                    <td><input type="number" min="0" step="0.001"
                                                            name="quantity[]" class="form-control text-end"
                                                            value="{{ old('quantity.' . $i) }}"></td>
                                                    <td><input type="number" min="0" step="0.01"
                                                            name="unit_price[]" class="form-control text-end"
                                                            value="{{ old('unit_price.' . $i) }}"></td>
                                                    <td><input type="text" name="item_description[]"
                                                            class="form-control"
                                                            value="{{ old('item_description.' . $i) }}"></td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">ثبت و ارسال به تایید خرید</button>
                                </div>
                            </div>
                        </form>
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
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    <script>
        $(function() {
            $('.select2-basic:not(.erp-remote-select)').select2({
                width: '100%'
            });
        });
    </script>
</body>

</html>

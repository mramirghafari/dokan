<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت محصول جدید - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <link href="{{ asset('assets/css/product-edit.css') }}?v=4" rel="stylesheet" />
</head>

<body>
    <?php $Organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
    @php
        $featureBranchManagement = \App\Services\TenantSettings::enabled('feature_branch_management');
        $featureWarehouseManagement = \App\Services\TenantSettings::enabled('feature_warehouse_management');
        $featureDistribution = $featureDistribution ?? \App\Services\TenantSettings::enabled('feature_distribution');
        $featureAgencySales = $featureAgencySales ?? \App\Services\TenantSettings::enabled('feature_agency_sales');
    @endphp
    @include('sweetalert::alert')
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
                    <div class="container-xxl flex-grow-1 container-p-y product-edit-page">
                        <h4 class="row justify-content-between py-3 mb-2">
                            <div class="col-9">
                                <a href="{{ route('products.index') }}" class="text-muted fw-light">محصولات /</a>
                                ثبت محصول جدید
                            </div>
                            <div class="col-3 text-end">
                                <a href="{{ route('products.index') }}" class="btn btn-label-dark waves-effect"
                                    type="button">
                                    بازگشت
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="black" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            </div>
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card product-edit-card erp-form-card mb-4">
                                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
                                        id="addProduct" class="erp-form-card__form" novalidate>
                                        @csrf
                                        @include('errors.errors')

                                        <div class="product-edit-card__head">
                                            <div class="product-edit-card__tabs-scroll">
                                                <ul class="nav nav-tabs product-edit-tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <button aria-controls="navs-top-home" aria-selected="true"
                                                            class="nav-link active" data-bs-target="#navs-top-home"
                                                            data-bs-toggle="tab" role="tab" type="button">
                                                            <x-ui.icon name="package" />مشخصات اصلی
                                                        </button>
                                                    </li>
                                                    <li class="nav-item">
                                                        <button aria-controls="navs-top-profile" aria-selected="false"
                                                            class="nav-link" data-bs-target="#navs-top-profile"
                                                            data-bs-toggle="tab" role="tab" type="button">
                                                            <x-ui.icon name="building-warehouse" />
                                                            {{ $featureWarehouseManagement ? 'مشخصات انبار' : 'اطلاعات تکمیلی' }}
                                                        </button>
                                                    </li>
                                                    <li class="nav-item">
                                                        <button aria-controls="navs-top-messages" aria-selected="false"
                                                            class="nav-link" data-bs-target="#navs-top-messages"
                                                            data-bs-toggle="tab" role="tab" type="button">
                                                            <x-ui.icon name="adjustments" />ویژگی‌ها و تصویر
                                                        </button>
                                                    </li>
                                                    <li class="nav-item">
                                                        <button aria-controls="pricesinfo" aria-selected="false"
                                                            class="nav-link" data-bs-target="#pricesinfo"
                                                            data-bs-toggle="tab" role="tab" type="button">
                                                            <x-ui.icon name="currency-dollar" />اعلامیه قیمت
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="product-edit-card__body tab-content">
                                        <div class="tab-pane fade show active" id="navs-top-home" role="tabpanel">
                                            <div class="row g-3 p-3">
                                                <div class="col-md-4">
                                                    <label for="parentCategoryId">انتخاب دسته بندی<small
                                                            style="color: red">*</small></label>
                                                    <select class="select2 form-select" name="parentCategory_id"
                                                        id="parentCategoryId" style="width: 100%;">
                                                        <option value="0">--هیچکدام--</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}"
                                                                {{ old('parentCategoryId') == $category->id ? 'selected' : '' }}>
                                                                {{ $category->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="title">عنوان محصول<small
                                                            style="color: red">*</small></label>
                                                    <input type="text" class="form-control" name="title"
                                                        id="title" value="{{ old('title') }}"
                                                        placeholder="نام محصول" required />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="display_name">نام نمایشی</label>
                                                    <input type="text" class="form-control" name="display_name"
                                                        id="display_name" placeholder="نام نمایشی محصول" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="pr_sku">کد محصول:</label>
                                                    <input type="text" class="form-control" name="sku"
                                                        id="pr_sku" value="{{ old('sku') }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="product_type">نوع کالا</label>
                                                    <select class="form-select" id="product_type"
                                                        name="product_type">
                                                        @foreach ($productTypes as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (old('product_type', 'goods') === $value) selected @endif>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="stock_tracking_mode">کنترل موجودی</label>
                                                    <select class="form-select" id="stock_tracking_mode"
                                                        name="stock_tracking_mode">
                                                        @foreach ($stockTrackingModes as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (old('stock_tracking_mode', 'tracked') === $value) selected @endif>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="valuation_method">روش ارزش گذاری</label>
                                                    <select class="form-select" id="valuation_method"
                                                        name="valuation_method">
                                                        @foreach ($valuationMethods as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (old('valuation_method', 'weighted_average') === $value) selected @endif>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <h6 class="product-edit-section-title">واحدهای اندازه‌گیری</h6>
                                                <div class="row g-3 mb-3">
                                                <div class="col-md-4">
                                                    <label for="pr_unit">واحد اصلی محصول:</label>
                                                    <select class="select2 form-select" data-allow-clear="true"
                                                        id="pr_unit" name="base_unit_id" required>
                                                        <option value="0">انتخاب کنید</option>
                                                        @foreach ($productUnits as $unit)
                                                            <option value="{{ $unit->id }}"
                                                                @if (old('base_unit_id') == $unit->id) selected @endif>
                                                                {{ $unit->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="pr_sub_unit">واحد فرعی محصول:</label>
                                                    <select class="select2 form-select" data-allow-clear="true"
                                                        id="pr_sub_unit" name="secondary_unit_id">
                                                        <option value="0">انتخاب کنید</option>
                                                        @foreach ($productUnits as $unit)
                                                            <option value="{{ $unit->id }}"
                                                                @if (old('secondary_unit_id') == $unit->id) selected @endif>
                                                                {{ $unit->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="pack_items">تعداد واحد اصلی در واحد فرعی شامل <input
                                                            class="form-control" id="pack_items"
                                                            name="unit_conversion_factor"
                                                            value="{{ old('unit_conversion_factor') }}"
                                                            placeholder="" type="text"
                                                            style="width: 60px;display: inline" /> <span
                                                            class="unit_text">محصول</span> در
                                                        <span class="sub_unit_text">واحد فرعی</span> </label>

                                                </div>
                                                </div>

                                                @if ($featureDistribution)
                                                    <h6 class="product-edit-section-title">واحدهای باربری</h6>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-md-4">
                                                            <label for="shipping_base_unit">واحد اصلی باربری</label>
                                                            <select class="select2 form-select" data-allow-clear="true"
                                                                id="shipping_base_unit" name="pr_weight_unit">
                                                                <option value="">انتخاب کنید</option>
                                                                @foreach ($shippingUnits as $unit)
                                                                    <option value="{{ $unit->title }}"
                                                                        @selected(old('pr_weight_unit') === $unit->title)>
                                                                        {{ $unit->title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="shipping_secondary_unit">واحد فرعی باربری</label>
                                                            <select class="select2 form-select" data-allow-clear="true"
                                                                id="shipping_secondary_unit" name="pack_weight_unit">
                                                                <option value="">انتخاب کنید</option>
                                                                @foreach ($shippingUnits as $unit)
                                                                    <option value="{{ $unit->title }}"
                                                                        @selected(old('pack_weight_unit') === $unit->title)>
                                                                        {{ $unit->title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="pr_weight">وزن واحد اصلی</label>
                                                            <input aria-label="وزن اصلی محصول" class="form-control"
                                                                id="pr_weight" name="pr_weight" type="text"
                                                                value="{{ old('pr_weight') }}" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="pack_weight">وزن واحد فرعی</label>
                                                            <input aria-label="وزن فرعی محصول" class="form-control"
                                                                id="pack_weight" name="pack_weight" type="text"
                                                                value="{{ old('pack_weight') }}" />
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-top-profile" role="tabpanel">
                                            <div class="row">
                                                @if ($featureBranchManagement)
                                                    <div class="col-md-4">
                                                        <label for="organization_id">واحد های پخش مرتبط: </label>
                                                        <select class="select2 form-select" name="organization_id[]"
                                                            multiple id="organization_id" required>
                                                            <option value="0">انتخاب کنید</option>
                                                            @foreach ($organizations as $organization)
                                                                <option value="{{ $organization->id }}">
                                                                    {{ $organization->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                @if ($featureWarehouseManagement)
                                                    <div class="col-md-4">
                                                        <label for="store_id">انبارهای مرتبط:</label>
                                                        <select class="select2 form-select" name="store_id[]"
                                                            id="store_id" style="width: 100%;" multiple required>
                                                            <option value="">انتخاب کنید</option>
                                                            @foreach ($stores as $store)
                                                                <option value="{{ $store->id }}">
                                                                    {{ $store->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                <div class="col-md-4">
                                                    <label for="brand_id">برند</label>
                                                    <select class="select2 form-select" id="brand_id"
                                                        name="brand_id" required>
                                                        <option value="">انتخاب کنید</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}">{{ $brand->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="production_date">تاریخ تولید</label>
                                                    <input class="form-control" id="production_date"
                                                        name="production_date" placeholder="" type="text" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="expiry_date">تاریخ انقضا</label>
                                                    <input class="form-control" id="expiry_date" name="expiry_date"
                                                        placeholder="" type="text" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-top-messages" role="tabpanel">
                                            <div class="row mt-4">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label" for="order_quantity_mode">نحوه سفارش در ثبت سفارش</label>
                                                    <select class="form-select" id="order_quantity_mode" name="order_quantity_mode">
                                                        @foreach (\App\Models\Product::ORDER_QUANTITY_MODES as $modeKey => $modeLabel)
                                                            <option value="{{ $modeKey }}" @selected(old('order_quantity_mode', 'main_unit') === $modeKey)>
                                                                {{ $modeLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">تعیین می‌کند کاربر در صفحه ثبت سفارش چگونه تعداد وارد کند.</small>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="isFreez" id="isFreez">
                                                    <label for="isFreez" class="cr">محصول یخچالی میباشد.</label>
                                                </div>

                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="isActive" id="isActive">
                                                    <label for="isActive" class="cr">فعال</label>
                                                </div>

                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="isMaterial" id="isMaterial">
                                                    <label for="isMaterial" class="cr">در صورتی که محصول برای فروش
                                                        نیست و مواد اولیه میباشد فعال کنید</label>
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="form-group col-12">
                                                    <label>تصویر محصول:
                                                        <input accept="image/*" type="file" name="photo"
                                                            id="imgInp">
                                                        <img src="{{ asset('/img/core-img/placeholder-image.png') }}"
                                                            class="img-fluid avatar rounded"
                                                            style="width: 300px;height: 300px;border: 1px solid #0a53be" />
                                                    </label>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="tab-pane fade" id="pricesinfo" role="tabpanel">

                                            <div class="row">

                                                <div class="col-md-6 mb-3">
                                                    <label for="price">قیمت محصول <small
                                                            style="color: red;">*</small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت محصول" class="form-control seprator"
                                                            id="price" name="price" type="text"
                                                            value="0" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">
                                                            ریال
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="discount">حد تخفیف <small
                                                            style="color: red;">*</small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="حد تخفیف" class="form-control"
                                                            id="discount" name="discount" type="text"
                                                            value="0" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">درصد</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="max_discount_amount">حداکثر مبلغ تخفیف</label>
                                                    <div class="input-group">
                                                        <input aria-label="حداکثر مبلغ تخفیف" class="form-control seprator"
                                                            id="max_discount_amount" name="max_discount_amount" type="text"
                                                            value="{{ old('max_discount_amount', '0') }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                    <small class="text-muted">سقف مبلغی تخفیف برای هر ردیف در ثبت سفارش.</small>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="tax">ارزش افزوده<small
                                                            style="color: red;">*</small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت محصول" class="form-control"
                                                            id="tax" name="tax" type="text"
                                                            value="0">
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">درصد</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="purchase_price">قیمت خرید:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت خرید" class="form-control seprator"
                                                            id="purchase_price" name="purchase_price" type="text"
                                                            value="0" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="cost_price">قیمت تمام شده:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت تمام شده"
                                                            class="form-control seprator" id="cost_price"
                                                            name="cost_price" type="text" value="0" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                @if ($featureAgencySales)
                                                <div class="col-md-6 mb-3">
                                                    <label for="representative_price">قیمت نماینده:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت نماینده" class="form-control seprator"
                                                            id="representative_price" name="representative_price"
                                                            type="text" value="0" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="col-md-6 mb-3">
                                                    <label for="wholesale_price">قیمت عمده:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت عمده" class="form-control seprator"
                                                            id="wholesale_price" name="wholesale_price"
                                                            type="text" value="0" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="fee_masraf">قیمت مصرف کننده: <small
                                                            class="text-muted"></small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت مضرف کننده"
                                                            class="form-control seprator" id="fee_masraf"
                                                            name="fee_masraf" type="text" value="0" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 my-3">
                                                    <label for="price_date">درج قیمت از تاریخ</label>
                                                    <input class="form-control" id="price_date" name="price_date"
                                                        placeholder="" type="text" data-jdp />
                                                </div>
                                                <div class="col-md-6 my-3">
                                                    <label for="price_date_exp">درج قیمت تا تاریخ</label>
                                                    <input class="form-control" id="price_date_exp"
                                                        name="price_date_exp" placeholder="" type="text"
                                                        data-jdp />
                                                </div>
                                                <div class="col-12 mt-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0">بازه های زمانی قیمت</h6>
                                                        <button type="button" class="btn btn-sm btn-label-primary" id="add-price-range-row">
                                                            افزودن بازه
                                                        </button>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm align-middle" id="price-ranges-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>نوع قیمت</th>
                                                                    <th>مبلغ</th>
                                                                    <th>از تاریخ</th>
                                                                    <th>تا تاریخ</th>
                                                                    <th>اولویت</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php $existingRanges = old('price_ranges', [['price_type' => 'sale', 'amount' => old('price', '0'), 'starts_at' => old('price_date'), 'ends_at' => old('price_date_exp'), 'priority' => 0]]); @endphp
                                                                @foreach ($existingRanges as $index => $range)
                                                                    <tr>
                                                                        <td>
                                                                            <select class="form-select" name="price_ranges[{{ $index }}][price_type]">
                                                                                @foreach ($pricePeriodTypes as $typeKey => $typeLabel)
                                                                                    <option value="{{ $typeKey }}" @selected(($range['price_type'] ?? 'sale') === $typeKey)>{{ $typeLabel }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="text" class="form-control seprator" name="price_ranges[{{ $index }}][amount]" value="{{ $range['amount'] ?? '' }}"></td>
                                                                        <td><input type="text" class="form-control" data-jdp name="price_ranges[{{ $index }}][starts_at]" value="{{ $range['starts_at'] ?? '' }}"></td>
                                                                        <td><input type="text" class="form-control" data-jdp name="price_ranges[{{ $index }}][ends_at]" value="{{ $range['ends_at'] ?? '' }}"></td>
                                                                        <td><input type="number" class="form-control" name="price_ranges[{{ $index }}][priority]" value="{{ $range['priority'] ?? 0 }}"></td>
                                                                        <td><button type="button" class="btn btn-sm btn-label-danger remove-price-range-row">حذف</button></td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        @include('partials.erp-form-card-footer', [
                                            'submitLabel' => 'ایجاد محصول',
                                            'submitIcon' => 'ti-plus',
                                            'cancelUrl' => route('products.index'),
                                        ])
                                    </form>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

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
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script>
        jalaliDatepicker.startWatch();
        $('.products').addClass('open')
        $('.products .addnew').addClass('active open')

        $('#pr_unit').on('change', function() {
            var pr_unit = $('#pr_unit option:selected').text().trim();
            if (pr_unit && pr_unit !== 'انتخاب کنید') {
                $('.unit_text').html(pr_unit);
            }
        });

        $('#pr_sub_unit').on('change', function() {
            var pr_sub_unit = $('#pr_sub_unit option:selected').text().trim();
            if (pr_sub_unit && pr_sub_unit !== 'انتخاب کنید') {
                $('.sub_unit_text').html(pr_sub_unit);
            }
        });

        $('.seprator').on('input', function() {
            // همه کاراکترهای غیرعدد رو حذف کن
            let val = $(this).val().replace(/\D/g, '');

            // اگر رشته خالی نیست، سه‌رقم سه‌رقم جدا کن
            if (val !== '') {
                val = val.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $(this).val(val);
        });

        let priceRangeIndex = $('#price-ranges-table tbody tr').length;
        $('#add-price-range-row').on('click', function() {
            const row = `
                <tr>
                    <td>
                        <select class="form-select" name="price_ranges[${priceRangeIndex}][price_type]">
                            @foreach ($pricePeriodTypes as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" class="form-control seprator" name="price_ranges[${priceRangeIndex}][amount]" value=""></td>
                    <td><input type="text" class="form-control" data-jdp name="price_ranges[${priceRangeIndex}][starts_at]" value=""></td>
                    <td><input type="text" class="form-control" data-jdp name="price_ranges[${priceRangeIndex}][ends_at]" value=""></td>
                    <td><input type="number" class="form-control" name="price_ranges[${priceRangeIndex}][priority]" value="0"></td>
                    <td><button type="button" class="btn btn-sm btn-label-danger remove-price-range-row">حذف</button></td>
                </tr>
            `;
            $('#price-ranges-table tbody').append(row);
            jalaliDatepicker.startWatch();
            priceRangeIndex++;
        });

        $(document).on('click', '.remove-price-range-row', function() {
            $(this).closest('tr').remove();
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#addProduct').on('submit', function(e) {
                let isValid = true;

                // پاک کردن پیام‌های قبلی
                $('.error-message').remove();

                // چک کردن تمام فیلدهای این فرم که الزامی هستند
                $(this).find('input[required], select[required]').each(function() {
                    let $field = $(this);
                    let value = $.trim($field.val());

                    // شرط: اگر select باشد و مقدارش 0 باشد، یا اینپوت خالی باشد
                    if (($field.is('select') && value === '0') ||
                        ($field.is('input') && value === '')) {

                        isValid = false;

                        // ساخت پیام خطا
                        let errorMsg = $(
                            '<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>'
                        );

                        // درج پیام بعد از فیلد
                        if ($field.next('.select2').length) {
                            // اگر select2 هست، پیام رو بعد از container select2 بذاریم
                            $field.next('.select2').after(errorMsg);
                        } else {
                            $field.after(errorMsg);
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault(); // جلوگیری از ارسال فرم
                }
            });
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ویرایش محصول - دکان دارمینو</title>
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
<?php $Organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
@php
    $featureBranchManagement = \App\Services\TenantSettings::enabled('feature_branch_management');
    $featureWarehouseManagement = \App\Services\TenantSettings::enabled('feature_warehouse_management');
    $featureDistribution = $featureDistribution ?? \App\Services\TenantSettings::enabled('feature_distribution');
    $featureAgencySales = $featureAgencySales ?? \App\Services\TenantSettings::enabled('feature_agency_sales');
@endphp

<body>
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
                        @php
                            use Carbon\Carbon;
                        @endphp

                        <nav aria-label="breadcrumb" class="mb-3" id="tour-product-breadcrumb">
                            <a href="{{ route('products.index') }}" class="text-muted fw-light">محصولات /</a>
                            <span class="text-body">ویرایش محصول</span>
                        </nav>

                        @if ($PriceLogLast)
                            @php
                                $targetDate = Carbon::parse($PriceLogLast->price_exp_en);
                                $now = Carbon::now();
                            @endphp
                            @if ($targetDate->isFuture() && $now->diffInDays($targetDate) <= 7)
                                <div class="alert alert-price-warning mb-3" id="tour-product-price-alert" role="alert">
                                    <x-ui.icon name="alert-triangle" class="me-1" />
                                    کمتر از یک هفته تا پایان بازهٔ قیمت‌گذاری این محصول باقی مانده است.
                                </div>
                            @endif
                        @endif

                        <div class="product-edit-hero card mb-4" id="tour-product-hero">
                            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <span class="product-edit-hero__eyebrow">ویرایش محصول</span>
                                    <h4 class="mb-2">{{ $product->display_name ?: $product->title }}</h4>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <span class="badge bg-label-primary">SKU: {{ $product->sku ?: '—' }}</span>
                                        @if ($product->isActive)
                                            <span class="badge bg-label-success">فعال</span>
                                        @else
                                            <span class="badge bg-label-danger">غیرفعال</span>
                                        @endif
                                        @if ($product->isMaterial)
                                            <span class="badge bg-label-warning">مواد اولیه</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2" id="tour-product-hero-actions">
                                    <a href="{{ route('products.index') }}" class="btn btn-label-secondary">
                                        <x-ui.icon name="arrow-right" class="me-1" />بازگشت
                                    </a>
                                    <button class="btn btn-primary" form="product-edit-form" type="submit"
                                        id="tour-product-save-top">
                                        <x-ui.icon name="device-floppy" class="me-1" />به‌روزرسانی محصول
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card product-edit-card erp-form-card mb-4" id="tour-product-form-card">
                            <form action="{{ route('products.update', $product->id) }}" method="POST"
                                enctype="multipart/form-data" id="product-edit-form" class="erp-form-card__form">
                                @csrf
                                @method('PATCH')
                                @include('errors.errors')

                                <div class="product-edit-card__head">
                                    <div class="product-edit-card__tabs-scroll">
                                        <ul class="nav nav-tabs product-edit-tabs" role="tablist"
                                            id="tour-product-tabs">
                                            <li class="nav-item">
                                                <button aria-controls="navs-top-home" aria-selected="true"
                                                    class="nav-link active" data-bs-target="#navs-top-home"
                                                    data-bs-toggle="tab" role="tab" type="button"
                                                    id="tour-product-tab-main">
                                                    <x-ui.icon name="package" />مشخصات اصلی
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button aria-controls="navs-top-profile" aria-selected="false"
                                                    class="nav-link" data-bs-target="#navs-top-profile"
                                                    data-bs-toggle="tab" role="tab" type="button"
                                                    id="tour-product-tab-extra">
                                                    <x-ui.icon name="building-warehouse" />
                                                    {{ $featureWarehouseManagement ? 'مشخصات انبار' : 'اطلاعات تکمیلی' }}
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button aria-controls="navs-top-messages" aria-selected="false"
                                                    class="nav-link" data-bs-target="#navs-top-messages"
                                                    data-bs-toggle="tab" role="tab" type="button"
                                                    id="tour-product-tab-features">
                                                    <x-ui.icon name="adjustments" />ویژگی‌ها و تصویر
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button aria-controls="pricesinfo" aria-selected="false"
                                                    class="nav-link" data-bs-target="#pricesinfo" data-bs-toggle="tab"
                                                    role="tab" type="button" id="tour-product-tab-pricing">
                                                    <x-ui.icon name="currency-dollar" />اعلامیه قیمت
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="product-edit-card__body tab-content">
                                        <div class="tab-pane fade show active" id="navs-top-home" role="tabpanel">
                                            <h6 class="product-edit-section-title">شناسه و دسته‌بندی</h6>
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-4" id="tour-product-category">
                                                    <label class="form-label" for="parentCategoryId">دسته‌بندی</label>
                                                    <select class="select2 form-select" name="parentCategory_id"
                                                        id="parentCategoryId" style="width: 100%;">
                                                        <option value="">— بدون دسته —</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}"
                                                                {{ $category->id == $product->parentCategory_id ? 'selected' : '' }}>
                                                                {{ $category->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4" id="tour-product-title">
                                                    <label class="form-label" for="title">عنوان محصول</label>
                                                    <input type="text" class="form-control" name="title" id="title"
                                                        value="{{ $product->title }}" placeholder="نام داخلی محصول" />
                                                </div>
                                                <div class="col-md-4" id="tour-product-display-name">
                                                    <label class="form-label" for="display_name">نام نمایشی</label>
                                                    <input type="text" class="form-control" name="display_name"
                                                        id="display_name" value="{{ $product->display_name }}"
                                                        placeholder="نامی که روی فاکتور دیده می‌شود" />
                                                </div>
                                                <div class="col-md-4" id="tour-product-sku">
                                                    <label class="form-label" for="pr_sku">کد محصول (SKU)</label>
                                                    <input type="text" class="form-control" name="sku" id="pr_sku"
                                                        value="{{ $product->sku }}">
                                                </div>
                                            </div>

                                            <h6 class="product-edit-section-title">نوع کالا و موجودی</h6>
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-4" id="tour-product-type">
                                                    <label class="form-label" for="product_type">نوع کالا</label>
                                                    <select class="form-select" id="product_type" name="product_type">
                                                        @foreach ($productTypes as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (old('product_type', $product->product_type ?: 'goods') === $value) selected @endif>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4" id="tour-product-stock-tracking">
                                                    <label class="form-label" for="stock_tracking_mode">کنترل موجودی</label>
                                                    <select class="form-select" id="stock_tracking_mode"
                                                        name="stock_tracking_mode">
                                                        @foreach ($stockTrackingModes as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (old('stock_tracking_mode', $product->stock_tracking_mode ?: 'tracked') === $value) selected @endif>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4" id="tour-product-valuation">
                                                    <label class="form-label" for="valuation_method">روش ارزش‌گذاری</label>
                                                    <select class="form-select" id="valuation_method"
                                                        name="valuation_method">
                                                        @foreach ($valuationMethods as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (old('valuation_method', $product->valuation_method ?: 'weighted_average') === $value) selected @endif>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <h6 class="product-edit-section-title">واحدهای اندازه‌گیری</h6>
                                            <div class="row g-3">
                                                <div class="col-md-4" id="tour-product-base-unit">
                                                    <label class="form-label" for="pr_unit">واحد اصلی</label>
                                                    <select class="select2 form-select" data-allow-clear="true"
                                                        id="pr_unit" name="base_unit_id">
                                                        <option value="">انتخاب کنید</option>
                                                        @foreach ($productUnits as $unit)
                                                            <option value="{{ $unit->id }}"
                                                                @if (old('base_unit_id', $product->base_unit_id) == $unit->id) selected @endif>
                                                                {{ $unit->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4" id="tour-product-secondary-unit">
                                                    <label class="form-label" for="pr_sub_unit">واحد فرعی</label>
                                                    <select class="select2 form-select" data-allow-clear="true"
                                                        id="pr_sub_unit" name="secondary_unit_id">
                                                        <option value="">انتخاب کنید</option>
                                                        @foreach ($productUnits as $unit)
                                                            <option value="{{ $unit->id }}"
                                                                @if (old('secondary_unit_id', $product->secondary_unit_id) == $unit->id) selected @endif>
                                                                {{ $unit->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-8" id="tour-product-conversion">
                                                    <label class="form-label d-block">ضریب تبدیل واحد</label>
                                                    <div class="conversion-inline">
                                                        <span>هر</span>
                                                        <span class="sub_unit_text fw-semibold">واحد فرعی</span>
                                                        <span>شامل</span>
                                                        <input class="form-control" id="pack_items"
                                                            name="unit_conversion_factor"
                                                            value="{{ $product->unit_conversion_factor ?: $product->pack_items }}"
                                                            type="text" />
                                                        <span class="unit_text fw-semibold">واحد اصلی</span>
                                                        <span>است.</span>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($featureDistribution)
                                                <h6 class="product-edit-section-title">واحدهای باربری</h6>
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="shipping_base_unit">واحد اصلی باربری</label>
                                                        <select class="select2 form-select" data-allow-clear="true"
                                                            id="shipping_base_unit" name="pr_weight_unit">
                                                            <option value="">انتخاب کنید</option>
                                                            @foreach ($shippingUnits as $unit)
                                                                <option value="{{ $unit->title }}"
                                                                    @selected(old('pr_weight_unit', $product->pr_weight_unit) === $unit->title)>
                                                                    {{ $unit->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="shipping_secondary_unit">واحد فرعی باربری</label>
                                                        <select class="select2 form-select" data-allow-clear="true"
                                                            id="shipping_secondary_unit" name="pack_weight_unit">
                                                            <option value="">انتخاب کنید</option>
                                                            @foreach ($shippingUnits as $unit)
                                                                <option value="{{ $unit->title }}"
                                                                    @selected(old('pack_weight_unit', $product->pack_weight_unit) === $unit->title)>
                                                                    {{ $unit->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="pr_weight">وزن واحد اصلی</label>
                                                        <input aria-label="وزن اصلی محصول" class="form-control"
                                                            id="pr_weight" name="pr_weight" type="text"
                                                            value="{{ $product->pr_weight }}" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="pack_weight">وزن واحد فرعی</label>
                                                        <input aria-label="وزن فرعی محصول" class="form-control"
                                                            id="pack_weight" name="pack_weight"
                                                            value="{{ $product->pack_weight }}" type="text" />
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="tab-pane fade" id="navs-top-profile" role="tabpanel">
                                            <h6 class="product-edit-section-title">ارتباط با انبار و برند</h6>
                                            <div class="row g-3 mb-4">
                                                @if ($featureBranchManagement)
                                                    <div class="col-md-4" id="tour-product-organizations">
                                                        <label class="form-label" for="organization_id">واحدهای پخش</label>
                                                        <select class="select2 form-select" data-allow-clear="true"
                                                            name="organization_id[]" multiple id="organization_id">
                                                            <option value="0">انتخاب کنید</option>
                                                            @foreach ($organizations as $organization)
                                                                <option value="{{ $organization->id }}"
                                                                    @if (is_array(json_decode($product->organization_id)) &&
                                                                            in_array($organization->id, json_decode($product->organization_id))) selected @endif>
                                                                    {{ $organization->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                @if ($featureWarehouseManagement)
                                                    <div class="col-md-4" id="tour-product-stores">
                                                        <label class="form-label" for="store_id">انبارهای مرتبط</label>
                                                        <select class="select2 form-select" name="store_id[]"
                                                            id="store_id" style="width: 100%;" multiple required>
                                                            <option value="">انتخاب کنید</option>
                                                            @foreach ($stores as $store)
                                                                <option value="{{ $store->id }}"
                                                                    @if (is_array(json_decode($product->store_id)) && in_array($store->id, json_decode($product->store_id))) selected @endif>
                                                                    {{ $store->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                <div class="col-md-4" id="tour-product-brand">
                                                    <label class="form-label" for="brand_id">برند</label>
                                                    <select class="select2 form-select" data-allow-clear="true"
                                                        id="brand_id" name="brand_id">
                                                        <option value="">انتخاب کنید</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}"
                                                                @if ($brand->id == $product->brand_id) selected @endif>
                                                                {{ $brand->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <h6 class="product-edit-section-title">تاریخ تولید و انقضا</h6>
                                            <div class="row g-3" id="tour-product-dates">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="production_date">تاریخ تولید</label>
                                                    <input class="form-control" id="production_date"
                                                        name="production_date" placeholder="۱۴۰۴/۰۱/۰۱" type="text" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label" for="expiry_date">تاریخ انقضا</label>
                                                    <input class="form-control" id="expiry_date" name="expiry_date"
                                                        placeholder="۱۴۰۵/۰۱/۰۱" type="text" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-top-messages" role="tabpanel">
                                            <div class="product-edit-panel" id="tour-product-order-qty-mode">
                                                <h6 class="product-edit-panel__title">نحوه سفارش در ثبت سفارش</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="order_quantity_mode">حالت انتخاب تعداد</label>
                                                        <select class="form-select" id="order_quantity_mode" name="order_quantity_mode">
                                                            @foreach (\App\Models\Product::ORDER_QUANTITY_MODES as $modeKey => $modeLabel)
                                                                <option value="{{ $modeKey }}"
                                                                    @selected(old('order_quantity_mode', $product->resolveOrderQuantityMode()) === $modeKey)>
                                                                    {{ $modeLabel }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-muted d-block mt-1">مثلاً برای پلن‌های اشتراکی «تک‌فروشی» را انتخاب کنید.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="product-edit-panel" id="tour-product-toggles-wrap">
                                                <h6 class="product-edit-panel__title">تنظیمات فروش و وضعیت</h6>
                                                <div class="row g-3" id="tour-product-toggles">
                                                    <div class="col-sm-6 col-xl-4">
                                                        <div class="product-toggle-card {{ $product->isFreez ? 'is-on' : '' }}"
                                                            id="tour-product-freeze">
                                                            <div class="product-toggle-card__body">
                                                                <div class="form-check form-switch m-0 flex-shrink-0">
                                                                    <input type="checkbox"
                                                                        class="form-check-input product-toggle-input"
                                                                        name="isFreez" id="isFreez"
                                                                        {{ $product->isFreez ? 'checked' : '' }}>
                                                                </div>
                                                                <div class="product-toggle-card__text">
                                                                    <label class="mb-0" for="isFreez"><strong>محصول یخچالی</strong></label>
                                                                    <small>برای باربری سرد</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-6 col-xl-4">
                                                        <div class="product-toggle-card {{ $product->set_price ? 'is-on' : '' }}"
                                                            id="tour-product-set-price">
                                                            <div class="product-toggle-card__body">
                                                                <div class="form-check form-switch m-0 flex-shrink-0">
                                                                    <input type="checkbox"
                                                                        class="form-check-input product-toggle-input"
                                                                        name="set_price" id="set_price"
                                                                        {{ $product->set_price ? 'checked' : '' }}>
                                                                </div>
                                                                <div class="product-toggle-card__text">
                                                                    <label class="mb-0" for="set_price"><strong>قیمت حین سفارش</strong></label>
                                                                    <small>قیمت در لحظه فاکتور</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-6 col-xl-4">
                                                        <div class="product-toggle-card {{ $product->isActive ? 'is-on' : '' }}"
                                                            id="tour-product-active">
                                                            <div class="product-toggle-card__body">
                                                                <div class="form-check form-switch m-0 flex-shrink-0">
                                                                    <input type="checkbox"
                                                                        class="form-check-input product-toggle-input"
                                                                        name="isActive" id="isActive"
                                                                        {{ $product->isActive ? 'checked' : '' }}>
                                                                </div>
                                                                <div class="product-toggle-card__text">
                                                                    <label class="mb-0" for="isActive"><strong>فعال</strong></label>
                                                                    <small>قابل انتخاب در فاکتور</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-6 col-xl-4">
                                                        <div class="product-toggle-card {{ $product->isMaterial ? 'is-on' : '' }}"
                                                            id="tour-product-material">
                                                            <div class="product-toggle-card__body">
                                                                <div class="form-check form-switch m-0 flex-shrink-0">
                                                                    <input type="checkbox"
                                                                        class="form-check-input product-toggle-input"
                                                                        name="isMaterial" id="isMaterial"
                                                                        {{ $product->isMaterial ? 'checked' : '' }}>
                                                                </div>
                                                                <div class="product-toggle-card__text">
                                                                    <label class="mb-0" for="isMaterial"><strong>مواد اولیه</strong></label>
                                                                    <small>فقط برای تولید</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3 mt-1">
                                                <div class="col-12">
                                                    <div class="product-edit-panel h-100" id="tour-product-image">
                                                        <h6 class="product-edit-panel__title">تصویر محصول</h6>
                                                        <label class="product-image-upload d-block mb-0" for="imgInp">
                                                            <input accept="image/*" type="file" name="photo" id="imgInp"
                                                                class="d-none">
                                                            <img src="{{ $product->photo == null ? asset('/img/core-img/placeholder-image.png') : asset("/storage/uploads/$product->photo") }}"
                                                                alt="تصویر محصول" id="product-image-preview" />
                                                            <p class="product-image-upload__hint mb-0">
                                                                <x-ui.icon name="upload" class="me-1" />برای تغییر تصویر کلیک کنید
                                                            </p>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="pricesinfo" role="tabpanel">
                                            <div class="product-edit-panel">
                                                <h6 class="product-edit-panel__title">قیمت فروش و تخفیف</h6>
                                                <div class="row g-3">
                                                <div class="col-md-6" id="tour-product-price">
                                                    <label class="form-label" for="price">قیمت فروش</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت محصول" class="form-control" id="price"
                                                            name="price" type="text"
                                                            value="{{ number_format(intval($product->price)) }}" />
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="tour-product-discount">
                                                    <label class="form-label" for="discount">حداکثر تخفیف (درصد)</label>
                                                    <div class="input-group">
                                                        <input aria-label="حد تخفیف" class="form-control" id="discount"
                                                            name="discount" type="text"
                                                            value="{{ $product->discount }}" />
                                                        <span class="input-group-text">درصد</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="tour-product-max-discount-amount">
                                                    <label class="form-label" for="max_discount_amount">حداکثر مبلغ تخفیف</label>
                                                    <div class="input-group">
                                                        <input aria-label="حداکثر مبلغ تخفیف" class="form-control"
                                                            id="max_discount_amount" name="max_discount_amount" type="text"
                                                            value="{{ number_format((float) ($product->max_discount_amount ?: 0)) }}" />
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="tour-product-tax">
                                                    <label class="form-label" for="tax">ارزش افزوده</label>
                                                    <div class="input-group">
                                                        <input aria-label="ارزش افزوده" class="form-control" id="tax"
                                                            name="tax" type="text" value="{{ $product->tax }}" />
                                                        <span class="input-group-text">درصد</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="tour-product-fee-masraf">
                                                    <label class="form-label" for="fee_masraf">قیمت مصرف‌کننده</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت مصرف کننده" class="form-control"
                                                            id="fee_masraf" name="fee_masraf" type="text"
                                                            value="{{ $product->fee_masraf }}" />
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>

                                            <div class="product-edit-panel" id="tour-product-cost-prices">
                                                <h6 class="product-edit-panel__title">قیمت‌های خرید و عمده</h6>
                                                <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="purchase_price">قیمت خرید</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت خرید" class="form-control"
                                                            id="purchase_price" name="purchase_price" type="text"
                                                            value="{{ number_format((float) ($product->purchase_price ?: 0)) }}" />
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label" for="cost_price">قیمت تمام‌شده</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت تمام شده" class="form-control"
                                                            id="cost_price" name="cost_price" type="text"
                                                            value="{{ number_format((float) ($product->cost_price ?: 0)) }}" />
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                                @if ($featureAgencySales)
                                                <div class="col-md-6">
                                                    <label class="form-label" for="representative_price">قیمت نماینده</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت نماینده" class="form-control"
                                                            id="representative_price" name="representative_price"
                                                            type="text"
                                                            value="{{ number_format((float) ($product->representative_price ?: 0)) }}" />
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="col-md-6">
                                                    <label class="form-label" for="wholesale_price">قیمت عمده</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت عمده" class="form-control"
                                                            id="wholesale_price" name="wholesale_price" type="text"
                                                            value="{{ number_format((float) ($product->wholesale_price ?: 0)) }}" />
                                                        <span class="input-group-text">ریال</span>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>

                                            <div class="product-edit-panel">
                                                <h6 class="product-edit-panel__title">بازهٔ اعتبار قیمت</h6>
                                                <div class="row g-3" id="tour-product-price-dates">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="price_date">قیمت از تاریخ</label>
                                                    <input class="form-control" id="price_date" name="price_date"
                                                        placeholder="۱۴۰۴/۰۱/۰۱"
                                                        value="{{ isset($PriceLogLast) ? $PriceLogLast->price_from_fa : '' }}"
                                                        type="text" data-jdp />
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label" for="price_date_exp">قیمت تا تاریخ</label>
                                                    <input class="form-control" id="price_date_exp" name="price_date_exp"
                                                        placeholder="۱۴۰۴/۱۲/۲۹"
                                                        value="{{ isset($PriceLogLast) ? $PriceLogLast->price_exp_fa : '' }}"
                                                        type="text" data-jdp />
                                                </div>
                                                </div>
                                            </div>
                                            <div class="product-edit-panel">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="product-edit-panel__title mb-0">بازه های زمانی قیمت</h6>
                                                    <button type="button" class="btn btn-sm btn-label-primary" id="add-price-range-row">افزودن بازه</button>
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
                                                            @php
                                                                $existingRanges = old('price_ranges');
                                                                if (!is_array($existingRanges)) {
                                                                    $existingRanges = ($pricePeriods ?? collect())->map(fn($period) => [
                                                                        'price_type' => $period->price_type,
                                                                        'amount' => $period->amount,
                                                                        'starts_at' => $period->starts_at_fa,
                                                                        'ends_at' => $period->ends_at_fa,
                                                                        'priority' => $period->priority,
                                                                    ])->values()->all();
                                                                }
                                                                if ($existingRanges === []) {
                                                                    $existingRanges[] = ['price_type' => 'sale', 'amount' => $product->price, 'starts_at' => isset($PriceLogLast) ? $PriceLogLast->price_from_fa : '', 'ends_at' => isset($PriceLogLast) ? $PriceLogLast->price_exp_fa : '', 'priority' => 0];
                                                                }
                                                            @endphp
                                                            @foreach ($existingRanges as $index => $range)
                                                                <tr>
                                                                    <td>
                                                                        <select class="form-select" name="price_ranges[{{ $index }}][price_type]">
                                                                            @foreach ($pricePeriodTypes as $typeKey => $typeLabel)
                                                                                <option value="{{ $typeKey }}" @selected(($range['price_type'] ?? 'sale') === $typeKey)>{{ $typeLabel }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td><input type="text" class="form-control" name="price_ranges[{{ $index }}][amount]" value="{{ $range['amount'] ?? '' }}"></td>
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
                                    'submitLabel' => 'به‌روزرسانی محصول',
                                    'submitIcon' => 'ti-device-floppy',
                                    'cancelUrl' => route('products.index'),
                                    'hintText' => 'پس از هر تغییر، ذخیره را بزنید.',
                                    'id' => 'tour-product-save-bottom',
                                ])
                            </form>
                        </div>

                        <div class="card product-edit-meta-card mb-4" id="tour-product-inventory">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><x-ui.icon name="packages" class="me-1" />موجودی محصول</h5>
                            </div>
                            <div class="card-datatable table-responsive py-0">
                                        @if (count($Depots) > 0)
                                            <table class="datatables-direct-basic table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px">شماره</th>
                                                        <th>نوع</th>
                                                        <th>انبار</th>
                                                        <th>مقدار وارده اصلی</th>
                                                        <th>مقدار وارده فرعی</th>
                                                        <th>وضعیت</th>
                                                        <th>تاریخ ثبت بار</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($Depots as $depot)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $depot->receipt ? $depot->receipt->type : '' }}</td>
                                                            <td>{{ $depot->store ? $depot->store->title : '' }}</td>
                                                            <td>{{ number_format($depot->entity) }}</td>
                                                            <td>{{ number_format($depot->entity_sub_unit) }}</td>
                                                            <td>
                                                                @if ($depot->status == 0)
                                                                    <span
                                                                        class="badge rounded-pill bg-danger text-white">غیر
                                                                        فعال</span>
                                                                @elseif($depot->status == 1)
                                                                    <span
                                                                        class="badge rounded-pill bg-success text-white">
                                                                        فعال</span>
                                                                @endif

                                                            </td>
                                                            <td>{{ Verta($depot->created_at)->format('Y-m-d') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="text-muted text-center py-4 mb-0">هیچ باری برای این محصول ثبت نشده است.</p>
                                        @endif
                            </div>
                        </div>

                        <div class="card product-edit-meta-card mb-4" id="tour-product-price-log">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h5 class="card-title mb-0"><x-ui.icon name="history" class="me-1" />تاریخچه و بازه‌های قیمت</h5>
                                @if (($pricePeriods ?? collect())->isNotEmpty())
                                    <span class="badge rounded-pill bg-label-primary">{{ $pricePeriods->count() }} بازه ثبت‌شده</span>
                                @endif
                            </div>
                            <div class="card-datatable table-responsive py-0">
                                @if (($pricePeriods ?? collect())->isNotEmpty())
                                    <table class="table table-hover align-middle mb-0 product-price-periods-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 48px">ردیف</th>
                                                <th>نوع قیمت</th>
                                                <th>مبلغ</th>
                                                <th>از تاریخ</th>
                                                <th>تا تاریخ</th>
                                                <th style="width: 72px">اولویت</th>
                                                <th style="width: 88px">وضعیت</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pricePeriods as $index => $period)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <span @class([
                                                            'badge rounded-pill',
                                                            'bg-label-warning' => $period->price_type === 'prepayment',
                                                            'bg-label-success' => $period->price_type === 'completion',
                                                            'bg-label-primary' => $period->price_type === 'sale',
                                                            'bg-label-secondary' => ! in_array($period->price_type, ['prepayment', 'completion', 'sale'], true),
                                                        ])>{{ $pricePeriodTypes[$period->price_type] ?? $period->price_type }}</span>
                                                    </td>
                                                    <td class="fw-medium">{{ number_format((float) $period->amount) }}</td>
                                                    <td>{{ $period->starts_at_fa ?: '—' }}</td>
                                                    <td>{{ $period->ends_at_fa ?: '—' }}</td>
                                                    <td>{{ $period->priority }}</td>
                                                    <td>
                                                        @if ($period->status)
                                                            <span class="badge rounded-pill bg-success">فعال</span>
                                                        @else
                                                            <span class="badge rounded-pill bg-secondary">غیرفعال</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <p class="text-muted small px-4 py-3 mb-0 border-top">
                                        برای ویرایش بازه‌ها از بخش «بازه‌های زمانی قیمت» در فرم بالا استفاده کنید.
                                    </p>
                                @endif

                                @if (count($PriceLogs) > 0)
                                    @if (($pricePeriods ?? collect())->isNotEmpty())
                                        <div class="px-4 pt-3 pb-1">
                                            <h6 class="mb-0 text-muted">سوابق تغییر دستی قیمت</h6>
                                        </div>
                                    @endif
                                            <table class="datatables-direct-basic table {{ ($pricePeriods ?? collect())->isNotEmpty() ? 'border-top' : '' }}">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px">ردیف</th>
                                                        <th>کاربر ویرایش کننده</th>
                                                        <th>قیمت</th>
                                                        <th>حد تخفیف</th>
                                                        <th>ارزش افزوده</th>
                                                        <th>خرید</th>
                                                        <th>تمام شده</th>
                                                        @if ($featureAgencySales)
                                                        <th>نماینده</th>
                                                        @endif
                                                        <th>عمده</th>
                                                        <th>قیمت مصرف کننده</th>
                                                        <th>تاریخ قیمت گذاری</th>
                                                        <th>تا تاریخ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($PriceLogs as $plog)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $plog->user->name }}</td>
                                                            <td>{{ $plog->price ? number_format($plog->price) : '' }}
                                                            </td>
                                                            <td>{{ $plog->discount ? $plog->discount : '' }}%</td>
                                                            <td>{{ $plog->tax ? $plog->tax : '' }}%</td>
                                                            <td>{{ $plog->purchase_price > 0 ? number_format((float) $plog->purchase_price) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->cost_price > 0 ? number_format((float) $plog->cost_price) : '-' }}
                                                            </td>
                                                            @if ($featureAgencySales)
                                                            <td>{{ $plog->representative_price > 0 ? number_format((float) $plog->representative_price) : '-' }}
                                                            </td>
                                                            @endif
                                                            <td>{{ $plog->wholesale_price > 0 ? number_format((float) $plog->wholesale_price) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->fee_masraf > 0 ? number_format($plog->fee_masraf) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->price_from_fa }}</td>
                                                            <td>{{ $plog->price_exp_fa }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                @endif

                                @if (($pricePeriods ?? collect())->isEmpty() && count($PriceLogs) === 0)
                                            <p class="text-muted text-center py-4 mb-0">هیچ بازه یا گزارش قیمتی برای این محصول ثبت نشده است.</p>
                                @endif
                            </div>
                        </div>
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

        $('.weight_selector li').click(function() {
            var selected_weight = $(this).html();
            $('.selected_weight').html(selected_weight);
            $('#pr_weight_txt').val(selected_weight);
        });

        $('.pack_weight_selector li').click(function() {
            var selected_weight = $(this).html();
            $('.selected_pack_weight').html(selected_weight);
            $('#pack_weight_txt').val(selected_weight);
        });

        document.querySelectorAll('.product-toggle-input').forEach(function(input) {
            var card = input.closest('.product-toggle-card');
            var sync = function() {
                card.classList.toggle('is-on', input.checked);
            };
            input.addEventListener('change', sync);
            sync();
        });

        var imgInp = document.getElementById('imgInp');
        var imgPreview = document.getElementById('product-image-preview');
        if (imgInp && imgPreview) {
            imgInp.addEventListener('change', function(e) {
                var file = e.target.files && e.target.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function(ev) {
                    imgPreview.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        document.getElementById('price').addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^0-9.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            const numberValue = parseFloat(value);
            if (!isNaN(numberValue)) {
                e.target.value = numberValue.toLocaleString('en-US');
            } else {
                e.target.value = '';
            }
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
                    <td><input type="text" class="form-control" name="price_ranges[${priceRangeIndex}][amount]" value=""></td>
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
</body>

</html>

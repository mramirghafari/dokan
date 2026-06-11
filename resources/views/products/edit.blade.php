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
<?php $Organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
@php
    $featureBranchManagement = \App\Services\TenantSettings::enabled('feature_branch_management');
    $featureWarehouseManagement = \App\Services\TenantSettings::enabled('feature_warehouse_management');
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
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="row justify-content-between py-3 mb-2">
                            <div class="col-9">
                                <a href="{{ route('products.index') }}" class="text-muted fw-light">محصولات /</a>
                                ویرایش محصول
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

                        @php
                            use Carbon\Carbon;
                        @endphp
                        @if ($PriceLogLast)
                            @php
                                $targetDate = Carbon::parse($PriceLogLast->price_exp_en); // تاریخ مورد نظر;
                                $now = \Carbon\Carbon::now();
                            @endphp
                            @if ($targetDate->isFuture() && $now->diffInDays($targetDate) <= 7)
                                <p class="alert alert-warning">کمتر از یک هفته تا تاریخ قیمت گذاری این محصول باقی مانده
                                    است</p>
                            @endif
                        @endif

                        <!-- Sticky Actions -->
                        <div class="row mt-3">
                            <form action="{{ route('products.update', $product->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                @include('errors.errors')
                                <div class="col-12 text-end py-4">
                                    <button class="btn btn-primary me-sm-3 me-1" type="submit">به روزرسانی
                                        محصول</button>
                                </div>
                                <div class="nav-align-top nav-tabs-shadow mb-4">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <button aria-controls="navs-top-home" aria-selected="true"
                                                class="nav-link active" data-bs-target="#navs-top-home"
                                                data-bs-toggle="tab" role="tab" type="button">مشخصات اصلی
                                                محصول</button>
                                        </li>
                                        <li class="nav-item">
                                            <button aria-controls="navs-top-profile" aria-selected="false"
                                                class="nav-link" data-bs-target="#navs-top-profile" data-bs-toggle="tab"
                                                role="tab"
                                                type="button">{{ $featureWarehouseManagement ? 'مشخصات انبار' : 'اطلاعات تکمیلی محصول' }}</button>
                                        </li>
                                        <li class="nav-item">
                                            <button aria-controls="navs-top-messages" aria-selected="false"
                                                class="nav-link" data-bs-target="#navs-top-messages"
                                                data-bs-toggle="tab" role="tab" type="button">ویژگی ها و اطلاعات
                                                اضافی</button>
                                        </li>
                                        <li class="nav-item">
                                            <button aria-controls="navs-top-messages" aria-selected="false"
                                                class="nav-link" data-bs-target="#pricesinfo" data-bs-toggle="tab"
                                                role="tab" type="button">اعلامیه قیمت</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="navs-top-home" role="tabpanel">
                                            <div class="row g-3 p-3">
                                                <div class="col-md-4">
                                                    <label for="parentCategoryId">انتخاب دسته بندی</label>
                                                    <select class="select2 form-select" name="parentCategory_id"
                                                        id="parentCategoryId" style="width: 100%;">
                                                        <option value="">--هیچکدام--</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}"
                                                                {{ $category->id == $product->parentCategory_id ? 'selected' : '' }}>
                                                                {{ $category->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="title">عنوان محصول</label>
                                                    <input type="text" class="form-control" name="title"
                                                        id="title" value="{{ $product->title }}"
                                                        placeholder="نام محصول" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="display_name">نام نمایشی محصول</label>
                                                    <input type="text" class="form-control" name="display_name"
                                                        id="display_name" value="{{ $product->display_name }}"
                                                        placeholder="نام محصول" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="pr_sku">کد محصول:</label>
                                                    <input type="text" class="form-control" name="sku"
                                                        id="pr_sku" value="{{ $product->sku }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="product_type">نوع کالا</label>
                                                    <select class="form-select" id="product_type"
                                                        name="product_type">
                                                        @foreach ($productTypes as $value => $label)
                                                            <option value="{{ $value }}"
                                                                @if (old('product_type', $product->product_type ?: 'goods') === $value) selected @endif>
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
                                                                @if (old('stock_tracking_mode', $product->stock_tracking_mode ?: 'tracked') === $value) selected @endif>
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
                                                                @if (old('valuation_method', $product->valuation_method ?: 'weighted_average') === $value) selected @endif>
                                                                {{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="pr_unit">واحد اصلی محصول:</label>
                                                    <select class="select2 form-select" data-allow-clear="true"
                                                        id="pr_unit" name="base_unit_id">
                                                        <option value="">انتخاب کنید</option>
                                                        @foreach ($units as $unit)
                                                            <option value="{{ $unit->id }}"
                                                                @if (old('base_unit_id', $product->base_unit_id) == $unit->id) selected @endif>
                                                                {{ $unit->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="pr_sub_unit">واحد فرعی محصول:</label>
                                                    <select class="select2 form-select" data-allow-clear="true"
                                                        id="pr_sub_unit" name="secondary_unit_id">
                                                        <option value="">انتخاب کنید</option>
                                                        @foreach ($units as $unit)
                                                            <option value="{{ $unit->id }}"
                                                                @if (old('secondary_unit_id', $product->secondary_unit_id) == $unit->id) selected @endif>
                                                                {{ $unit->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="pack_items">تعداد واحد اصلی در واحد فرعی شامل <input
                                                            class="form-control" id="pack_items"
                                                            name="unit_conversion_factor"
                                                            value="{{ $product->unit_conversion_factor ?: $product->pack_items }}"
                                                            type="text" style="width: 60px;display: inline" />
                                                        <span class="unit_text">محصول</span> در
                                                        <span class="sub_unit_text">واحد فرعی</span> </label>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-top-profile" role="tabpanel">
                                            <div class="row">
                                                @if ($featureBranchManagement)
                                                    <div class="col-md-4">
                                                        <label for="organization_id">واحد های پخش مرتبط: </label>
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
                                                    <div class="col-md-4 ">
                                                        <label for="store_id">انبارهای مرتبط:</label>
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
                                                <div class="col-md-4">
                                                    <label for="brand_id">برند</label>
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
                                                <div class="col-md-6 my-3">
                                                    <label for="production_date">تاریخ تولید</label>
                                                    <input class="form-control" id="production_date"
                                                        name="production_date" placeholder="" type="text" />
                                                </div>
                                                <div class="col-md-6 my-3">
                                                    <label for="expiry_date">تاریخ انقضا</label>
                                                    <input class="form-control" id="expiry_date" name="expiry_date"
                                                        placeholder="" type="text" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-top-messages" role="tabpanel">
                                            <div class="row mt-4">
                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="item_sale_status" id="checkbox-p-1"
                                                        {{ $product->item_sale_status ? 'checked' : '' }}>
                                                    <label for="checkbox-p-1" class="cr text-white">قابلیت فروش
                                                        تکی</label>
                                                </div>

                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="pack_sale_status"
                                                        id="pack_sale_status"
                                                        {{ $product->pack_sale_status ? 'checked' : '' }}>
                                                    <label for="pack_sale_status" class="cr text-white">قابلیت فروش
                                                        عمده</label>
                                                </div>

                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="isFreez" id="isFreez"
                                                        {{ $product->isFreez ? 'checked' : '' }}>
                                                    <label for="isFreez" class="cr text-white">محصول یخچالی
                                                        میباشد.</label>
                                                </div>

                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="set_price" id="set_price"
                                                        {{ $product->set_price ? 'checked' : '' }}>
                                                    <label for="set_price" class="cr text-white">قیمت گذاری حین ثبت
                                                        سفارش</label>
                                                </div>

                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="isActive" id="isActive"
                                                        {{ $product->isActive ? 'checked' : '' }}>
                                                    <label for="isActive" class="cr text-white">فعال</label>
                                                </div>

                                                <div
                                                    class="form-group col-12 bg-secondary p-3 mb-3 rounded checkbox checkbox-primary">
                                                    <input type="checkbox" name="isMaterial" id="isMaterial"
                                                        {{ $product->isMaterial ? 'checked' : '' }}>
                                                    <label for="isMaterial" class="cr text-white">در صورتی که محصول
                                                        برای فروش نیست و مواد اولیه میباشد فعال کنید</label>
                                                </div>

                                            </div>

                                            <div class="row my-4">
                                                <div class="form-group col-12">
                                                    <label>تصویر محصول:
                                                        <input accept="image/*" type="file" name="photo"
                                                            id="imgInp" style="display: none">
                                                        <img src="{{ $product->photo == null ? asset('/img/core-img/placeholder-image.png') : asset("/storage/uploads/$product->photo") }}"
                                                            class="img-fluid avatar rounded "
                                                            style="width: 300px;height: 300px; border: 1px solid #0a53be" />
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="pr_weight">وزن اصلی محصول <small
                                                            class="text-muted">تنظیمات باربری</small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="وزن اصلی محصول" class="form-control"
                                                            id="pr_weight" name="pr_weight" type="text"
                                                            value="{{ $product->pr_weight }}" />
                                                        <button aria-expanded="false"
                                                            class="btn btn-outline-primary dropdown-toggle selected_weight"
                                                            data-bs-toggle="dropdown" type="button">
                                                            @if ($product->pr_unit != null)
                                                                {{ $product->pr_unit }}
                                                            @else
                                                                واحد وزن
                                                            @endif
                                                        </button>
                                                        <input type="hidden" name="pr_weight_txt"
                                                            id="pr_weight_txt">
                                                        <ul class="dropdown-menu weight_selector dropdown-menu-end">
                                                            <li class="dropdown-item">انتخاب کنید</li>
                                                            <li class="dropdown-item">گرم</li>
                                                            <li class="dropdown-item">کیلوگرم</li>
                                                            <li class="dropdown-item">مثقال</li>
                                                            <li class="dropdown-item">سی سی</li>
                                                            <li class="dropdown-item">میلی لیتر</li>
                                                            <li class="dropdown-item">لیتر</li>
                                                            <li class="dropdown-item">تن</li>
                                                            <li class="dropdown-item">متر مکعب</li>
                                                            <li class="dropdown-item">گالن</li>
                                                            <li class="dropdown-item">فله</li>
                                                            <li class="dropdown-item">متر</li>
                                                            <li class="dropdown-item">سانتی متر</li>
                                                            <li class="dropdown-item">میلی متر</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="pack_weight">وزن فرعی محصول<small
                                                            class="text-muted">تنظیمات باربری</small>: </label>
                                                    <div class="input-group">
                                                        <input aria-label="وزن اصلی محصول" class="form-control"
                                                            id="pack_weight" name="pack_weight"
                                                            value="{{ $product->pack_weight }}" type="text" />
                                                        <button aria-expanded="false"
                                                            class="btn btn-outline-primary dropdown-toggle selected_pack_weight"
                                                            data-bs-toggle="dropdown" type="button">
                                                            @if ($product->pr_sub_unit != null)
                                                                {{ $product->pr_sub_unit }}
                                                            @else
                                                                واحد وزن
                                                            @endif
                                                        </button>
                                                        <input type="hidden" name="pack_weight_txt"
                                                            id="pack_weight_txt">
                                                        <ul
                                                            class="dropdown-menu pack_weight_selector dropdown-menu-end">
                                                            <li class="dropdown-item">انتخاب کنید</li>
                                                            <li class="dropdown-item">گرم</li>
                                                            <li class="dropdown-item">کیلوگرم</li>
                                                            <li class="dropdown-item">مثقال</li>
                                                            <li class="dropdown-item">سی سی</li>
                                                            <li class="dropdown-item">میلی لیتر</li>
                                                            <li class="dropdown-item">لیتر</li>
                                                            <li class="dropdown-item">تن</li>
                                                            <li class="dropdown-item">متر مکعب</li>
                                                            <li class="dropdown-item">گالن</li>
                                                            <li class="dropdown-item">فله</li>
                                                            <li class="dropdown-item">متر</li>
                                                            <li class="dropdown-item">سانتی متر</li>
                                                            <li class="dropdown-item">میلی متر</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="pricesinfo" role="tabpanel">

                                            <div class="row">

                                                <div class="col-md-6 mb-3">
                                                    <label for="price">قیمت محصول <small
                                                            class="text-muted"></small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت محصول" class="form-control"
                                                            id="price" name="price" type="text"
                                                            value="{{ number_format(intval($product->price)) }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">
                                                            ریال
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="discount">حد تخفیف <small
                                                            class="text-muted"></small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="حد تخفیف" class="form-control"
                                                            id="discount" name="discount" type="text"
                                                            value="{{ $product->discount }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">درصد</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="tax">ارزش افزوده<small
                                                            class="text-muted"></small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت محصول" class="form-control"
                                                            id="tax" name="tax" type="text"
                                                            value="{{ $product->tax }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">درصد</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="purchase_price">قیمت خرید:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت خرید" class="form-control"
                                                            id="purchase_price" name="purchase_price" type="text"
                                                            value="{{ number_format((float) ($product->purchase_price ?: 0)) }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="cost_price">قیمت تمام شده:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت تمام شده" class="form-control"
                                                            id="cost_price" name="cost_price" type="text"
                                                            value="{{ number_format((float) ($product->cost_price ?: 0)) }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="representative_price">قیمت نماینده:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت نماینده" class="form-control"
                                                            id="representative_price" name="representative_price"
                                                            type="text"
                                                            value="{{ number_format((float) ($product->representative_price ?: 0)) }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="wholesale_price">قیمت عمده:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت عمده" class="form-control"
                                                            id="wholesale_price" name="wholesale_price"
                                                            type="text"
                                                            value="{{ number_format((float) ($product->wholesale_price ?: 0)) }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="fee_masraf">قیمت مصرف کننده: <small
                                                            class="text-muted"></small>:</label>
                                                    <div class="input-group">
                                                        <input aria-label="قیمت مضرف کننده" class="form-control"
                                                            id="fee_masraf" name="fee_masraf" type="text"
                                                            value="{{ $product->fee_masraf }}" />
                                                        <button aria-expanded="false" class="btn btn-outline-primary"
                                                            type="button">ریال</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 my-3">
                                                    <label for="price_date">درج قیمت از تاریخ</label>
                                                    <input class="form-control" id="price_date" name="price_date"
                                                        placeholder=""
                                                        value="{{ isset($PriceLogLast) ? $PriceLogLast->price_from_fa : '' }}"
                                                        type="text" data-jdp />
                                                </div>
                                                <div class="col-md-6 my-3">
                                                    <label for="price_date_exp">درج قیمت تا تاریخ</label>
                                                    <input class="form-control" id="price_date_exp"
                                                        name="price_date_exp" placeholder=""
                                                        value="{{ isset($PriceLogLast) ? $PriceLogLast->price_exp_fa : '' }}"
                                                        type="text" data-jdp />
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card">
                                    <div
                                        class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                                        <h5 class="card-title mb-sm-0 me-2">موجودی محصول</h5>
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
                                                    @php($x = 1)
                                                    @foreach ($Depots as $depot)
                                                        <tr>
                                                            <td>{{ $x }}</td>
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
                                                        @php($x++)
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="text-muted text-center">هیچ باری برای این محصول ثبت نشده است.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row mt-3">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card">
                                    <div
                                        class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                                        <h5 class="card-title mb-sm-0 me-2">تغییرات قیمت</h5>
                                    </div>
                                    <div class="card-datatable table-responsive py-0">
                                        @if (count($PriceLogs) > 0)
                                            <table class="datatables-direct-basic table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px">ردیف</th>
                                                        <th>کاربر ویرایش کننده</th>
                                                        <th>قیمت</th>
                                                        <th>حد تخفیف</th>
                                                        <th>ارزش افزوده</th>
                                                        <th>خرید</th>
                                                        <th>تمام شده</th>
                                                        <th>نماینده</th>
                                                        <th>عمده</th>
                                                        <th>قیمت مصرف کننده</th>
                                                        <th>تاریخ قیمت گذاری</th>
                                                        <th>تا تاریخ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php($x = 1)
                                                    @foreach ($PriceLogs as $plog)
                                                        <tr>
                                                            <td>{{ $x }}</td>
                                                            <td>{{ $plog->user->name }}</td>
                                                            <td>{{ $plog->price ? number_format($plog->price) : '' }}
                                                            </td>
                                                            <td>{{ $plog->discount ? $plog->discount : '' }}%</td>
                                                            <td>{{ $plog->tax ? $plog->tax : '' }}%</td>
                                                            <td>{{ $plog->purchase_price > 0 ? number_format((float) $plog->purchase_price) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->cost_price > 0 ? number_format((float) $plog->cost_price) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->representative_price > 0 ? number_format((float) $plog->representative_price) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->wholesale_price > 0 ? number_format((float) $plog->wholesale_price) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->fee_masraf > 0 ? number_format($plog->fee_masraf) : '-' }}
                                                            </td>
                                                            <td>{{ $plog->price_from_fa }}</td>
                                                            <td>{{ $plog->price_exp_fa }}</td>
                                                        </tr>
                                                        @php($x++)
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else>
                                            <p class="text-muted text-center">هیچ گزارش قیمتی برای این محصول ثبت نشده
                                                است.</p>
                                        @endif
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
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>



    <script>
        jalaliDatepicker.startWatch();
        // datatable (jquery)




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

        document.getElementById('price').addEventListener('input', function(e) {
            let value = e.target.value;

            // Remove all non-digit characters except for a single decimal point
            value = value.replace(/[^0-9.]/g, '');

            // Handle multiple decimal points
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            // Convert to a number and format
            const numberValue = parseFloat(value);
            if (!isNaN(numberValue)) {
                e.target.value = numberValue.toLocaleString('en-US'); // Format for US locale
            } else {
                e.target.value = ''; // Clear if not a valid number
            }
        });
    </script>
</body>

</html>

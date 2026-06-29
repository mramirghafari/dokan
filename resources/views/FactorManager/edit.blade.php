<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش فاکتورهای سامانه - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
</head>

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
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">اطلاعات پایه /</span>
                        ویرایش فاکتور های سامانه
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form class="row" method="POST" action="{{ route('FactorManager.update',$factorMaker->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        @include('errors.errors')

                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="factor_title">تیتر بالای فاکتور</label>
                                            <input class="form-control" id="factor_title" placeholder="نام فاکتور" name="name" type="text" value="{{ $factorMaker->name }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="type">انتخاب نوع فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="type" name="type">
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->type == 1) selected @endif>فاکتور رسمی</option>
                                                <option value="2"  @if($factorMaker->type == 2) selected @endif>فاکتور غیر رسمی</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="pr_type">نوع محصولات فاکتور</label>
                                            @php($currentProductType = app(\App\Services\InvoiceLayoutService::class)->normalizeProductType($factorMaker->pr_type === null ? null : (string) $factorMaker->pr_type))
                                            <select class="select2 form-select" data-allow-clear="true" id="pr_type" name="pr_type" data-profile-link="business_profile">
                                                @foreach ($productTypes as $type)
                                                    <option value="{{ $type['key'] }}" data-profile="{{ $type['profile'] }}"
                                                        @if($currentProductType === $type['key']) selected @endif>{{ $type['label'] }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted d-block mt-1" id="pr_type_help">
                                                {{ collect($productTypes)->firstWhere('key', $currentProductType)['description'] ?? '' }}
                                            </small>
                                        </div>
                                        @include('FactorManager.partials.business_profile_fields')
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="currency_type">واحد پولی فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="currency_type" name="currency_type">
                                                <option value="">انتخاب کنید</option>
                                                <option value="2" @if($factorMaker->currency_type == 2 || !$factorMaker->currency_type) selected @endif>ریال</option>
                                                <option value="1" @if($factorMaker->currency_type == 1) selected @endif>تومان</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="name-seller">نام فروشنده فاکتور:</label>
                                            <input class="form-control" id="name-seller" placeholder="نام فروشنده فاکتور" name="seller_name" type="text" value="{{ $factorMaker->seller_name }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="economynumber-seller">شماره اقتصادی فروشنده فاکتور:</label>
                                            <input class="form-control" id="economynumber-seller" placeholder="شماره اقتصادی فروشنده فاکتور" name="seller_economic_number" type="text" value="{{ $factorMaker->seller_economic_number }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="nationalid-seller">شماره ثبت / شماره ملی فروشنده فاکتور:</label>
                                            <input class="form-control" id="nationalid-seller" placeholder="شماره ثبت / شماره ملی فروشنده فاکتور" name="seller_registration_number" type="text" value="{{ $factorMaker->seller_registration_number }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="shenasemeli-seller">شناسه ملی فروشنده فاکتور:</label>
                                            <input class="form-control" id="shenasemeli-seller" placeholder="شناسه ملی فروشنده فاکتور" name="seller_id_number" type="text" value="{{ $factorMaker->seller_id_number }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6">
                                            <label class="form-label" for="address-seller">آدرس فروشنده فاکتور:</label>
                                            <input class="form-control" id="address-seller" placeholder="آدرس فروشنده فاکتور" name="seller_address" type="text" value="{{ $factorMaker->seller_address }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-4">
                                            <label class="form-label" for="zipcode-seller">کدپستی فروشنده فاکتور:</label>
                                            <input class="form-control" id="zipcode-seller" placeholder="کدپستی فروشنده فاکتور" name="seller_zip_code" type="text" value="{{ $factorMaker->seller_zip_code }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-4">
                                            <label class="form-label" for="phone-seller">شماه تلفن فاکتور:</label>
                                            <input class="form-control" id="phone-seller" placeholder="شماره تلفن فاکتور" name="seller_phone" type="text" value="{{ $factorMaker->seller_phone }}"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-4">
                                            <label class="form-label" for="fax-seller">شماه فکس فروشنده:</label>
                                            <input class="form-control" id="fax-seller" placeholder="شماره فکس فاکتور" name="seller_fax" type="text" value="{{ $factorMaker->seller_fax }}"/>
                                        </div>
                                        <hr class="my-2" />
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="buyer_name">وضعیت نمایش نام خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="buyer_name" name="buyer_name" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_name == 1) selected @endif>نمایش نام کامل خریدار</option>
                                                <option value="2" @if($factorMaker->buyer_name == 2) selected @endif>نمایش تابلو خریدار</option>
                                                <option value="3" @if($factorMaker->buyer_name == 3) selected @endif>نمایش تابلو خریدار + نام کامل خریدار</option>
                                                <option value="4" @if($factorMaker->buyer_name == 4) selected @endif> تابلو خریدار + نام کامل خریدار + منطقه + مسیر + آدرس</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="sh_kh">نمایش شناسه اقتصادی خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="sh_kh" name="buyer_econimic_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_econimic_code == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->buyer_econimic_code == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="sh_ss">نمایش شماره ثبت خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="sh_ss" name="buyer_registration_number" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_registration_number == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->buyer_registration_number == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_address">نمایش آدرس خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_address" name="buyer_address" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_address == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->buyer_address == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_zip_code">نمایش کدپستی خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_zip_code" name="buyer_zip_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_zip_code == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->buyer_zip_code == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_phone">نمایش تلفن خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_phone" name="buyer_phone" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_phone == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->buyer_phone == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_region_area">نمایش منطقه مسیر خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_region_area" name="buyer_region_area" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_region_area == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->buyer_region_area == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="mapcode_status">نمایش مپ کد در فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="mapcode_status" name="buyer_map_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->buyer_map_code == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->buyer_map_code == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="visitor_display">نمایش بازاریاب مربوطه</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="visitor_display" name="visitor_display" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->visitor_display == 1) selected @endif>نمایش نام بازاریاب</option>
                                                <option value="2" @if($factorMaker->visitor_display == 2) selected @endif>نمایش کد بازاریاب</option>
                                                <option value="3" @if($factorMaker->visitor_display == 3) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="visitor_mobile">نمایش تلفن بازاریاب مربوطه</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="visitor_mobile" name="visitor_mobile" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->visitor_mobile == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->visitor_mobile == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <hr class="my-2" />
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_pr_sku">نمایش ستون کد محصولات</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_pr_sku" name="column_pr_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->column_pr_code == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->column_pr_code == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_moadian">نمایش ستون شناسه مودیان محصولات</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_moadian" name="column_moadian" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->column_moadian == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->column_moadian == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_sub_unit_kol">نمایش ستون واحد فرعی و کل</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_sub_unit_kol" name="column_sub_unit" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->column_sub_unit == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->column_sub_unit == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_discount">نمایش ستون درصد و مبلغ تخفیف</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_discount" name="column_discount" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->column_discount == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->column_discount == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_tax">نمایش ستون مالیات</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_tax" name="column_tax">
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($factorMaker->column_tax == 1) selected @endif>نمایش</option>
                                                <option value="2" @if($factorMaker->column_tax == 2) selected @endif>عدم نمایش</option>
                                            </select>
                                        </div>
                                        <hr class="my-2" />
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="organization_id">واحد پخش مربوطه</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="organization_id" name="organization_id[]" >
                                                <option value="">انتخاب کنید</option>
                                                @foreach($Organizations as $organ)
                                                    <option value="{{ $organ->id }}" @if(in_array($organ->id,json_decode($factorMaker->organization_id))) selected @endif>{{ $organ->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="organization_id">انبار مربوطه این فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="store_id" name="store_id[]" >
                                                <option value="">انتخاب کنید</option>
                                                @foreach($Stores as $store)
                                                    <option value="{{ $store->id }}" @if(is_array(json_decode($factorMaker->store_id)) && in_array($store->id,json_decode($factorMaker->store_id))) selected @endif>{{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ویرایش فاکتور</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            @if($factorMaker->type == 1)
                            <div class="card mb-3">
                                <div class="card-datatable table-responsive p-3">
                                    <h5 class="text-center">{{ $factorMaker->name }}</h5>
                                    <table class="factor_table nowrap w-100">
                                        <thead>
                                        <thead>
                                        <tr class="x_border">
                                            <th class="text-center" colspan="14" style="text-align: center;padding: 2px !important;">مشخصات فروشنده</th>
                                        </tr>
                                        <tr class="x_border">
                                            <td colspan="5" class="border-0"><span style="font-size: 13px">نام شخص حقیقی/حقوقی:</span> <strong>{{ $factorMaker->seller_name }}</strong></td>
                                            <td colspan="5" class="border-0">شناسه اقتصادی/کدملی: <strong>{{ $factorMaker->seller_economic_number }}</strong></td>
                                            <td colspan="4" class="border-0">شماره ثبت: <strong>{{ $factorMaker->seller_registration_number }}</strong></td>
                                        </tr>
                                        <tr class="no_border x_border">
                                            <td colspan="5">نشانی کامل: <strong>{{ $factorMaker->seller_address }}</strong></td>
                                            <td colspan="5">شماره تلفن: <strong style="display: inline-block;direction: ltr !important;">{{ $factorMaker->seller_phone }}</strong></td>
                                            <td colspan="4">کدپستی 10 رقمی: <strong>{{ $factorMaker->seller_zip_code }}</strong></td>
                                        </tr>
                                        <tr class="x_border">
                                            <th class="text-center" colspan="14" style="text-align: center;padding: 2px !important;">مشخصات خریدار</th>
                                        </tr>
                                        <tr class="x_border sp">
                                            <td colspan="5" class="border-0">نام خریدار:
                                                <strong>
                                                    @if($factorMaker->buyer_name == 1)
                                                            نام خریدار
                                                    @elseif($factorMaker->buyer_name == 2)
                                                        تابلو فروشگاه خریدار
                                                    @elseif($factorMaker->buyer_name == 3)
                                                        نمایش تابلو خریدار + نام کامل خریدار
                                                    @elseif($factorMaker->buyer_name == 4)
                                                        تابلو خریدار + نام کامل خریدار + منطقه + مسیر + آدرس
                                                    @endif
                                                </strong>
                                            </td>
                                            <td colspan="5" class="border-0">شناسه اقتصادی/کدملی: <strong>{{ $factorMaker->buyer_econimic_code }}</strong></td>
                                            <td colspan="4" class="border-0">شماره ثبت/شماره ملی: {{ $factorMaker->buyer_registration_number }}</strong></td>
                                        </tr>
                                        <tr class="no_border x_border">
                                            <td colspan="5">نشانی کامل: <strong>{{ $factorMaker->buyer_address }}</strong></td>
                                            <td colspan="5">کدپستی 10 رقمی: <strong>{{ $factorMaker->buyer_zip_code }}</strong></td>
                                            <td colspan="4">شماره تلفن: <strong>{{ $factorMaker->buyer_phone }}</strong></td>
                                        </tr>
                                        <tr class="no_border x_border">
                                            @if($factorMaker->buyer_region_area == 1)
                                            <td colspan="3">منطقه/مسیر: <strong> - </strong></td>
                                            @endif
                                            @if($factorMaker->buyer_map_code == 1)
                                            <td colspan="2">مپ کد: <strong> - </strong></td>
                                            @endif
                                            @if($factorMaker->visitor_display != 3)
                                            <td colspan="5">نام بازاریاب:
                                                <strong>
                                                    @if($factorMaker->visitor_display == 1) نام بازاریاب @else کد بازاریاب @endif
                                                </strong></td>
                                            @endif
                                            @if($factorMaker->visitor_mobile == 1)
                                            <td colspan="4">شماره همراه بازاریاب: <strong> - </strong></td>
                                            @endif

                                        </tr>
                                        @include('FactorManager.partials.layout_preview', ['factorMaker' => $factorMaker])
                                        <tfoot>
                                        <tr>
                                            <th colspan="@if($factorMaker->column_sub_unit == 1) 4 @else 3 @endif">جمع کل</th>
                                            @if($factorMaker->column_pr_code == 1)
                                            <th class="text-center">---</th>
                                            @endif
                                            @if($factorMaker->column_moadian == 1)
                                                <th class="text-center">---</th>
                                            @endif
                                            @if($factorMaker->column_sub_unit == 1)
                                                <th class="text-center">---</th>
                                                <th class="text-center">---</th>
                                            @endif
                                            @if($factorMaker->column_discount == 1)
                                            <th class="text-center">---</th>
                                            <th></th>
                                            <th class="all_discounts text-center"></th>
                                            <th class="all_pats text-center"></th>
                                            @endif
                                            @if($factorMaker->column_tax == 1)
                                            <th class="all_taxs text-center"></th>
                                            @endif
                                            <th class="full_prices text-center">
                                                <span></span>
                                                <input type="hidden" name="fullPrice" value="" />
                                            </th>
                                        </tr>
                                        <tr>
                                            <th colspan="5">شرایط و نحوه فروش: <span> <label class="active" >نقدی</label> <label class="">چکی</label></span></th>
                                            <th class="horof" colspan="9"></th>
                                        </tr>
                                        <tr>
                                            <th colspan="14">توضیحات: --- </th>
                                        </tr>

                                        <tr>
                                            <th colspan="14">این اقلام توسط فروشگاه --- تحویل گرفته شد و تا زمان وصول کامل مبلغ فاکتور، نزد مشتری گرامی به صورت امانی میباشد.</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" style="text-align: right !important; height: 100px !important;">مهر و امضای فروشنده </th>
                                            <th colspan="5" style="text-align: right !important; height: 100px !important;">مهر و امضای مسئول پخش </th>
                                            <th colspan="5" style="text-align: right !important; height: 100px !important;">مهر و امضای خریدار </th>
                                        </tr>
                                        </tfoot>

                                    </table>
                                </div>
                            </div>
                            @endif

                            @if($factorMaker->type == 2)
                            <div class="card mb-3">
                                <div class="card-datatable table-responsive p-3">
                                    <table class="factor_table nowrap w-50 mx-auto">
                                        <thead>
                                        <thead>
                                        <tr class="x_border">
                                            <th class="text-center" colspan="14" style="text-align: center;padding: 2px !important;">حواله خروج بار از ---</th>
                                        </tr>
                                        <tr class="x_border">
                                            <td colspan="3" style="min-height: 60px">خریدار: ---</td>
                                            <td colspan="2">تاریخ: <strong>---</strong></td>
                                        </tr>
                                        <tr class="x_border text-center">
                                            <th width="30">ردیف</th>
                                            <th>عنوان محصول</th>
                                            <th>وزن خالص</th>
                                            <th>فی ({{ currency_label() }})</th>
                                            <th>جمع</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                            <tr class="x_border text-center">
                                                <td>1</td>
                                                <td>---</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                        <tr class="x_border">
                                            <td colspan="2" style="min-height: 60px">امضاء انبار: </td>
                                            <td style="border-width: 0;" class="no_border">امضاء پخش: </td>
                                            <td colspan="2"></td>
                                        </tr>
                                        </tfoot>

                                    </table>
                                </div>
                            </div>
                            @endif
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
<style>
    .factor_table th {
        position: relative;
    }
    .factor_table th .editbox {
        position: absolute;
        background-color: #30a9ff;
        border-radius: 10px;
        padding: 3px;
        color: #fff;
        top: 3px;
        left: 5px;
        font-size: 10px;
    }
    .factor_table th .editbox .box-content {
        display: none;
    }
    input.width.form-control {
        padding: 3px;
        font-size: 13px;
        text-align: center;
    }
</style>
<script>
    $('.basicdata').addClass('open')
    $('.basicdata .factormaker').addClass('active open')
    // datatable (jquery)
    $(function () {
        var
            dt_without_ajax_table = $('.datatables-direct-basic');

        // DataTable Direct
        // --------------------------------------------------------------------
        if(dt_without_ajax_table.length){
            dt_without_ajax = dt_without_ajax_table.DataTable({
                searching: false,
                lengthChange: false,
                ordering: false,
                pageLength: 5,
            });

            $('.datatables-direct-basic tbody').on( 'click', '.dropdown-item.delete-record', function () {
                dt_without_ajax
                    .row( $(this).parents('tr') )
                    .remove()
                    .draw();
            } );
        }


        $('.factor_table thead th').each(function(index, element) {
            $(this).css('position','relative');
            $(this).append('<div class="editbox"><span class="icon">edit</span><div class="box-content"><input type="text" class="width form-control" /></div></div>')
        });

        $('.editbox .icon').click(function() {
            $(this).siblings('.box-content').fadeToggle();
        });


        $('.editbox .width').on('keyup',function() {
            var arz = $(this).val();
            $(this).parents('th').attr('style',"width: "+arz+"px !important;min-width: "+arz+"px !important;max-width: "+arz+"px !important");
        });


    });

    @php $savedLayoutLabels = $factorMaker->line_layout['labels'] ?? []; @endphp
    @include('FactorManager.partials.layout_preview_script')
</script>
</body>

</html>

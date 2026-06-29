<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>تنظیمات فاکتورهای سامانه - دکان دارمینو</title>
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
                        تنظیمات فاکتور های سامانه
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form class="row" method="POST" action="{{ route('FactorManager.store') }}">
                                        @csrf
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="factor_title">تیتر بالای فاکتور</label>
                                            <input class="form-control" id="factor_title" placeholder="نام فاکتور" name="name" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="type">انتخاب نوع فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="type" name="type">
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">فاکتور رسمی</option>
                                                <option value="2">فاکتور غیر رسمی</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="pr_type">نوع محصولات فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="pr_type" name="pr_type" data-profile-link="business_profile">
                                                @foreach ($productTypes as $type)
                                                    <option value="{{ $type['key'] }}" data-profile="{{ $type['profile'] }}"
                                                        @if(($factorMaker->pr_type ?? config('factor_product_types.default')) === $type['key']) selected @endif>{{ $type['label'] }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted d-block mt-1" id="pr_type_help">
                                                {{ collect($productTypes)->firstWhere('key', $factorMaker->pr_type ?? config('factor_product_types.default'))['description'] ?? '' }}
                                            </small>
                                        </div>
                                        @include('FactorManager.partials.business_profile_fields')
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="currency_type">واحد پولی فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="currency_type" name="currency_type">
                                                <option value="">انتخاب کنید</option>
                                                <option value="2" selected>ریال</option>
                                                <option value="1">تومان</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="name-seller">نام فروشنده فاکتور:</label>
                                            <input class="form-control" id="name-seller" placeholder="نام فروشنده فاکتور" name="seller_name" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="economynumber-seller">شماره اقتصادی فروشنده فاکتور:</label>
                                            <input class="form-control" id="economynumber-seller" placeholder="شماره اقتصادی فروشنده فاکتور" name="seller_economic_number" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="nationalid-seller">شماره ثبت / شماره ملی فروشنده فاکتور:</label>
                                            <input class="form-control" id="nationalid-seller" placeholder="شماره ثبت / شماره ملی فروشنده فاکتور" name="seller_registration_number" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="shenasemeli-seller">شناسه ملی فروشنده فاکتور:</label>
                                            <input class="form-control" id="shenasemeli-seller" placeholder="شناسه ملی فروشنده فاکتور" name="seller_id_number" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6">
                                            <label class="form-label" for="address-seller">آدرس فروشنده فاکتور:</label>
                                            <input class="form-control" id="address-seller" placeholder="آدرس فروشنده فاکتور" name="seller_address" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-4">
                                            <label class="form-label" for="zipcode-seller">کدپستی فروشنده فاکتور:</label>
                                            <input class="form-control" id="zipcode-seller" placeholder="کدپستی فروشنده فاکتور" name="seller_zip_code" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-4">
                                            <label class="form-label" for="phone-seller">شماه تلفن فاکتور:</label>
                                            <input class="form-control" id="phone-seller" placeholder="شماره تلفن فاکتور" name="seller_phone" type="text"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-4">
                                            <label class="form-label" for="fax-seller">شماه فکس فروشنده:</label>
                                            <input class="form-control" id="fax-seller" placeholder="شماره فکس فاکتور" name="seller_fax" type="text"/>
                                        </div>
                                        <hr class="my-2" />
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="buyer_name">وضعیت نمایش نام خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="buyer_name" name="buyer_name">
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش نام کامل خریدار</option>
                                                <option value="2">نمایش تابلو خریدار</option>
                                                <option value="3">نمایش تابلو خریدار + نام کامل خریدار</option>
                                                <option value="4"> تابلو خریدار + نام کامل خریدار + منطقه + مسیر + آدرس</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="sh_kh">نمایش شناسه اقتصادی خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="sh_kh" name="buyer_econimic_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="sh_ss">نمایش شماره ثبت خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="sh_ss" name="buyer_registration_number" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_address">نمایش آدرس خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_address" name="buyer_address" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_zip_code">نمایش کدپستی خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_zip_code" name="buyer_zip_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_phone">نمایش تلفن خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_phone" name="buyer_phone" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="kh_region_area">نمایش منطقه مسیر خریدار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="kh_region_area" name="buyer_region_area" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش نام بازاریاب</option>
                                                <option value="2">نمایش کد بازاریاب</option>
                                                <option value="3">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="mapcode_status">نمایش مپ کد در فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="mapcode_status" name="buyer_map_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="visitor_display">نمایش بازاریاب مربوطه</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="visitor_display" name="visitor_display" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش نام بازاریاب</option>
                                                <option value="2">نمایش کد بازاریاب</option>
                                                <option value="3">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="visitor_mobile">نمایش تلفن بازاریاب مربوطه</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="visitor_mobile" name="visitor_mobile" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <hr class="my-2" />
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_pr_sku">نمایش ستون کد محصولات</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_pr_sku" name="column_pr_code" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_moadian">نمایش ستون شناسه مودیان محصولات</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_moadian" name="column_moadian" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_sub_unit_kol">نمایش ستون واحد فرعی و کل</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_sub_unit_kol" name="column_sub_unit" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_discount">نمایش ستون درصد و مبلغ تخفیف</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_discount" name="column_discount" >
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="column_tax">نمایش ستون مالیات</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="column_tax" name="column_tax">
                                                <option value="">انتخاب کنید</option>
                                                <option value="1">نمایش</option>
                                                <option value="2">عدم نمایش</option>
                                            </select>
                                        </div>
                                        <hr class="my-2" />
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="organization_id">واحد پخش مربوطه</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="organization_id" name="organization_id[]" >
                                                <option value="">انتخاب کنید</option>
                                                @foreach($Organizations as $organ)
                                                    <option value="{{ $organ->id }}">{{ $organ->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="organization_id">انبار مربوطه این فاکتور</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="store_id" name="store_id[]" >
                                                <option value="">انتخاب کنید</option>
                                                @foreach($Stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ایجاد فاکتور</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-5">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان فاکتور</th>
                                            <th>نوع فاکتور</th>
                                            <th>نوع محصولات</th>
                                            <th>پروفایل خط فاکتور</th>
                                            <th>شعبه</th>
                                            <th>انبار</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach ($Factors as $factor)
                                            <tr>
                                                <td>{{ $x }}</td>
                                                <td><a href="{{ route('FactorManager.edit', $factor->id) }}">{{ $factor->name }}</a></td>
                                                <td>
                                                    @if($factor->type == 1) <strong>رسمی</strong> @else <strong>غیر رسمی</strong> @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $factor->productTypeLabel() }}</strong>
                                                </td>
                                                <td><strong>{{ $factor->businessProfileLabel() }}</strong></td>
                                                <td>
                                                    @if($factor->organization_id != null && is_array(json_decode($factor->organization_id)))
                                                        @php($Oranizations = DB::table('organizations')->wherein('id', json_decode($factor->organization_id))->get())
                                                        @foreach($Oranizations as $organ)
                                                        {{ $organ->title }},
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($factor->store_id != null && is_array(json_decode($factor->store_id)))
                                                            @php($Stores = DB::table('stores')->wherein('id', json_decode($factor->store_id))->get())
                                                            @foreach($Stores as $store)
                                                                {{ $store->title }},
                                                            @endforeach
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('FactorManager.edit', $factor->id) }}"
                                                       style="font-size:20px;float: right;margin-left:5px"><x-ui.icon name="fa-edit" /></a>
                                                    {{-- <form action="{{ route('stores.destroy', $region->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('آیا از حذف رکورد مورد نظر اطمینان دارید؟');">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit"
                                                            style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                            <x-ui.icon name="fa-trash" />
                                                        </button>
                                                    </form> --}}
                                                </td>
                                            </tr>
                                            @php($x++)
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-3">
                                <div class="card-datatable table-responsive p-3">
                                    <table class="factor_table nowrap w-100">
                                        <thead>
                                        <thead>
                                        <tr class="x_border">
                                            <th class="text-center" colspan="14" style="text-align: center;padding: 2px !important;">مشخصات فروشنده</th>
                                        </tr>
                                        <tr class="x_border">
                                            <td colspan="5" class="border-0"><span style="font-size: 13px">نام شخص حقیقی/حقوقی:</span> <strong>---</strong></td>
                                            <td colspan="5" class="border-0">شناسه اقتصادی/کدملی: <strong>---</strong></td>
                                            <td colspan="4" class="border-0">شماره ثبت: <strong>---</strong></td>
                                        </tr>
                                        <tr class="no_border x_border">
                                            <td colspan="5">نشانی کامل: <strong>تهران</strong> <strong>---</strong></td>
                                            <td colspan="5">شماره تلفن: <strong style="display: inline-block;direction: ltr !important;">---</strong></td>
                                            <td colspan="4">کدپستی 10 رقمی: <strong> </strong></td>
                                        </tr>
                                        <tr class="x_border">
                                            <th class="text-center" colspan="14" style="text-align: center;padding: 2px !important;">مشخصات خریدار</th>
                                        </tr>
                                        <tr class="x_border sp">
                                            <td colspan="5" class="border-0">نام خریدار: <strong> - </strong></td>
                                            <td colspan="5" class="border-0">شناسه اقتصادی/کدملی: <strong> - </strong></td>
                                            <td colspan="4" class="border-0">شماره ثبت/شماره ملی: <strong></strong>--</td>
                                        </tr>
                                        <tr class="no_border x_border">
                                            <td colspan="5">نشانی کامل: <strong>تهران</strong> <strong> - </strong></td>
                                            <td colspan="5">کدپستی 10 رقمی: <strong></strong></td>
                                            <td colspan="4">شماره تلفن: <strong> - </strong></td>
                                        </tr>
                                        <tr class="no_border x_border">
                                            <td colspan="3">منطقه/مسیر: <strong> - </strong></td>
                                            <td colspan="2">مپ کد: <strong> - </strong></td>
                                            <td colspan="5">نام ویزیتور: <strong> - </strong></td>
                                            <td colspan="4">شماره تماس: <strong> - </strong></td>

                                        </tr>
                                        @include('FactorManager.partials.layout_preview', ['factorMaker' => $factorMaker])
                                        <tfoot>
                                        <tr>
                                            <th colspan="4">جمع کل</th>
                                            <th class="text-center">---</th>
                                            <th class="text-center">---</th>
                                            <th class="text-center">---</th>
                                            <th class="text-center">---</th>
                                            <th class="text-center">---</th>
                                            <th></th>
                                            <th class="all_discounts text-center"></th>
                                            <th class="all_pats text-center"></th>
                                            <th class="all_taxs text-center"></th>
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


    });

    @php($savedLayoutLabels = [])
    @include('FactorManager.partials.layout_preview_script')
</script>
</body>

</html>

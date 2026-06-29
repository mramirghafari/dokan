<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش شعبه - دکان دارمینو</title>
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
                        ویرایش مشخصات شعبه
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form action="{{ route('organizations.update',$organization->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="mb-3">
                                            <label class="form-label" for="organ_name">نام شعبه</label>
                                            <input class="form-control" name="title" id="organ_name" placeholder="نام شعبه" value="{{ $organization->title }}" type="text"/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="tenants_id">انتخاب پنل</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="tenants_id" name="tenants_id">
                                                <option value="">انتخاب کنید</option>
                                                @foreach($Tenants as $tenant)
                                                <option value="{{ $tenant->id }}" @if($organization->tenants_id == $tenant->id) selected @endif>{{ $tenant->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="exampleInputEmail111">انتخاب نوع سفارش گیری:</label>
                                            <select class="form-control" name="type">
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($organization->type == 1) selected @endif>ثبت سفارش بر اساس موجودی</option>
                                                <option value="2" @if($organization->type == 2) selected @endif>پیش سفارش (موجودی بر اساس سفارشات)</option>
                                            </select>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col mb-3">
                                                <label for="exampleInputEmail111">واحد پولی شعبه:</label>
                                                <select class="form-control" name="currency_type">
                                                    <option value="0">-- انتخاب کنید --</option>
                                                    <option value="2" @if($organization->currency_type == 2 || !$organization->currency_type) selected @endif>ریال</option>
                                                    <option value="1" @if($organization->currency_type == 1) selected @endif>تومان</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <label for="pr_unit">واحد اصلی محصولات:</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="pr_unit" name="unit_order">
                                                <option value="">انتخاب کنید</option>
                                                <option @if($organization->unit_order == 'عدد') selected @endif>عدد</option>
                                                <option @if($organization->unit_order == 'بسته') selected @endif>بسته</option>
                                                <option @if($organization->unit_order == 'کیلوگرم') selected @endif>کیلوگرم</option>
                                                <option @if($organization->unit_order == 'گرم') selected @endif>گرم</option>
                                                <option @if($organization->unit_order == 'مثقال') selected @endif>مثقال</option>
                                                <option @if($organization->unit_order == 'سی سی') selected @endif>سی سی</option>
                                                <option @if($organization->unit_order == 'میلی لیتر') selected @endif>میلی لیتر</option>
                                                <option @if($organization->unit_order == 'لیتر') selected @endif>لیتر</option>
                                                <option @if($organization->unit_order == 'تن') selected @endif>تن</option>
                                                <option @if($organization->unit_order == 'متر مکعب') selected @endif>متر مکعب</option>
                                                <option @if($organization->unit_order == 'گالن') selected @endif>گالن</option>
                                                <option @if($organization->unit_order == 'فله') selected @endif>فله</option>
                                                <option @if($organization->unit_order == 'متر') selected @endif>متر</option>
                                                <option @if($organization->unit_order == 'سانتی متر') selected @endif>سانتی متر</option>
                                                <option @if($organization->unit_order == 'میلی متر') selected @endif>میلی متر</option>
                                            </select>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <label for="pr_sub_unit">واحد فرعی محصولات:</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="pr_sub_unit" name="sub_unit">
                                                <option value="">انتخاب کنید</option>
                                                <option @if($organization->sub_unit == 'بسته') selected @endif>بسته</option>
                                                <option @if($organization->sub_unit == 'کارتن') selected @endif>کارتن</option>
                                                <option @if($organization->sub_unit == 'جعبه') selected @endif>جعبه</option>
                                                <option @if($organization->sub_unit == 'باکس') selected @endif>باکس</option>
                                                <option @if($organization->sub_unit == 'سبد') selected @endif>سبد</option>
                                                <option @if($organization->sub_unit == 'نایلون') selected @endif>نایلون</option>
                                                <option @if($organization->sub_unit == 'گونی') selected @endif>گونی</option>
                                                <option @if($organization->sub_unit == 'فله') selected @endif>فله</option>
                                                <option @if($organization->sub_unit == 'کیلوگرم') selected @endif>کیلوگرم</option>
                                                <option @if($organization->sub_unit == 'لیتر') selected @endif>لیتر</option>
                                                <option @if($organization->sub_unit == 'تن') selected @endif>تن</option>
                                            </select>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <label for="unit_display">نمایش واحد در سرجدول ها:</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="unit_display" name="unit_display">
                                                <option value="">انتخاب کنید</option>
                                                <option value="1" @if($organization->unit_display == 1) selected @endif>واحد اصلی</option>
                                                <option value="2" @if($organization->unit_display == 2) selected @endif>واحد فرعی</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description">توضیح:</label>
                                            <input type="text" class="form-control" name="description" id="description" value="{{ $organization->description }}">
                                        </div>
                                        <div class="mb-3">
                                            <input type="checkbox" name="isActive" id="isActive"
                                                {{ $organization->isActive ? 'checked' : '' }}>
                                            <label for="isActive" class="cr">فعال</label>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ویرایش شعبه</button>
                                    </form>
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
    // datatable (jquery)
    $('.basicdata').addClass('open')
    $('.basicdata .organizations').addClass('active open')
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

</script>
</body>

</html>

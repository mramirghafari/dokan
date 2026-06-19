<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>قیمت روز محصولات - دکان دارمینو</title>
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
                        <span class="text-muted fw-light">محصولات /</span>
                        قیمت روز محصولات
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card">
                                <h5 class="card-header">قیمت روز محصولات</h5>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('products.update_product_fees') }}">
                                        @csrf
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr class="text-center">
                                                <th width="30">ردیف</th>
                                                <th>نام محصول</th>
                                                <th>آخرین قیمت (ریال)</th>
                                                <th>قیمت جدید (ریال)</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php($x = 1)
                                            @foreach($Products as $pr)
                                            <tr>
                                                <td>{{ $x }}</td>
                                                <td>{{ $pr->title }} @if($pr->display_name != null) {{ $pr->display_name }} @endif</td>
                                                <td class="text-center"> <span class="badge bg-label-primary me-1">{{ intval($pr->price) > 0 ? number_format(intval($pr->price)) : '0' }} ریال</span></td>
                                                <td>
                                                    <input type="text" id="pr_price_{{ $pr->id }}" name="pr_price_{{ $pr->id }}" class="form-control price" />
                                                </td>
                                            </tr>
                                            @php($x++)
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <div class="pt-4">
                                            <button class="btn btn-success me-sm-3 me-1" type="submit">به روزرسانی قیمت ها</button>
                                        </div>
                                    </div>
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
<
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>

<script>
    $('.products').addClass('open')
    $('.products .fees').addClass('active open')
</script>
<script>
    document.querySelectorAll('.price').forEach(input => {
        input.addEventListener('input', e => {
            // حذف غیر عدد و نگه داشتن متن فعلی
            let pos = e.target.selectionStart;
            let raw = e.target.value.replace(/[^\d]/g, '');

            // فرمت سه‌رقم سه‌رقم
            let withCommas = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            e.target.value = withCommas;

            // برگردوندن مکان کرسر تا وسط تایپ نپره
            let diff = withCommas.length - raw.length;
            e.target.selectionEnd = pos + diff;
        });
    });
</script>
</body>

</html>

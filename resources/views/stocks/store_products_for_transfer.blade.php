<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>لیست محصولات انبار {{ $store->title }} - دکان دارمینو</title>
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
                        لیست محصولات انبار
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th width="40">ردیف</th>
                                            <th>کد محصول</th>
                                            <th>عنوان محصول</th>
                                            <th>اول دوره</th>
                                            <th>اول دوره واحد فرعی</th>
                                            <th>ورودی</th>
                                            <th>ورودی واحد فرعی</th>
                                            <th>خروجی</th>
                                            <th>خروجی واحد فرعی</th>
                                            <th>موجودی</th>
                                            <th>موجودی واحد فرعی</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach($Products as $product)
                                        <tr>
                                            <td>{{ $x }}</td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">{{ $product->sku }}</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">{{ $product->title }} {{ $product->display_name }}</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">123</a> </td>
                                            <td><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">

                                                    <svg width="20" height="19" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M9.8618 2.3125L10.9415 1.32222C11.1665 1.11591 11.4718 1 11.7901 1C12.1084 1 12.4136 1.11591 12.6387 1.32222C12.8638 1.52854 12.9902 1.80836 12.9902 2.10013C12.9902 2.3919 12.8638 2.67173 12.6387 2.87804L5.84265 9.10777C5.5043 9.41774 5.08705 9.64557 4.62859 9.7707L2.91021 10.24L3.4222 8.66484C3.5587 8.24458 3.80724 7.8621 4.14539 7.55195L9.8618 2.3125ZM9.8618 2.3125L11.5501 3.86011M10.5901 7.89339V10.68C10.5901 11.0301 10.4384 11.3658 10.1683 11.6134C9.8983 11.8609 9.53203 12 9.15013 12H2.43022C2.04831 12 1.68204 11.8609 1.41199 11.6134C1.14195 11.3658 0.990234 11.0301 0.990234 10.68V4.5201C0.990234 4.17002 1.14195 3.83427 1.41199 3.58673C1.68204 3.33919 2.04831 3.20012 2.43022 3.20012H5.47018" stroke="#F9BA16" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>

                                                </a> </td>

                                        </tr>
                                            @php($x++)
                                        @endforeach
                                        </tbody>
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
    $('.stocks').addClass('open')
    $('.stocks .export').addClass('active open')
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
                pageLength: 100,
            });

            $('.datatables-direct-basic tbody').on( 'click', '.dropdown-item.delete-record', function () {
                dt_without_ajax
                    .row( $(this).parents('tr') )
                    .remove()
                    .draw();
            } );
        }


    });

    $(document).ready(function() {
        $('#pr_id').on('change',function() {
            var dataUnit = $(this).find('option:selected').attr('data-unit');
            $('.unitplace').html(dataUnit);
            var dataSubUnit = $(this).find('option:selected').attr('data-subunit');
            $('.subunitplace').html(dataSubUnit);
        });
    });

</script>
</body>

</html>

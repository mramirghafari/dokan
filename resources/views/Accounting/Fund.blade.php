<?php

use Hekmatinasser\Verta\Verta; ?>
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پیشخوان دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/swiper/swiper.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css" />
    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/pages/cards-advance.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        @media(max-width: 768px) {
            .svgicon {
                width: 60%;
                margin-left: 8px;
                height: auto;
                margin-bottom: 15px;
            }

            .dataTables_filter .form-control {
                width: 190px !important;
            }
        }

        .datatables-direct-basic thead th {
            text-align: center !important;
        }

        .datatables-direct-basic td:nth-child(1),
        .datatables-direct-basic th:nth-child(1) {
            width: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            white-space: nowrap;
            text-align: center;
        }
    </style>
</head>

<body>
    @include('partials.panel-toasts')
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <!-- Layout container -->
            <div class="layout-page">
                @include('sections/header')
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 id="tour-fund-page-header" class="py-3 mb-4">
                            <span class="text-muted fw-light">مالی و حسابداری /</span>
                            صندوق
                        </h4>
                        <div id="tour-fund-actions" class="row">
                            @if (\App\Services\TenantSettings::enabled('feature_route_management'))
                                <div class="mb-4 col-sm-6 col-12">
                                    <a class="btn btn-primary waves-effect waves-light w-100"
                                        href="{{ route('tasks.create') }}"><x-ui.icon name="plus" class="me-md-2" /> ثبت مسیر
                                        جدید</a>
                                </div>
                            @endif
                            <div class="mb-4 col-sm-6 col-12">
                                <a class="btn btn-primary waves-effect waves-light w-100"
                                    href="{{ route('products.index') }}"><x-ui.icon name="plus" class="me-md-2" /> ثبت سفارش
                                    جدید</a>
                            </div>
                        </div>

                        <div id="tour-fund-table" class="row mb-3">
                            <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card">
                                    <div class="card-datatable table-responsive py-0">
                                        <style>
                                            .table tr th,
                                            .table tr td {
                                                padding: 7px !important;
                                            }

                                            .dataTables_filter {
                                                width: 365px
                                            }
                                        </style>
                                        <table class="datatables-direct-basic tablelist table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th width="20">#</th>
                                                    <th>عنوان</th>
                                                    <th>گردش بدهکار</th>
                                                    <th>گردش بستانکار</th>
                                                    <th>مانده بدهکار</th>
                                                    <th>مانده بستانکار</th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: #fff">
                                                <tr>
                                                    <th>1</th>
                                                    <th>موجودی نقدی</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                                <tr>
                                                    <th>2</th>
                                                    <th>موجودی کالاها</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                                <tr>
                                                    <th>2</th>
                                                    <th>پیش دریافت ها</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                                <tr>
                                                    <th>2</th>
                                                    <th>هزینه های عمومی</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>

                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->
                    @include('sections/footer')
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
    <!-- Vendors JS -->
    <script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/swiper/swiper.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <!-- اکستنشن‌ها -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            jalaliDatepicker.startWatch();
        });
    </script>
    <script>
        // datatable (jquery)
        $(function() {
            var dt_without_ajax_table = $('.datatables-direct-basic');

            if (dt_without_ajax_table.length) {
                dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: true,
                    pageLength: 25,
                    autoWidth: false, // مهم: جلوگیری از inline-width های خودکار
                    columnDefs: [{
                            width: '40px',
                            targets: 0
                        }, // ستون اول
                        {
                            width: '70px',
                            targets: 0
                        }, // ستون اول
                        // مثال: { width: '100px', targets: 1 }, برای ستون دوم
                    ],
                    language: {
                        search: 'جستجو: ',
                        searchPlaceholder: 'جستجو کنید...',
                        info: 'نمایش صفحه _PAGE_ از _PAGES_',
                        infoEmpty: 'موردی وجود ندارد.',
                        infoFiltered: '(فیلتر شده از _MAX_ مورد)',
                        lengthMenu: 'نمایش _MENU_ مورد در صفحه',
                        zeroRecords: 'متاسفانه موردی پیدا نشد',
                        paginate: {
                            previous: 'قبلی',
                            next: 'بعدی',
                        }
                    }
                });
            }
        });
    </script>





</body>

</html>

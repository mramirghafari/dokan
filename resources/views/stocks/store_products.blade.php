<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>لیست محصولات انبار {{ $store->title }} - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/>
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet"/>
    <!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>


    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- FixedHeader CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.bootstrap5.min.css">
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.css">

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
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
                        لیست محصولات {{ $store->title }}
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table id="example" class="table">
                                        <thead>
                                        <tr>
                                            <th class="text-center" width="40">ردیف</th>
                                            <th class="text-center">کد محصول</th>
                                            <th>عنوان محصول</th>
                                            <th class="text-center">اول دوره</th>
                                            <th  class="text-center" style="font-size: 12px !important;">اول دوره واحد فرعی</th>
                                            <th class="text-center">ورودی</th>
                                            <th class="text-center">ورودی واحد فرعی</th>
                                            <th class="text-center">خروجی</th>
                                            <th class="text-center">خروجی واحد فرعی</th>
                                            <th class="text-center">موجودی</th>
                                            <th class="text-center" style="font-size: 12px !important;">موجودی واحد فرعی</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach($Products as $product)
                                        <tr>
                                            <td class="text-center">{{ $x }}</td>
                                            <td class="text-center"><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}"><small>{{ $product->sku }}</small></a> </td>
                                            <td class="bigcol">
                                                <a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}"><small>{{ $product->title }} {{ $product->display_name }}</small></a>
                                            </td>
                                            {{-- اول دوره --}}
                                            <td class="text-center"><small>  {{ $product->getFirstPeriodMain($store->id) }}</small></td>
                                            <td class="text-center"><small>  {{ $product->getFirstPeriodSub($store->id) }}</small></td>

                                            {{-- ورودی‌ها --}}
                                            <td class="text-center"><small>{{ $product->getInputMain($store->id) }}</small></td>
                                            <td class="text-center"><small>{{ $product->getInputSub($store->id) }}</small></td>

                                            {{-- خروجی‌ها --}}
                                            <td class="text-center"><small>{{ $product->getOutputMain() }}</small></td>
                                            <td class="text-center"><small>{{ $product->getOutputSub() }}</small></td>

                                            <td class="text-center"><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">

                                                    <span class="d-inline-block" style="direction: ltr"><small @if($product->currentStock() < 0) style="color: red;" @endif>{{ $product->currentStock() }}</small></span>

                                                </a> </td>
                                            <td class="text-center"><a href="{{ route('stocks.storeProductCardex',['store' => $store->id, 'product' => $product->id]) }}">

                                                    <small><span class="d-inline-block" style="direction: ltr">


                                                        </span></small>
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
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<!-- endbuild -->
<script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
<!-- Buttons Extension -->
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<!-- FixedHeader -->
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<style>
    .dt-scroll-body table thead {
        display: none !important;
    }
    #example_length {
        text-align: left;
        padding-left: 10px;
    }
    .dt-buttons {
        direction: rtl !important;
        padding: 10px;
        float: right;
    }
</style>
<script>
    $('.stocks').addClass('open')
    $('.stocks .vorodi').addClass('active open')
    // datatable (jquery)
    $(function () {

        let table = $('#example').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 120, // نمایش 50 ردیف به صورت پیش‌فرض

            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/fa.json'
            },
            dom: '<"row"<"col-md-6"l><"col-md-6 text-end"B>>rtip',
            buttons: [
                { extend: 'excel', text: 'خروجی اکسل' },
            ],
            columnDefs: [
                { targets: 0, width: '40px' },   // ستون اول
                { targets: 1, width: '80px' },   // ستون دوم
                { targets: 2, width: '200px' },  // ستون سوم
                { targets: 3, width: '60px' },  // ...
                { targets: 4, width: '70px' },  // ...
                { targets: 5, width: '60px' },  // ...
                { targets: 6, width: '70px' },  // ...
                { targets: 7, width: '70px' },  // ...
                { targets: 8, width: '70px' },  // ...
                { targets: 9, width: '70px' },  // ...
                { targets: 10, width: '70px' },  // ...
                // می‌تونی ادامه بدی برای همه ستون‌ها
            ]
        });


        var
            dt_without_ajax_table = $('.datatables-direct-basic');

        // DataTable Direct
        // --------------------------------------------------------------------
        /*if(dt_without_ajax_table.length){
            dt_without_ajax = dt_without_ajax_table.DataTable({
                searching: false,
                lengthChange: false,
                ordering: false,
                pageLength: 100,
                dom: '<"row"<"col-md-6"l><"col-md-6 text-end"B>>rtip',
                buttons: [
                    { extend: 'excel', text: 'اکسل' },
                    { extend: 'print', text: 'پرینت' },
                ],
                columnDefs: [
                    { targets: 0, width: '40px' },   // ستون اول
                    { targets: 1, width: '80px' },   // ستون دوم
                    { targets: 2, width: '200px' },  // ستون سوم
                    { targets: 3, width: '60px' },  // ...
                    { targets: 4, width: '70px' },  // ...
                    { targets: 5, width: '60px' },  // ...
                    { targets: 6, width: '70px' },  // ...
                    { targets: 7, width: '70px' },  // ...
                    { targets: 8, width: '70px' },  // ...
                    { targets: 9, width: '70px' },  // ...
                    { targets: 10, width: '70px' },  // ...
                    // می‌تونی ادامه بدی برای همه ستون‌ها
                ]
            });

            $('.datatables-direct-basic tbody').on( 'click', '.dropdown-item.delete-record', function () {
                dt_without_ajax
                    .row( $(this).parents('tr') )
                    .remove()
                    .draw();
            } );
        } */


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

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>لیست کاردکس محصول {{ $store->title }} - دکان دارمینو</title>
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
                        <span class="text-muted fw-light"> {{ $store->title }} /</span>
                        کاردکس محصول:
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card-header sticky-element d-flex justify-content-between align-items-sm-center flex-column flex-sm-row">
                                <h4 class="card-title">{{ $product->title }} {{ $product->display_name }}</h4>
                            </div>
                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th width="40">ردیف</th>
                                            <th>تاریخ</th>
                                            <th>شماره</th>
                                            <th>سند</th>
                                            <th>نوع</th>
                                            <th>مقدار وارده</th>
                                            <th>مقدار وارده واحد فرعی</th>
                                            <th>مقدار صادره</th>
                                            <th>مقدار صادره واحد فرعی</th>
                                            <th>مقدار مانده</th>
                                            <th>مقدار مانده واحد فرعی</th>
                                            <th>نیاز به تولید</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @php($Mande = 0)
                                        @php($MandeFaree = 0)
                                        @foreach($timeline as $item)
                                        <tr>
                                            <td>{{ $x }}</td>
                                            <td><small>{{ Verta($item['date'])->format('Y/m/d') }}</small></td>
                                            <td>
                                                @if($item['type'] == 'depot' && $item['receipt'] != null) {{ $item['receipt']->number }} @else - @endif
                                            </td>
                                            <td>
                                                <small>{{ $item['type'] === 'depot' ? 'رسید' : '' }}</small>
                                            </td>
                                            <td>
                                                <small>@if($item['type'] === 'sale')
                                                        خروج
                                                    @else
                                                        @if($item['receipt']->type == 1)
                                                            خرید (داخلی)
                                                        @elseif($item['receipt']->type == 2)
                                                            خرید (وارداتی)
                                                        @elseif($item['receipt']->type == 3)
                                                            تولید
                                                        @elseif($item['receipt']->type == 4)
                                                            سایر
                                                        @elseif($item['receipt']->type == 5)
                                                            موجودی اول دوره
                                                        @elseif($item['receipt']->type == 6)
                                                            انتقال انبار
                                                        @elseif($item['receipt']->type == 7)
                                                            فروش
                                                        @elseif($item['receipt']->type == 8)
                                                            مصرف
                                                        @elseif($item['receipt']->type == 9)
                                                            انتقال بین انبار (سایر)
                                                       @endif
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="text-center ">
                                                <small>{{ $item['type'] === 'depot' ? $item['tedad'] : 0 }}
                                                    @if($item['type'] == 'depot')
                                                        @php($Mande +=  $item['tedad'])
                                                    @endif</small>
                                            </td>
                                            <td class="text-center ">
                                                <small>{{ $item['type'] === 'depot' ? $item['tedad'] / $product->pack_items : 0 }}
                                                    @if($item['type'] == 'depot')
                                                        @php($MandeFaree +=  ($item['tedad'] / $product->pack_items) )
                                                    @endif</small>
                                            </td>
                                            <td class="text-center {{ $item['type'] === 'sale' ? 'text-danger' : '' }}">
                                                <small>{{ $item['type'] === 'sale' ? $item['tedad'] : 0 }}</small>

                                            </td>
                                            <td class="text-center {{ $item['type'] === 'sale' ? 'text-danger' : '' }}">
                                               <small> {{ $item['type'] === 'sale' ? $item['pack'] : 0 }}</small>

                                            </td>
                                            @if($item['type'] == 'sale')
                                                @php($Mande -=  $item['tedad'] + $item['pack'] * $product->pack_items)
                                            @endif
                                            @if($item['type'] == 'sale')
                                                @php($MandeFaree -=  $item['pack'] + ($item['tedad'] / $product->pack_items))
                                            @endif
                                            <td class="text-center @if($Mande < 0) text-danger @endif" style="direction: ltr"><small>{{ $Mande }}</small></td>
                                            <td class="text-center  @if($Mande < 0) text-danger @endif" style="direction: ltr"><small>{{ round($MandeFaree,3) }}</small></td>
                                            <td class="text-center @if($Mande < 0) text-danger @endif" style="direction: ltr">

                                            </td>
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
    $('.stocks .vorodi').addClass('active open')
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
                "info": false
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

    $('#entity').on('keyup',function() {
        var entity = $(this).val();
        var per_pack = {{ $product->pack_items }};
        var entity_sub_unit = entity / per_pack;
        $('#entity_sub_unit').val(entity_sub_unit);
    })

    $('#entity_sub_unit').on('keyup',function() {
        var entity_sub_unit = $(this).val();
        var per_pack = {{ $product->pack_items }};
        var entity = entity_sub_unit * per_pack;
        $('#entity').val(entity);
    })

</script>
</body>

</html>

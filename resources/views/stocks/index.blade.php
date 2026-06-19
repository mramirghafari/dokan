<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ثبت ورودی و  تولید - دکان دارمینو</title>
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
                        ثبت تولید و ورودی انبار
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form class="row" method="POST" action="{{ route('stocks.store') }}">
                                        @csrf
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="pr_id">انتخاب محصول</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="pr_id" name="pr_id">
                                                <option value="">انتخاب کنید</option>
                                                @foreach($Products as $product)
                                                <option value="{{ $product->id }}" data-unit="{{ $product->pr_unit }}" data-subunit="{{ $product->pr_sub_unit }}" >{{ $product->title }} {{ $product->display_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="store_id">انتخاب انبار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="store_id" name="store_id">
                                                <option value="">انتخاب کنید</option>
                                                @foreach($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="entity_unit">مقدار ورودی واحد اصلی (<span class="unitplace"></span>)</label>
                                            <input class="form-control" id="entity_unit" name="entity_unit" placeholder="" type="number"/>
                                        </div>
                                        <div class="mb-3 col-12 col-md-3">
                                            <label class="form-label" for="entity_sub_unit">مقدار ورودی واحد فرعی (<span class="subunitplace"></span>)</label>
                                            <input class="form-control" id="entity_sub_unit" name="entity_sub_unit" placeholder="مقدار ای تعدادش" type="number"/>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ثبت تولید محصول</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th width="40">ردیف</th>
                                            <th>محصول</th>
                                            <th>موجودی واحد اصلی</th>
                                            <th>موجودی واحد فرعی</th>
                                            <th>موجودی کل</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach($stocks as $stock)
                                        <tr>
                                            <td>{{ $x }}</td>
                                            <td>{{ $stock->product->title }}</td>
                                            <td><span class="badge  bg-label-success">{{ $stock->entity_unit }} {{ $stock->product->pr_unit }}</span></td>
                                            <td>
                                                <span class="badge  bg-label-success">{{ $stock->entity_sub_unit }} {{ $stock->product->pr_sub_unit }}</span>
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">{{ intval($stock->entity_sub_unit * $stock->product->pack_items) + $stock->entity_unit }} {{ $stock->product->pr_unit }}</span>
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

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>پنل های سامانه - دکان دارمینو</title>
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

<?php $Organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
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
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">تولید /</span>
                        کاردکس های محصولات / کاردکس های محصول {{ $Product->title }} {{ $Product->display_name }}
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                                    <h5 class="card-title mb-sm-0 me-2">کاردکس های محصول {{ $Product->title }} {{ $Product->display_name }}</h5>
                                    <div class="action-btns">
                                        <button class="btn btn-primary" data-bs-target="#modalTop" data-bs-toggle="modal" type="button">ثبت کاردکس جدید</button>
                                    </div>
                                    <div class="modal modal-top fade" id="modalTop" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form class="modal-content" action="{{ route('depot.store') }}" method="POST">
                                                @csrf
                                                @include('errors.errors')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalTopTitle">ثبت ورودی جدید برای محصول</h5>
                                                    <button aria-label="بستن" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                                                </div>
                                                <div class="modal-body">

                                                    <input type="hidden" name="pr_id" value="{{ $Product->id }}" />
                                                    <div class="row g-2">
                                                        <div class="col mb-3">
                                                            <label class="form-label" for="store_id">انتخاب انبار:</label>
                                                            <select class="select2 form-select" name="store_id"
                                                                    id="store_id">
                                                                <option value="0">انتخاب کنید</option>
                                                                @foreach ($Stores as $store)
                                                                    <option value="{{ $store->id }}" >
                                                                        {{ $store->title }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col mb-3">
                                                            <label class="form-label" for="brand_id">انتخاب برند:</label>
                                                            <select class="select2 form-select" name="brand_id"
                                                                    id="brand_id">
                                                                <option value="0">انتخاب کنید</option>
                                                                @foreach ($Brands as $brand)
                                                                    <option value="{{ $brand->id }}" >
                                                                        {{ $brand->title }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col mb-3">
                                                            <label class="form-label" for="entity">موجودی واحد اصلی:</label>
                                                            <input type="number" class="form-control" name="entity"
                                                                   id="entity">
                                                        </div>
                                                        <div class="col mb-3">
                                                            <label class="form-label" for="entity_sub_unit">موجودی واحد فرعی:</label>
                                                            <input type="number" class="form-control" name="entity_sub_unit"
                                                                   id="entity_sub_unit">
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col mb-3">
                                                            <label class="form-label" for="orderLimit">حداقل سفارش:</label>
                                                            <input type="number" class="form-control" name="orderLimit"
                                                                   id="orderLimit">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-primary" type="submit">ثبت کاردکس</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th style="width: 40px">شماره</th>
                                            <th>قیمت پایه</th>
                                            <th>حداقل مقدار سفارش</th>
                                            <th>موجودی</th>
                                            <th>تخفیف</th>
                                            <th>ارزش افزوده</th>
                                            <th>فی مصرف</th>
                                            <th>وضعیت</th>
                                            <th>تاریخ ثبت بار</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach($Depots as $depot)
                                            <tr>
                                                <td>{{ $x }}</td>
                                                <td>{{ number_format($depot->price) }} <small>@if($Organ->currency_type == 1) تومان @else ریال @endif</small></td>
                                                <td>{{ number_format($depot->orderLimit) }}</td>
                                                <td>{{ number_format($depot->entity) }}</td>
                                                <td>{{ number_format($depot->discount) }}</td>
                                                <td>{{ number_format($depot->tax) }}</td>
                                                <td>{{ number_format($depot->fee_masraf) }}</td>
                                                <td>
                                                    @if($depot->status == 0)
                                                        <span class="badge rounded-pill bg-danger text-white">غیر فعال</span>
                                                    @elseif($depot->status == 1)
                                                        <span class="badge rounded-pill bg-success text-white"> فعال</span>
                                                    @endif

                                                </td>
                                                <td>{{ $depot->created_at }}</td>
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
    $('.stocks').addClass('open')
    $('.stocks .cardex').addClass('active open')
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

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مشاهده جزئیات عملیات مسیر - دکان دارمینو</title>
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
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>

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
                    <h4 class="py-3 mb-2">
                        <span class="text-muted fw-light">لیست مسیرها /</span>
                        مشاهده جزئیات عملیات مسیر
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-3">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <form class="card-body" action="{{ route('tasks.update',$Task->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @include('errors.errors')
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="multicol-country">انتخاب منطقه</label>
                                            <select class="select2 form-select" name="region_id" id="region_id"
                                                    style="width: 100%;">
                                                <option>--انتخاب کنید--</option>
                                                @foreach ($Regions as $region)
                                                    <option value="{{ $region->id }}" {{ isset($Cur_Region->id) && $Cur_Region->id == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="multicol-country">انتخاب مسیر</label>
                                            <select class="select2 form-select" name="area_id" id="areas"
                                                    style="width: 100%;">
                                                <option>--انتخاب کنید--</option>
                                                @if(!is_null($Cur_areas))
                                                    @foreach($Cur_areas as $area)
                                                        <option value="{{ $area->id }}" {{ $Task->area_id == $area->id ? 'selected' : '' }} >{{ $area->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="channel">انتخاب کانال</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="channel" name="channel">
                                                <option value="">--همه موارد--</option>
                                                <option value="سوپر مارکت" {{ $Task->channel == "سوپر مارکت" ? 'selected' : '' }}> سوپر مارکت </option>
                                                <option value="یاران دریان" {{ $Task->channel == "یاران دریان" ? 'selected' : '' }}> یاران دریان </option>
                                                <option value="عمده فروش" {{ $Task->channel == "عمده فروش" ? 'selected' : '' }}> عمده فروش </option>
                                                <option value="خرده فروش" {{ $Task->channel == "خرده فروش" ? 'selected' : '' }}> خرده فروش </option>
                                                <option value="سوپر پروتئین" {{ $Task->channel == "سوپر پروتئین" ? 'selected' : '' }}> سوپر پروتئین </option>
                                                <option value="شوینده و بهداشتی" {{ $Task->channel == "شوینده و بهداشتی" ? 'selected' : '' }}> شوینده و بهداشتی </option>
                                                <option value="رستوران" {{ $Task->channel == "رستوران" ? 'selected' : '' }}> رستوران </option>
                                                <option value="فست فود" {{ $Task->channel == "فست فود" ? 'selected' : '' }}> فست فود </option>
                                                <option value="تره بار" {{ $Task->channel == "تره بار" ? 'selected' : '' }}>تره بار </option>
                                                <option value="هورکا" {{ $Task->channel == "هورکا" ? 'selected' : '' }}>هورکا </option>
                                                <option value="تعاونی" {{ $Task->channel == "تعاونی" ? 'selected' : '' }}>تعاونی </option>
                                                <option value="سایر" {{ $Task->channel == "سایر" ? 'selected' : '' }}> سایر </option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="senf">انتخاب صنف</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="senf" name="senf">
                                                <option value="">--همه موارد--</option>
                                                <option value="سوپر مارکت" {{ $Task->senf == "سوپر مارکت" ? 'selected' : '' }}> سوپر مارکت </option>
                                                <option value="یاران دریان" {{ $Task->senf == "یاران دریان" ? 'selected' : '' }}> یاران دریان </option>
                                                <option value="عمده فروش" {{ $Task->senf == "عمده فروش" ? 'selected' : '' }}> عمده فروش </option>
                                                <option value="خرده فروش" {{ $Task->senf == "خرده فروش" ? 'selected' : '' }}> خرده فروش </option>
                                                <option value="سوپر پروتئین" {{ $Task->senf == "سوپر پروتئین" ? 'selected' : '' }}> سوپر پروتئین </option>
                                                <option value="شوینده و بهداشتی" {{ $Task->senf == "شوینده و بهداشتی" ? 'selected' : '' }}> شوینده و بهداشتی </option>
                                                <option value="رستوران" {{ $Task->senf == "رستوران" ? 'selected' : '' }}> رستوران </option>
                                                <option value="فست فود" {{ $Task->senf == "فست فود" ? 'selected' : '' }}> فست فود </option>
                                                <option value="تره بار" {{ $Task->senf == "تره بار" ? 'selected' : '' }}>تره بار </option>
                                                <option value="هورکا" {{ $Task->senf == "هورکا" ? 'selected' : '' }}>هورکا </option>
                                                <option value="تعاونی" {{ $Task->senf == "تعاونی" ? 'selected' : '' }}>تعاونی </option>
                                                <option value="سایر" {{ $Task->senf == "سایر" ? 'selected' : '' }}> سایر </option>
                                            </select>
                                        </div>
                                        <hr class="my-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="user_id">انتخاب بازاریاب مسئول</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="user_id" name="user_id">
                                                <option>--انتخاب کنید--</option>
                                                @foreach ($Users as $visitor)
                                                    <option value="{{ $visitor->id }}" {{ $Task->user_id == $visitor->id ? 'selected' : '' }}>{{ $visitor->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="code">تاریخ عملیات</label>
                                            <input class="form-control" id="code" placeholder="تاریخ فعالیت" type="text" value="{{ $Task->date }}"/>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="min_sale_item">حداقل تعداد فروش در این مسیر</label>
                                            <input class="form-control" id="min_sale_item" name="min_sale_item" placeholder="حداقل تعداد فروش" type="number" value="{{ $Task->min_sale_item }}"/>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="min_sale_price">حداقل مبلغ فروش در این مسیر</label>
                                            <input class="form-control" id="min_sale_price" name="min_sale_price" placeholder="حداقل مبلغ فروش" type="number" value="{{ $Task->min_sale_price }}"/>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="min_sale_item_price">اعمال محدودیت حداقل مبلغ هر سفارش</label>
                                            <input class="form-control" id="min_sale_item_price" name="min_sale_item_price" placeholder="محدودیت حداقل مبلغ هر سفارش" type="number" value="{{ $Task->min_sale_item_price }}"/>
                                        </div>
                                        <div class="col-md-6">
                                            <p>وضعیت تسک:</p>
                                            <input type="checkbox" name="status" id="status"
                                                {{ $Task->status == 1 ? 'checked' : '' }}>
                                            <label for="status" class="cr">فعال</label>
                                        </div>
                                    </div>
                                    <div class="pt-4">
                                        <button class="btn btn-primary me-sm-3 me-1" type="submit">به روزرسانی مسیر</button>
                                    </div>
                                </form>
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
<script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<script>
    $('.tasks').addClass('open')
    $('.tasks .list').addClass('active open')
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

</script>
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script>
    $(document).ready(function() {
        jalaliDatepicker.startWatch();

        //انتخاب دسته بندی فرزند
        $('#region_id').on('change', function () {
            var region_id = $(this).val();
            if (region_id) {
                $.ajax({
                    url: '{{ asset("getAreasByRegion/") }}/' + region_id,
                    type: "GET",
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data) {
                            $('#areas').empty();
                            $('#areas').append(
                                '<option value="0">انتخاب ناحیه</option>');
                            $.each(data, function (key, area) {
                                $('select[name="area_id"]').append(
                                    '<option value="' + area.id +
                                    '">' + area.name + '</option>');
                            });
                        } else {
                            $('#region_id').empty();
                        }
                    }
                });
            } else {
                $('#region_id').empty();
            }
        });

    });

</script>

</body>

</html>

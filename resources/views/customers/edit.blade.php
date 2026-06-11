<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ویرایش مشتری - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet" />
    <!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
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
                        <div class="row justofy-content-between">
                            <div class="col">
                                <h4 class="py-3 mb-2">
                                    <span class="text-muted fw-light">مشتریان /</span>
                                    ویرایش مشتری
                                </h4>
                            </div>
                            <div class="col text-end">
                                <a href="{{ session('backlink') }}" class="btn btn-label-dark waves-effect ms-3 mt-2"
                                    type="button">
                                    بازگشت
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="black" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <!-- Sticky Actions -->
                        <div class="row mt-3">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <form class="card-body" action="{{ route('customers.update', $customer->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @include('errors.errors')
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label" for="fullname">نام کامل مشتری</label>
                                                <input class="form-control" id="fullname"
                                                    placeholder="نام و نام خانوادگی" type="text" name="name"
                                                    value="{{ $customer->name }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="tablo">تابلو مشتری</label>
                                                <input class="form-control" id="tablo" placeholder="تابلو مشتری"
                                                    type="text" name="tablo" value="{{ $customer->tablo }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="customer_code">کد مشتری</label>
                                                <input class="form-control" id="customer_code" placeholder="کد مشتری"
                                                    type="text" name="customer_code"
                                                    value="{{ $customer->customer_code }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="fullname">شماره تلفن مشتری</label>
                                                <input class="form-control" id="fullname"
                                                    placeholder="شماره تلفن مشتری" type="text" name="phone"
                                                    value="{{ $customer->phone }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="mobile">شماره موبایل مشتری</label>
                                                <input class="form-control" id="mobile"
                                                    placeholder="شماره موبایل مشتری" type="text" name="mobile"
                                                    value="{{ $customer->mobile }}" />
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label" for="national_id">کدملی مشتری</label>
                                                <input class="form-control" id="national_id"
                                                    placeholder="کدملی مشتری" type="text" name="national_id"
                                                    value="{{ $customer->national_id }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="mapcode">مپ کد مشتری</label>
                                                <input class="form-control" id="mapcode" placeholder="مپ کد مشتری"
                                                    type="text" name="mapcode"
                                                    value="{{ $customer->mapcode }}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="sales_channel_id">کانال فروش</label>
                                                <select class="select2 form-select" id="sales_channel_id"
                                                    name="sales_channel_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($salesChannels as $segment)
                                                        <option value="{{ $segment->id }}"
                                                            @if (old('sales_channel_id', $customer->sales_channel_id) == $segment->id) selected @endif>
                                                            {{ $segment->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="customer_group_id">گروه مشتری</label>
                                                <select class="select2 form-select" id="customer_group_id"
                                                    name="customer_group_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($customerGroups as $segment)
                                                        <option value="{{ $segment->id }}"
                                                            @if (old('customer_group_id', $customer->customer_group_id) == $segment->id) selected @endif>
                                                            {{ $segment->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label" for="customer_status_id">وضعیت مشتری</label>
                                                <select class="select2 form-select" id="customer_status_id"
                                                    name="customer_status_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($customerStatuses as $segment)
                                                        <option value="{{ $segment->id }}"
                                                            @if (old('customer_status_id', $customer->customer_status_id) == $segment->id) selected @endif>
                                                            {{ $segment->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($usesAreaWorkflow)
                                                <div class="col-md-3">
                                                    <label class="form-label" for="multicol-country">منطقه
                                                        مشتری@if ($requiresAreaWorkflow)
                                                            <small style="color: red">*</small>
                                                        @endif
                                                    </label>
                                                    <select class="select2 form-select" name="region_id"
                                                        id="region_id" style="width: 100%;"
                                                        @if ($requiresAreaWorkflow) required @endif>
                                                        <option value="">--هیچکدام--</option>
                                                        @foreach ($Regions as $region)
                                                            <option value="{{ $region->id }}"
                                                                {{ $Cur_Region && $region->id == $Cur_Region->id ? 'selected' : '' }}>
                                                                {{ $region->name }} </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @else
                                                <input name="region_id" type="hidden" value="0">
                                            @endif
                                            @if ($usesRouteWorkflow)
                                                <div class="col-md-3">
                                                    <label class="form-label" for="multicol-country">انتخاب
                                                        مسیر@if ($requiresRouteWorkflow)
                                                            <small style="color: red">*</small>
                                                        @endif
                                                    </label>
                                                    <select class="select2 form-select" name="area" id="areas"
                                                        style="width: 100%;"
                                                        @if ($requiresRouteWorkflow) required @endif>
                                                        <option value="">--هیچکدام--</option>
                                                        @foreach ($This_areas as $t_areas)
                                                            <option value="{{ $t_areas->id }}"
                                                                {{ $t_areas->id == $customer->area ? 'selected' : '' }}>
                                                                {{ $t_areas->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @else
                                                <input name="area" type="hidden" value="0">
                                            @endif


                                            <div class="col-md-6">
                                                <label class="form-label" for="multicol-username">آدرس
                                                    فروشگاه:</label>
                                                <textarea class="form-control" name="address" placeholder="آدرس کامل فروشگاه مشتری">{{ $customer->address }}</textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="multicol-username">آدرس انبار:</label>
                                                <textarea class="form-control" ame="store_address" placeholder="آدرس کامل انبار مشتری">{{ $customer->store_address }}</textarea>
                                            </div>
                                        </div>
                                        <div class="pt-4">
                                            <button class="btn btn-primary me-sm-3 me-1" type="submit">به روزرسانی
                                                مشتری</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <form class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="multicol-country">محدودیت برای
                                                    مشتری</label>
                                                <select class="select2 form-select" data-allow-clear="true"
                                                    id="multicol-country">
                                                    <option value="">انتخاب کنید</option>
                                                    <option value="1">فعال</option>
                                                    <option value="2">غیرفعال</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="fullname">سقف محدودیت مالی برای فروش به
                                                    این مشتری (ریال)</label>
                                                <input class="form-control" id="fullname" placeholder="5000000"
                                                    type="number" />
                                            </div>

                                        </div>
                                        <div class="pt-4">
                                            <button class="btn btn-warning me-sm-3 me-1" type="submit">اعمال
                                                محدودیت</button>
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
        // datatable (jquery)
        $('.customers').addClass('open')
        $('.customers .list').addClass('active open')

        $(document).ready(function() {
            @if ($usesRouteWorkflow)
                $('#region_id').on('change', function() {
                    var region_id = $(this).val();
                    if (region_id) {
                        $.ajax({
                            url: '{{ asset('getAreasByRegion/') }}/' + region_id,
                            type: "GET",
                            data: {
                                "_token": "{{ csrf_token() }}"
                            },
                            dataType: "json",
                            success: function(data) {
                                if (data) {
                                    $('#areas').empty();
                                    $('#areas').append(
                                        '<option value="">انتخاب ناحیه</option>');
                                    $.each(data, function(key, area) {
                                        $('select[name="area"]').append(
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
            @endif
        });
    </script>
</body>

</html>

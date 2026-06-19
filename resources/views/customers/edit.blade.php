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
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
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
                        <div class="card mb-4 erp-form-card">
                            <div class="card-header border-bottom">
                                <div>
                                    <h5 class="mb-1">ویرایش اطلاعات مشتری</h5>
                                    <small class="text-muted">{{ $customer->name }} — {{ $customer->customer_code }}</small>
                                </div>
                            </div>
                            <form id="editCustomer" class="erp-form-card__form" action="{{ route('customers.update', $customer->id) }}"
                                method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="card-body">
                                @include('errors.errors')

                                <div class="mb-4">
                                    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">اطلاعات اصلی</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label" for="fullname">نام کامل مشتری</label>
                                            <input class="form-control" id="fullname" placeholder="نام و نام خانوادگی"
                                                type="text" name="name" value="{{ old('name', $customer->name) }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="tablo">تابلو مشتری</label>
                                            <input class="form-control" id="tablo" placeholder="تابلو مشتری" type="text"
                                                name="tablo" value="{{ old('tablo', $customer->tablo) }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="customer_code">کد مشتری</label>
                                            <input class="form-control" id="customer_code" placeholder="کد مشتری"
                                                type="text" name="customer_code"
                                                value="{{ old('customer_code', $customer->customer_code) }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">اطلاعات تماس</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label" for="phone">شماره تلفن</label>
                                            <input class="form-control" id="phone" placeholder="شماره تلفن مشتری"
                                                type="text" name="phone"
                                                value="{{ old('phone', $customer->phone) }}" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="mobile">شماره موبایل</label>
                                            <input class="form-control" id="mobile" placeholder="شماره موبایل مشتری"
                                                type="text" name="mobile"
                                                value="{{ old('mobile', $customer->mobile) }}" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="national_id">کد ملی</label>
                                            <input class="form-control" id="national_id" placeholder="کدملی مشتری"
                                                type="text" name="national_id"
                                                value="{{ old('national_id', $customer->national_id) }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">طبقه‌بندی</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4 col-lg-3">
                                            <label class="form-label" for="mapcode">مپ کد</label>
                                            <input class="form-control" id="mapcode" placeholder="مپ کد مشتری"
                                                type="text" name="mapcode"
                                                value="{{ old('mapcode', $customer->mapcode) }}" />
                                        </div>
                                        <div class="col-md-4 col-lg-3">
                                            <label class="form-label" for="customer_group_id">گروه مشتری</label>
                                            <select class="select2 form-select w-100" id="customer_group_id"
                                                name="customer_group_id">
                                                <option value="">انتخاب کنید</option>
                                                @foreach ($customerGroups as $segment)
                                                    <option value="{{ $segment->id }}"
                                                        @if (old('customer_group_id', $customer->customer_group_id) == $segment->id) selected @endif>
                                                        {{ $segment->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 col-lg-3">
                                            <label class="form-label" for="sales_channel_id">کانال فروش</label>
                                            <select class="select2 form-select w-100" id="sales_channel_id"
                                                name="sales_channel_id">
                                                <option value="">انتخاب کنید</option>
                                                @foreach ($salesChannels as $segment)
                                                    <option value="{{ $segment->id }}"
                                                        @if (old('sales_channel_id', $customer->sales_channel_id) == $segment->id) selected @endif>
                                                        {{ $segment->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 col-lg-3">
                                            <label class="form-label" for="customer_status_id">وضعیت مشتری</label>
                                            <select class="select2 form-select w-100" id="customer_status_id"
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
                                            <div class="col-md-4 col-lg-3">
                                                <label class="form-label" for="region_id">منطقه مشتری
                                                    @if ($requiresAreaWorkflow)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                <select class="select2 form-select w-100" name="region_id" id="region_id"
                                                    @if ($requiresAreaWorkflow) required @endif>
                                                    <option value="">--هیچکدام--</option>
                                                    @foreach ($Regions as $region)
                                                        <option value="{{ $region->id }}"
                                                            {{ $Cur_Region && $region->id == $Cur_Region->id ? 'selected' : '' }}>
                                                            {{ $region->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <input name="region_id" type="hidden" value="0">
                                        @endif
                                        @if ($usesRouteWorkflow)
                                            <div class="col-md-4 col-lg-3">
                                                <label class="form-label" for="areas">انتخاب مسیر
                                                    @if ($requiresRouteWorkflow)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                <select class="select2 form-select w-100" name="area" id="areas"
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
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">محدودیت‌های مالی</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="max_purchase_amount">سقف مبلغ خرید (ریال)</label>
                                            <input class="form-control seprator" id="max_purchase_amount"
                                                name="max_purchase_amount" type="text"
                                                value="{{ old('max_purchase_amount', $customer->max_purchase_amount ? number_format((float) $customer->max_purchase_amount) : '') }}"
                                                placeholder="مثلاً ۵۰,۰۰۰,۰۰۰" />
                                            <small class="text-muted">حداکثر مبلغ مجاز برای ثبت سفارش این مشتری.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="max_discount_amount">سقف مبلغ تخفیف (ریال)</label>
                                            <input class="form-control seprator" id="max_discount_amount"
                                                name="max_discount_amount" type="text"
                                                value="{{ old('max_discount_amount', $customer->max_discount_amount ? number_format((float) $customer->max_discount_amount) : '') }}"
                                                placeholder="مثلاً ۱,۰۰۰,۰۰۰" />
                                            <small class="text-muted">حداکثر مبلغ تخفیف مجاز در هر سفارش.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <h6 class="fw-semibold text-body border-bottom pb-2 mb-3">آدرس</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="address">آدرس فروشگاه</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"
                                                placeholder="آدرس کامل فروشگاه مشتری">{{ old('address', $customer->address) }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="store_address">آدرس انبار</label>
                                            <textarea class="form-control" id="store_address" name="store_address" rows="3"
                                                placeholder="آدرس کامل انبار مشتری">{{ old('store_address', $customer->store_address) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                @include('partials.erp-form-card-footer', [
                                    'hintText' => 'تغییرات پس از ذخیره اعمال می‌شوند.',
                                    'cancelUrl' => session('backlink'),
                                    'submitLabel' => 'به‌روزرسانی مشتری',
                                    'submitIcon' => 'ti-device-floppy',
                                ])
                            </form>
                        </div>
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

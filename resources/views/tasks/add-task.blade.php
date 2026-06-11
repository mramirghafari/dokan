<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت مسیر برای بازاریاب - دکان دارمینو</title>
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
                            ثبت مسیر برای بازاریاب
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-3">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <form class="card-body" id="addTask" action="{{ route('tasks.store') }}"
                                        method="POST" novalidate>
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="region_id">انتخاب منطقه<small
                                                        style="color: red">*</small></label>
                                                <select class="select2 form-select" name="region_id" id="region_id"
                                                    required>
                                                    <option value="0">انتخاب کنید</option>
                                                    @foreach ($Regions as $region)
                                                        <option value="{{ $region->id }}">{{ $region->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="areas">انتخاب مسیر<small
                                                        style="color: red">*</small></label>
                                                <select class="select2 form-select" name="area_id" id="areas"
                                                    required>
                                                    <option value="0">--انتخاب کنید--</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="channel">انتخاب کانال</label>
                                                <select class="select2 form-select" id="channel" name="channel">
                                                    <option value="0">--همه موارد--</option>
                                                    <option value="سوپر مارکت"> سوپر مارکت </option>
                                                    <option value="یاران دریان"> یاران دریان </option>
                                                    <option value="عمده فروش"> عمده فروش </option>
                                                    <option value="خرده فروش"> خرده فروش </option>
                                                    <option value="سوپر پروتئین"> سوپر پروتئین </option>
                                                    <option value="شوینده و بهداشتی"> شوینده و بهداشتی </option>
                                                    <option value="رستوران"> رستوران </option>
                                                    <option value="فست فود"> فست فود </option>
                                                    <option value="تره بار">تره بار </option>
                                                    <option value="هورکا">هورکا </option>
                                                    <option value="تعاونی">تعاونی </option>
                                                    <option value="سایر"> سایر </option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="senf">انتخاب صنف</label>
                                                <select class="select2 form-select" name="senf" id="senf">
                                                    <option value="0">--همه موارد--</option>
                                                    <option value="سوپر مارکت"> سوپر مارکت </option>
                                                    <option value="یاران دریان"> یاران دریان </option>
                                                    <option value="عمده فروش"> عمده فروش </option>
                                                    <option value="خرده فروش"> خرده فروش </option>
                                                    <option value="سوپر پروتئین"> سوپر پروتئین </option>
                                                    <option value="شوینده و بهداشتی"> شوینده و بهداشتی </option>
                                                    <option value="رستوران"> رستوران </option>
                                                    <option value="فست فود"> فست فود </option>
                                                    <option value="تره بار">تره بار </option>
                                                    <option value="هورکا">هورکا </option>
                                                    <option value="تعاونی">تعاونی </option>
                                                    <option value="سایر"> سایر </option>
                                                </select>
                                            </div>
                                            <hr class="my-3">
                                            <div class="col-md-6">
                                                <label class="form-label" for="user_id">انتخاب بازاریاب مسئول <small
                                                        style="color: red">*</small></label>
                                                <select class="select2 form-select" id="user_id" name="user_id"
                                                    required>
                                                    <option value="0">--انتخاب کنید--</option>
                                                    @foreach ($Users as $visitor)
                                                        <option value="{{ $visitor->id }}">{{ $visitor->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="code">تاریخ عملیات <small
                                                        style="color: red">*</small></label>
                                                <input type="text" class="form-control" name="date"
                                                    id="date" data-jdp required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="min_sale_item">حداقل تعداد فروش در این
                                                    مسیر</label>
                                                <input class="form-control" id="min_sale_item"
                                                    placeholder="حداقل تعداد فروش" name="min_sale_item"
                                                    type="number" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="min_sale_price">حداقل مبلغ فروش در این
                                                    مسیر</label>
                                                <input class="form-control" id="min_sale_price" name="min_sale_price"
                                                    placeholder="حداقل مبلغ فروش" type="number" />
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label" for="min_sale_item_price">اعمال محدودیت
                                                    حداقل مبلغ هر سفارش</label>
                                                <input class="form-control" id="min_sale_item_price"
                                                    placeholder="محدودیت حداقل مبلغ هر سفارش"
                                                    name="min_sale_item_price" type="number" />
                                            </div>
                                        </div>
                                        <div class="pt-4">
                                            <button class="btn btn-primary me-sm-3 me-1" type="submit">ثبت مسیر
                                                جدید</button>
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
        $(function() {
            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: false,
                    pageLength: 5,
                });

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }


        });
    </script>

    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    @php
        $taskVisitors = $Users
            ->map(function ($visitor) {
                return [
                    'id' => $visitor->id,
                    'name' => $visitor->name,
                    'leader_id' => $visitor->leader_id,
                    'organization_id' => $visitor->organization_id,
                ];
            })
            ->values();

        $taskRegions = $Regions
            ->map(function ($region) {
                return [
                    'id' => $region->id,
                    'leader_id' => $region->leader_id,
                    'organization_id' => $region->organization_id,
                ];
            })
            ->values();
    @endphp
    <script>
        $(document).ready(function() {
                    jalaliDatepicker.startWatch();

                    var visitors = @json($taskVisitors);
                    var regions = @json($taskRegions);

                    function normalizeToArray(value) {
                        if (!value) {
                            return [];
                        }

                        if (Array.isArray(value)) {
                            return value.filter(Boolean).map(String);
                        }

                        try {
                            var parsed = JSON.parse(value);
                            if (Array.isArray(parsed)) {
                                return parsed.filter(Boolean).map(String);
                            }
                        } catch (e) {}

                        return [String(value)];
                    }

                    function loadVisitorsByRegion(regionId) {
                        $('#user_id').empty().append('<option value="0">--انتخاب کنید--</option>');

                        if (!regionId || regionId === '0') {
                            return;
                        }

                        var selectedRegion = regions.find(function(region) {
                            return String(region.id) === String(regionId);
                        });

                        if (!selectedRegion) {
                            return;
                        }

                        var regionLeaderIds = normalizeToArray(selectedRegion.leader_id);
                        var regionOrganizationIds = normalizeToArray(selectedRegion.organization_id);

                        visitors.forEach(function(visitor) {
                            var visitorOrganizationIds = normalizeToArray(visitor.organization_id);
                            var orgMatch = !regionOrganizationIds.length || regionOrganizationIds.some(function(
                                orgId) {
                                return visitorOrganizationIds.includes(String(orgId));
                            });
                            var leaderMatch = !regionLeaderIds.length || regionLeaderIds.includes(String(visitor
                                .leader_id));

                            if (orgMatch && leaderMatch) {
                                $('#user_id').append('<option value="' + visitor.id + '">' + visitor.name +
                                    '</option>');
                            }
                        });
                    }

                    $('#region_id').on('change', function() {
                        var regionId = $(this).val();
                        loadVisitorsByRegion(regionId);

                        if (regionId && regionId !== '0') {
                            $.ajax({
                                url: '{{ asset('getAreasByRegion/') }}/' + regionId,
                                type: 'GET',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                dataType: 'json',
                                success: function(data) {
                                    $('#areas').empty();
                                    $('#areas').append('<option value="0">انتخاب ناحیه</option>');

                                    if (data && data.length) {
                                        $.each(data, function(key, area) {
                                            $('#areas').append('<option value="' + area.id +
                                                '">' + area.name + '</option>');
                                        });
                                    }
                                }
                            });
                        } else {
                            $('#areas').empty();
                            $('#areas').append('<option value="0">--انتخاب کنید--</option>');
                        }
                    });

                    loadVisitorsByRegion($('#region_id').val());
    </script>
    <script>
        $(document).ready(function() {
            $('#addTask').on('submit', function(e) {
                let isValid = true;

                // پاک کردن پیام‌های قبلی
                $('.error-message').remove();

                // چک کردن تمام فیلدهای این فرم که الزامی هستند
                $(this).find('input[required], select[required]').each(function() {
                    let $field = $(this);
                    let value = $.trim($field.val());

                    // شرط: اگر select باشد و مقدارش 0 باشد، یا اینپوت خالی باشد
                    if (($field.is('select') && value === '0') ||
                        ($field.is('input') && value === '')) {

                        isValid = false;

                        // ساخت پیام خطا
                        let errorMsg = $(
                            '<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>'
                        );

                        // درج پیام بعد از فیلد
                        if ($field.next('.select2').length) {
                            // اگر select2 هست، پیام رو بعد از container select2 بذاریم
                            $field.next('.select2').after(errorMsg);
                        } else {
                            $field.after(errorMsg);
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault(); // جلوگیری از ارسال فرم
                }
            });
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت مشتری جدید - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/neshan-sdk/v1.1.5/index.css') }}" />


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
                        <div class="row justify-content-between">
                            <div class="col">
                                <h4 class="py-3 mb-2">
                                    <span class="text-muted fw-light">مشتریان /</span>
                                    ثبت مشتری جدید
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
                                    <h5 class="mb-1">فرم ثبت مشتری</h5>
                                    <small class="text-muted">اطلاعات مشتری را در بخش‌های زیر تکمیل کنید. فیلدهای
                                        <span class="text-danger">*</span> الزامی هستند.</small>
                                </div>
                            </div>
                            <form id="addCustomer" class="erp-form-card__form" action="{{ route('customers.store') }}" method="POST" novalidate>
                                @csrf
                                <div class="card-body">
                                    @include('errors.errors')
                                    @include('customers._form')
                                </div>
                                @include('partials.erp-form-card-footer', [
                                    'hintText' => 'پس از تکمیل فرم، روی «ثبت مشتری» کلیک کنید.',
                                    'cancelUrl' => session('backlink'),
                                    'submitLabel' => 'ثبت مشتری',
                                    'submitIcon' => 'ti-check',
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

    <script src="{{ asset('assets/vendor/libs/neshan-sdk/v1.1.5/index.js') }}"></script>

    <script>
        $(document).ready(function() {

            //انتخاب دسته بندی فرزند
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

            //select Prs From API
            $('#childCategory_id').on('change', function() {
                var title = $(this).val();
                if (title) {
                    $.ajax({
                        url: "{{ route('index') }}/products/getprInfo/" + title,
                        type: "GET",
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(data) {
                            if (data.PricePer == 'یک عدد محصول') {
                                $('.PerPrice').html('هر عدد محصول');
                            } else {
                                if (data.weight_vahed != null) {
                                    $('.PerPrice').html('هر ' + data.weight_vahed);
                                }

                            }
                        }
                    });
                } else {
                    $('#childCategory_id').empty();
                }
            });

            $('#parentCategoryId').on('change', function() {
                var parentCategoryId = $(this).val();
                if (parentCategoryId) {
                    $.ajax({
                        url: "{{ route('index') }}/products/getprs/" + parentCategoryId,
                        type: "GET",
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(data) {
                            if (data) {
                                $('#childCategory_id').empty();
                                $('#childCategory_id').append(
                                    '<option value="">انتخاب محصول</option>');
                                $.each(data, function(key, childCategory_id) {
                                    $('select[name="title"]').append(
                                        '<option value="' + childCategory_id +
                                        '">' + childCategory_id + '</option>');
                                });
                            } else {
                                $('#childCategory_id').empty();
                                alert('fai');
                            }
                        }
                    });
                } else {
                    $('#childCategory_id').empty();
                }
            });

            //getStore
            $('#organization_id').on('change', function() {
                var organization_id = $(this).val();
                if (organization_id) {
                    $.ajax({
                        url: '/products/getStore/' + organization_id,
                        type: "GET",
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(data) {
                            if (data) {
                                $('#store_id').empty();
                                $('#store_id').append(
                                    '<option value="">انتخاب انبار</option>');
                                $.each(data, function(key, store_id) {
                                    $('select[name="store_id"]').append(
                                        '<option value="' + store_id.id +
                                        '">' + store_id
                                        .title + '</option>');
                                });
                            } else {
                                $('#store_id').empty();
                            }
                        }
                    });
                } else {
                    $('#store_id').empty();
                }
            });


        });
    </script>

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
    <script>
        $(document).ready(function() {
            const neshanMapget = new nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: "map_get",
                zoom: 14,
                pitch: 0,
                center: [51.391173, 35.700954],
                minZoom: 2,
                maxZoom: 21,
                trackResize: true,
                mapKey: "web.69873d4db05f495bb49de6c13e8eb294",
                poi: false,
                traffic: false,
                mapTypeControllerOptions: {
                    show: false,
                    position: 'bottom-left'
                }
            });


            var popup = new nmp_mapboxgl.Popup({
                offset: 25
            }).setText('در محل مورد نظر قرار بگیرد.');


            var marker_with_popup = new nmp_mapboxgl.Marker({
                    color: "#FABA0D",
                    draggable: true
                }).setPopup(popup)
                .setLngLat([51.391173, 35.700954])
                .addTo(neshanMapget).togglePopup();

            function ShoponDragEnd() {
                const lngLat = marker_with_popup.getLngLat();
                var latinp = document.getElementById('shop_lat').value = lngLat.lat;
                var langinp = document.getElementById('shop_lng').value = lngLat.lng;

            }
            marker_with_popup.on('dragend', ShoponDragEnd);

            // Add geolocate control to the map.
            // Initialize the geolocate control.
            let geolocate = new nmp_mapboxgl.GeolocateControl({
                positionOptions: {
                    enableHighAccuracy: true
                },
                trackUserLocation: true
            });
            // Add the control to the map.
            neshanMapget.addControl(geolocate);
            neshanMapget.on("load", function() {
                geolocate.trigger(); // add this if you want to fire it by code instead of the button
            });
            geolocate.on("geolocate", locateUser);

            function locateUser(e) {
                // alert("A geolocate event has occurred.");
                //alert("lng:" + e.coords.longitude + ", lat:" + e.coords.latitude);



            }

            const neshanMapget2 = new nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: "map_get_store",
                zoom: 14,
                pitch: 0,
                center: [51.391173, 35.700954],
                minZoom: 2,
                maxZoom: 21,
                trackResize: true,
                mapKey: "web.69873d4db05f495bb49de6c13e8eb294",
                poi: false,
                traffic: false,
                mapTypeControllerOptions: {
                    show: false,
                    position: 'bottom-left'
                }
            });

            var popup2 = new nmp_mapboxgl.Popup({
                offset: 25
            }).setText(
                'روی محل مورد نظر قرار بگیرد'
            );

            var marker_with_popup2 = new nmp_mapboxgl.Marker({
                    color: "#FABA0D",
                    draggable: true
                }).setPopup(popup2)
                .setLngLat([51.391173, 35.700954])
                .addTo(neshanMapget2).togglePopup();

            function StoreonDragEnd() {
                const lngLat = marker_with_popup2.getLngLat();
                var latinp = document.getElementById('store_lat').value = lngLat.lat;
                var langinp = document.getElementById('store_lng').value = lngLat.lng;

            }

            marker_with_popup2.on('dragend', StoreonDragEnd);


        });
    </script>
    <script>
        $(document).ready(function() {
            $('#addCustomer').on('submit', function(e) {
                let isValid = true;

                // فقط پیام‌های قدیمی *مربوط به همان فیلدها* حذف شود نه همهٔ پیام‌ها
                $(this).find('input[required], select[required]').each(function() {
                    let $field = $(this);
                    $field.nextAll('.error-message').remove(); // پاک کردن خطای همان فیلد

                    let value = $.trim($field.val());

                    if (($field.is('select') && (value === '' || value === '0')) ||
                        ($field.is('input') && value === '')) {

                        isValid = false;

                        let errorMsg = $(
                            '<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>'
                        );

                        // اگر Select2 هست
                        if ($field.next('.select2').length) {
                            $field.next('.select2').after(errorMsg);
                        } else {
                            $field.after(errorMsg);
                        }
                    }
                });

                if (!isValid) e.preventDefault();
            });

            // در صورت انتخاب آیتم جدید در select، خطای همون فیلد حذف شه
            $(document).on('change', 'select[required], input[required]', function() {
                let $field = $(this);
                if ($field.val() !== '' && $field.val() !== '0') {
                    $field.nextAll('.error-message').remove();
                }
            });
        });
    </script>
</body>

</html>

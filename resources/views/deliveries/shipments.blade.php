<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>تعریف بار / مرسولات - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/jquery-ui/jquery-ui.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/neshan-sdk/v1.1.1/index.css') }}" />
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
    <style>
        #map {
            min-height: 700px;
            height: 100%;
            width: 100%;
        }
        #sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
        #sortable li {
            margin: 0 3px 3px 3px;
            font-size: 15px;
            border: 1px solid #ededed;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
        }
        #sortable li:hover {
            background-color: #ededed;
        }
    </style>
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
                <form id="safar" method="POST" action="{{ route('deliveries.storeShipments') }}">
                    @csrf
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">انبار و توزیع /</span>
                            تعریف بار و سفر
                        </h4>
                        <div class="col d-flex justify-content-end">
                            <div class="col-2 mr-auto text-end mb-2">
                                <button class="btn btn-primary" id="sbtsfr" type="submit">ثبت سفر </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="card col-12 mb-4">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label" for="driver">انتخاب راننده:</label>
                                            <select class="select2 form-select" name="driver_ids[]" multiple id="driver">
                                                <option value="0">انتخاب کنید...</option>
                                                @foreach($Drivers as $driver)
                                                    <option value="{{ $driver->id }}" data-cartons="{{ $driver->cargo ? $driver->cargo->cartons: '' }}" data-weight="{{ $driver->cargo ? $driver->cargo->weight : '' }}" >{{ $driver->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="from_store_id">انتخاب انبار (مبدا سفر):</label>
                                            <select class="select2 form-select" name="store_id" id="from_store_id">
                                                <option value="0">انتخاب کنید...</option>
                                                @foreach($Stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="number">شماره</label>
                                            <input type="text" class="form-control" id="number" name="number" placeholder="شماره سفر" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="date">تاریخ سفر</label>
                                            <input type="text" class="form-control" id="date" name="date" placeholder="تاریخ سفر"  data-jdp />
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="hours">ساعت شروع سفر</label>
                                            <select class="select2 form-select" name="hours" id="hours">
                                                <option value="0">انتخاب کنید...</option>
                                                <option>00:00</option>
                                                <option>01:00</option>
                                                <option>02:00</option>
                                                <option>03:00</option>
                                                <option>04:00</option>
                                                <option>05:00</option>
                                                <option>06:00</option>
                                                <option>07:00</option>
                                                <option>08:00</option>
                                                <option>09:00</option>
                                                <option>10:00</option>
                                                <option>11:00</option>
                                                <option>12:00</option>
                                                <option>13:00</option>
                                                <option>14:00</option>
                                                <option>15:00</option>
                                                <option>16:00</option>
                                                <option>17:00</option>
                                                <option>18:00</option>
                                                <option>19:00</option>
                                                <option>20:00</option>
                                                <option>21:00</option>
                                                <option>22:00</option>
                                                <option>23:00</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label" for="tozihat">توضیحات مخصوص راننده:</label>
                                            <textarea class="form-control" name="tozihat" id="tozihat" placeholder="توضیحات ارسالی برای راننده..."></textarea>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row factor_list_box d-none">
                            <div class="card col-12 mb-4">
                                <div class="card-body">
                                    <div class="col-12">
                                        <div class="col d-flex justify-content-end">
                                            <div class="col-10 mb-2">
                                                <select class="select2 form-select" name="factors" id="factors">
                                                    <option value="0">انتخاب کنید...</option>
                                                    @foreach($PishFactors as $factor)
                                                            <?php
                                                            $details = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->count();
                                                            $Packs = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->sum('pack');
                                                            $tedad = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->sum('tedad');
                                                            ?>
                                                        <option value="{{ $factor->id }}" data-delivery="{{ $factor->recive_date }}" data-name="{{ $factor->customer->name }} / {{ $factor->customer->tablo }}" data-invoiceNum="{{ $factor->invoiceID }}" data-tozihat="{{ $factor->tozihat }}" data-packs="{{ $Packs }}" data-item="{{ $tedad }}" data-aghlam="{{ $details }}" data-address="{{ $factor->customer->address }}" data-lat="{{ $factor->customer->shop_lat }}" data-lng="{{ $factor->customer->shop_lng }}">   شماره: {{ $factor->invoiceID }} |{{ $factor->customer->name }} / {{ $factor->customer->tablo }} | تاریخ تحویل: {{ $factor->recive_date }} | توضیحات: {{ $factor->tozihat }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-2 mr-auto text-end mb-2">
                                                <button class="btn btn-info additem" type="button">افزودن بار جدید</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card col-12 mb-4 gonjayesh_box d-none">

                                <div class="card-body">
                                    <div class="col-12">
                                        <div class="col d-flex justify-content-end">
                                            <div class="col-6" style="min-height: 200px;background: url({{ asset('/img/cartons_bg.png') }}) no-repeat right bottom;background-size: 40% " >
                                                <h3 style="font-size: 20px;">گنجایش کارتن در ناوگان</h3>
                                                <p><span class="cartons">0</span> کارتن از <span class="gonjayesh"></span> کارتن</p>
                                            </div>
                                            <div class="col-6" style="min-height: 200px;background: url({{ asset('/img/maps_bg.png') }}) no-repeat left bottom;background-size: 30%" >
                                                <h3 style="font-size: 20px;">تعداد نقاط توقف در سفر</h3>
                                                <p>تعداد مسیرهای انتخاب شده: <span class="masirs">0</span> مسیر</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                @if(session('Routes'))
                                    <h3>مسیرهای چینش شده</h3>
                                    <p class="col-12 alert alert-info">تعداد فاکتورهای چینش شده: {{ session('Routes')['total_factors'] }} فاکتور</p>
                                    <p class="col-12 alert alert-info">تعداد اقلام در این مسیر شده: {{ session('Routes')['total_details'] }} قلم</p>
                                    <p class="col-12 alert alert-info">تعداد کارتن در این مسیر: {{ session('Routes')['total_packs'] }} کارتن</p>
                                    <p class="col-12 alert alert-info">تعداد اقلام تکی در این مسیر: {{ session('Routes')['total_tedad'] }} عدد</p>
                                    <ul id="sortable">
                                        @foreach(session('Routes')['factors'] as $route)
                                            <li class="row px-0 mx-0 card" data-number="{{ $route->invoiceID }}">
                                                <span class="factor_id">شماره فاکتور:  {{ $route->invoiceID }}</span>
                                                <p class="mb-1">نام تابلو / فروشنده:{{ $route->customer->name }} / {{ $route->customer->tablo }}</p>
                                                <p class="mb-1">آدرس: {{ $route->customer->address }}</p>
                                                <p class="mb-1">توضیحات: {{ $route->tozihat }}</p>
                                                <p class="mb-1">وضعیت اقلام: شامل مجموعا {{ $route->total_details }} قلم {{ $route->total_packs }} کارتن و {{ $route->total_tedad }} عدد </p>
                                                <p class="mb-1">مدت زمان رسیدن به این مسیر: <strong>{{ $route->duration }}</strong> و مقدار قاصله تا این مسیر: <strong>{{ $route->distance }}</strong></p>
                                                <input type="hidden" name="factor_{{ $route->invoiceID }}" value="${count}">
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if(session('Unassigned'))
                                    <h3>مسیرهای در انتظار (چینش نشده)</h3>
                                    <ul id="sortable">
                                        @foreach(session('Unassigned')['factors'] as $route)
                                            <li class="row px-0 mx-0 card" data-number="{{ $route->invoiceID }}">
                                                <span class="factor_id">شماره فاکتور:  {{ $route->invoiceID }}</span>
                                                <p class="mb-1">نام تابلو / فروشنده:{{ $route->customer->name }} / {{ $route->customer->tablo }}</p>
                                                <p class="mb-1">آدرس: {{ $route->customer->address }}</p>
                                                <p>توضیحات: {{ $route->tozihat }}</p>
                                                <p>وضعیت اقلام: شامل مجموعا {{ $route->total_details }} قلم {{ $route->total_packs }} کارتن و {{ $route->total_tedad }} عدد </p>
                                                <p>مدت زمان رسیدن به این مسیر: <strong>{{ $route->duration }}</strong></p>
                                                <p>فاصله از مبدا(انبار) تا این مسیر: <strong>{{ $route->distance }}</strong></p>
                                                <p>وضعیت اقلام: شامل مجموعا {{ $route->total_details }} قلم {{ $route->total_packs }} کارتن و {{ $route->total_tedad }} عدد </p>
                                                <input type="hidden" name="factor_{{ $route->invoiceID }}" value="${count}">
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div id="map"></div>
                        </div>
                    </div>
                </form>
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
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<link href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" rel="stylesheet">
<script src="{{ asset('assets/vendor/libs/neshan-sdk/v1.1.1/index.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/mapbox-polyline/polyline.js') }}"></script>
<script>
    $(function(){
        $("#sortable").sortable({
            update: function(event, ui) {

                $("#sortable li").each(function(index){
                    // index از 0 شروع میشه، ما +1 می‌کنیم
                    $(this).find('input[type="hidden"]').val(index + 1);
                });
            }
        });
        $("#sortable").disableSelection();
    });
</script>
<script>
    jalaliDatepicker.startWatch();
</script>
<script>
    // datatable (jquery)

    $(document).ready(function() {

        let allOptionsCache = $('#factors option').clone();

        $('#date').on('change', function () {
            let selectedDate = $(this).val();
            let $factors = $('#factors');
            let $btn = $('#sbtsfr');
            $('.factor_list_box').removeClass('d-none');

            if (!selectedDate) {
                $factors.empty().append(allOptionsCache.clone());
                $factors.val("0").trigger('change.select2');
                $btn.prop('disabled', false).off('click');
                return;
            }

            let filteredOptions = allOptionsCache.filter(function () {
                return $(this).val() === "0" || $(this).attr('data-delivery') === selectedDate;
            });

            $factors.empty().append(filteredOptions.clone());
            $factors.val("0").trigger('change.select2');

            let realOptions = filteredOptions.filter(function () {
                return $(this).val() !== "0";
            });

            if (realOptions.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'توجه',
                    text: 'در این تاریخ فاکتوری برای تحویل وجود ندارد.',
                    confirmButtonText: 'باشه'
                });
                $btn.prop('disabled', true).off('click');
                return;
            }

            // چک موقعیت lat/lng
            let invalidFactor = null;
            realOptions.each(function () {
                let lat = $(this).attr('data-lat');
                let lng = $(this).attr('data-lng');
                if (!lat || !lng || lat.trim() === '' || lng.trim() === '') {
                    invalidFactor = {
                        name: $(this).attr('data-name'),
                        invoiceNum: $(this).attr('data-invoiceNum')
                    };
                    return false;
                }
            });

            if (invalidFactor) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطای موقعیت مکانی',
                    html: `فاکتور شماره <b>${invalidFactor.invoiceNum}</b><br>مربوط به مشتری <b>${invalidFactor.name}</b> موقعیت ثبت‌شده صحیح ندارد!`,
                    confirmButtonText: 'باشه'
                });
                $btn.prop('disabled', true).off('click');
            } else {
                $btn.prop('disabled', false).off('click');
            }
        });


        $('#driver').on('change',function() {
            driver_id = $(this).val();
            if(driver_id != 0) {
                var boxes = $(this).find('option:selected').attr('data-cartons');
                $('.gonjayesh_box').removeClass('d-none');
                $('.gonjayesh').html(boxes);
            }
        });

        $('#sbtsfr').click(function(e) {
            e.preventDefault();
            var driver =  $('#driver option:selected').val();
            if(driver == 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'انتخاب راننده',
                    text: 'ابتدا راننده را انتخاب کنید.',
                });
                return; // بقیه کد اجرا نشه
            }

            var from_store_id =  $('#from_store_id option:selected').val();
            if(from_store_id == 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'انتخاب انبار',
                    text: 'انبار مبدا را مشخص کنید.',
                });
                return; // بقیه کد اجرا نشه
            }

            var date =  $('#date').val();
            if(date == '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'انتخاب تاریخ',
                    text: 'تاریخ سفر را مشخص کنید.',
                });
                return; // بقیه کد اجرا نشه
            }

            /* var masirs = $('#sortable li').length;
             if(masirs == 0) {
                 Swal.fire({
                     icon: 'warning',
                     title: 'انتخاب مسیر',
                     text: 'هنوز هیچ مسیری برای این سفر مشخص نشده است.',
                 });
                 return; // بقیه کد اجرا نشه
             } */

            $('#safar').submit();

        });
    });

</script>

@if(isset($routes))
    <script type="text/javascript">
        const routesData = @json($routes);
        let neshanMap;

        document.addEventListener("DOMContentLoaded", function () {
            neshanMap = new nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: "map",
                zoom: 12,
                center: [51.4000, 35.7000],
                mapKey: "web.3ba60897ffcd4148a1bfacbca7c7174e",
            });

            neshanMap.on("load", function () {
                console.log("Map loaded successfully ✅");
                drawNearestAddresses();
            });
        });

        //----------------------------------------
        // نمایش نزدیک‌ترین آدرس‌ها از خروجی نِشان
        //----------------------------------------
        function drawNearestAddresses() {
            if (!routesData || routesData.length === 0) {
                console.warn("هیچ داده‌ای برای نمایش وجود ندارد.");
                return;
            }

            // نمایش نقاط تمام سفرها (چند راننده)
            routesData.forEach((trip, index) => {
                const tripPoints = trip.points || [];
                tripPoints.forEach(p => {
                    // مختصات‌شده از API نِشان (snap)
                    const lng = parseFloat(p.lng);
                    const lat = parseFloat(p.lat);
                    const type = p.type;

                    // رنگ براساس نوع نقطه
                    const markerColor =
                        type === "start" ? "green" :
                            type === "end" ? "red" : "#2575fc";

                    // پاپ‌آپ با اطلاعات مشتری
                    const popupHtml = `
                    <div style="direction:rtl;font-family:tahoma;font-size:13px">
                        <b>نوع:</b> ${type} <br>
                        ${p.address ? `<b>آدرس:</b> ${p.address}<br>` : ""}
                        <b>بار:</b> ${p.load ?? 0} کارتن<br>
                        ${p.id ? `<b>ID فاکتور:</b> ${p.id}` : ""}
                    </div>
                `;

                    new nmp_mapboxgl.Marker({ color: markerColor })
                        .setLngLat([lng, lat])
                        .setPopup(new nmp_mapboxgl.Popup({ offset: 6 }).setHTML(popupHtml))
                        .addTo(neshanMap);
                });
            });

            // تنظیم موقعیت نقشه روی مجموعه نقاط
            let allCoords = [];
            routesData.forEach(trip => {
                (trip.points || []).forEach(p => allCoords.push([parseFloat(p.lng), parseFloat(p.lat)]));
            });
            if (allCoords.length > 0) {
                const bounds = new nmp_mapboxgl.LngLatBounds();
                allCoords.forEach(c => bounds.extend(c));
                neshanMap.fitBounds(bounds, { padding: 80 });
            }
        }
    </script>
@endif


</body>

</html>

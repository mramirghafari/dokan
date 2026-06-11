<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش سفر - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/>
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet"/>
    <!-- Core CSS -->
    <link rel="stylesheet" href="https://static.neshan.org/sdk/mapboxgl/v1.13.2/neshan-sdk/v1.1.1/index.css" />
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
            padding: 30px;
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
                            جزئیات سفر
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
                                            <select class="select2 form-select" name="driver_id" id="driver">
                                                <option value="0">انتخاب کنید...</option>
                                                @foreach($Drivers as $driver)
                                                    <option value="{{ $driver->id }}" data-cartons="{{ $driver->cargo ? $driver->cargo->cartons: '' }}" data-weight="{{ $driver->cargo ? $driver->cargo->weight : '' }}" {{ $shipment->driver_id == $driver->id ? 'selected' : '' }} >{{ $driver->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="from_store_id">انتخاب انبار (مبدا سفر):</label>
                                            <select class="select2 form-select" name="store_id" id="from_store_id">
                                                <option value="0">انتخاب کنید...</option>
                                                @foreach($Stores as $store)
                                                    <option value="{{ $store->id }}" {{ $shipment->mabda == $store->id ? 'selected' : '' }}>{{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="number">شماره</label>
                                            <input type="text" class="form-control" id="number" name="number" placeholder="شماره سفر" value="{{ $shipment->number }}" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="date">تاریخ سفر</label>
                                            <input type="text" class="form-control" id="date" name="date" placeholder="تاریخ سفر"  data-jdp value="{{ $shipment->date_fa }}" />
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="hours">ساعت شروع سفر</label>
                                            <select class="select2 form-select" name="hours" id="hours">
                                                <option value="0">انتخاب کنید...</option>
                                                <option {{ $shipment->hours == '00:00' ? 'selected' : '' }}>00:00</option>
                                                <option {{ $shipment->hours == '01:00' ? 'selected' : '' }}>01:00</option>
                                                <option {{ $shipment->hours == '02:00' ? 'selected' : '' }}>02:00</option>
                                                <option {{ $shipment->hours == '03:00' ? 'selected' : '' }}>03:00</option>
                                                <option {{ $shipment->hours == '04:00' ? 'selected' : '' }}>04:00</option>
                                                <option {{ $shipment->hours == '05:00' ? 'selected' : '' }}>05:00</option>
                                                <option {{ $shipment->hours == '06:00' ? 'selected' : '' }}>06:00</option>
                                                <option {{ $shipment->hours == '07:00' ? 'selected' : '' }}>07:00</option>
                                                <option {{ $shipment->hours == '08:00' ? 'selected' : '' }}>08:00</option>
                                                <option {{ $shipment->hours == '09:00' ? 'selected' : '' }}>09:00</option>
                                                <option {{ $shipment->hours == '10:00' ? 'selected' : '' }}>10:00</option>
                                                <option {{ $shipment->hours == '11:00' ? 'selected' : '' }}>11:00</option>
                                                <option {{ $shipment->hours == '12:00' ? 'selected' : '' }}>12:00</option>
                                                <option {{ $shipment->hours == '13:00' ? 'selected' : '' }}>13:00</option>
                                                <option {{ $shipment->hours == '14:00' ? 'selected' : '' }}>14:00</option>
                                                <option {{ $shipment->hours == '15:00' ? 'selected' : '' }}>15:00</option>
                                                <option {{ $shipment->hours == '16:00' ? 'selected' : '' }}>16:00</option>
                                                <option {{ $shipment->hours == '17:00' ? 'selected' : '' }}>17:00</option>
                                                <option {{ $shipment->hours == '18:00' ? 'selected' : '' }}>18:00</option>
                                                <option {{ $shipment->hours == '19:00' ? 'selected' : '' }}>19:00</option>
                                                <option {{ $shipment->hours == '20:00' ? 'selected' : '' }}>20:00</option>
                                                <option {{ $shipment->hours == '21:00' ? 'selected' : '' }}>21:00</option>
                                                <option {{ $shipment->hours == '22:00' ? 'selected' : '' }}>22:00</option>
                                                <option {{ $shipment->hours == '23:00' ? 'selected' : '' }}>23:00</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label" for="tozihat">توضیحات مخصوص راننده:</label>
                                            <textarea class="form-control" name="tozihat" id="tozihat" placeholder="توضیحات ارسالی برای راننده...">{{ $shipment->tozihat }}</textarea>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row pr_list_box">
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
                            <div id="map"></div>
                        </div>
                        <div class="row mt-3">
                            <div class="card">
                                <h5 class="card-header">لیست فاکتورهای ثبت شده برای این سفر:</h5>
                                <div class="table-responsive text-nowrap">
                                    <table class="table">
                                        <thead class="table-light">
                                        <tr>
                                            <th>ردیف</th>
                                            <th>شماره فاکتور</th>
                                            <th>نام مشتری/تابلو</th>
                                            <th>کد مشتری</th>
                                            <th>آدرس</th>
                                            <th>تعداد اقلام</th>
                                            <th>مقدار فاکتور</th>
                                            <th>مبلغ غاکتور</th>
                                            <th>وضعیت پرداخت</th>
                                            <th>وضعیت تحویل</th>
                                        </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                        @php($x = 1)
                                            @foreach($routesForMap as $item)
                                            <tr>
                                                <td>{{ $x }}</td>
                                                <td>@if($item['factor_id'] == 'start') -0- @else {{ $item['factor_id'] }} @endif</td>
                                                <td>@if($item['factor_id'] == 'start') مبدا @else {{ $item['customer_name'] }} @endif</td>
                                                <td>
                                                    <span class="badge bg-label-primary me-1">فعال</span>
                                                </td>
                                                <td>{{ $item['address'] }}</td>
                                                <td>{{ $item['total_details'] }} قلم</td>
                                                <td>{{ $item['total_packs'] > 0 ? $item['total_packs'].'کارتن' : '' }} - {{ $item['total_tedad'] > 0 ? $item['total_tedad'].'عدد' : '' }}</td>
                                                <td>{{ $item['total_details'] }}</td>
                                                <td>پارمیدا مقدسی</td>
                                                <td>پارمیدا مقدسی</td>
                                                <td>پارمیدا مقدسی</td>
                                            </tr>
                                                @php($x++)
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://static.neshan.org/sdk/mapboxgl/v1.13.2/neshan-sdk/v1.1.1/index.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mapbox-polyline/1.2.1/polyline.js"></script>
<script>
    $(function(){
        $("#sortable").sortable({
            update: function(event, ui) {
                // برو تمام liها رو به ترتیب جدید شماره‌گذاری کن
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
        var allpacks = 0;
        var allmasirs = 0;

        $('.additem').click(function () {
            var driver =  $('#driver option:selected').val();
            if(driver == 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'انتخاب راننده',
                    text: 'ابتدا راننده را انتخاب کنید.',
                });
                return; // بقیه کد اجرا نشه
            }
            $('#factors option:selected').attr('disabled','disabled');

            var factor_id   = $('#factors option:selected').val();
            var name        = $('#factors option:selected').attr('data-name');
            var invoiceNum  = $('#factors option:selected').attr('data-invoiceNum');
            var tozihat     = $('#factors option:selected').attr('data-tozihat');
            var recive_date = $('#factors option:selected').attr('data-delivery');
            var address = $('#factors option:selected').attr('data-address');

            var packs   = Number($('#factors option:selected').attr('data-packs')) || 0;
            var tedad   = $('#factors option:selected').attr('data-item');
            var aghlam  = $('#factors option:selected').attr('data-aghlam');

            // چک وجود آیتم با همین invoiceNum
            if ($('#sortable li[data-number="'+invoiceNum+'"]').length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تکراری!',
                    text: 'این فاکتور قبلاً انتخاب شده است.',
                });
                return; // بقیه کد اجرا نشه
            }

            // جمع کل پک‌ها
            allpacks += packs;
            var count = $('#sortable li').length+1;
            $('#sortable').append(`
        <li class="row px-0 mx-0 card" data-number="${invoiceNum}">
            <span class="factor_id">شماره فاکتور: ${invoiceNum}</span>
            <p class="mb-1">نام تابلو / فروشنده: ${name}</p>
            <p class="mb-1">آدرس: ${address}</p>
            <p>توضیحات: ${tozihat}</p>
            <p>وضعیت اقلام: شامل ${packs} کارتن و (تعداد اقلام: ${aghlam} قلم)</p>
            <input type="hidden" name="factor_${invoiceNum}" value="${count}">
        </li>
    `);

            $('.masirs').html($('#sortable li').length);
            $('.cartons').html(allpacks);
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

            var masirs = $('#sortable li').length;
            if(masirs == 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'انتخاب مسیر',
                    text: 'هنوز هیچ مسیری برای این سفر مشخص نشده است.',
                });
                return; // بقیه کد اجرا نشه
            }

            $('#safar').submit();

        });
    });

</script>
@if(!empty($routesForMap) && count($routesForMap) > 0)
    <script type="text/javascript">
        const routesData = @json($routesForMap);
        console.log(routesData);
        document.addEventListener("DOMContentLoaded", () => {
            const neshanMap = new nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: "map",
                zoom: 12.6,
                center: [51.35, 35.70],
                mapKey: "web.3ba60897ffcd4148a1bfacbca7c7174e"
            });

            neshanMap.on("load", () => drawMarkers(neshanMap));
        });

        function drawMarkers(map) {
            if (!routesData || routesData.length === 0) return;

            const totalStops = routesData.length;

            routesData.forEach((r, i) => {
                // مختصات: اولویت با origin، اگر نبود از destination
                const lat = parseFloat(r.origin_lat ?? r.destination_lat);
                const lng = parseFloat(r.origin_lng ?? r.destination_lng);
                if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return;

                let color = "#2575fc"; // پیش‌فرض آبی برای توقف‌ها
                let popupHtml = "";

                // ✅ مبدأ انبار (factor_id = start یا null)
                if (r.factor_id === null || r.factor_id === 'start') {
                    color = "#51bc3c";
                    popupHtml = `
                <div style="direction:rtl;font-family:YekanBakh;text-align:right;font-size:13px">
                    📦 <b>مبدا:</b> {{ $shipment->mabda_title ?? 'انبار مرکزی' }}<br>
                    <small>${lat.toFixed(4)}, ${lng.toFixed(4)}</small>
                </div>
            `;
                }
                else {
                    // رنگ برای توقف‌های تحویل‌شده
                    if (parseInt(r.status) === 1) color = "#196200";
                    // رنگ برای آخرین توقف
                    if (i === totalStops - 1) color = "#d93025";

                    const factorId = r.factor_id;
                    const infoUrl = "{{ route('pishFactorInfo', ':id') }}".replace(':id', factorId);

                    popupHtml = `
                <div style="direction:rtl;font-family:YekanBakh;text-align:right;font-size:13px">
                    👤 <b>مشتری:</b>
                    <a href="${infoUrl}" target="_blank" style="color:#0d6efd;text-decoration:none">
                        ${r.customer_name ?? '—'}
                    </a><br>
                    <b>آدرس:</b> ${r.address ?? '—'}<br>
                    <b>شماره فاکتور:</b> ${r.invoiceID ?? '—'}<br>
                    <b>توضیحات:</b> ${r.tozihat ?? '—'}
                    <hr style="margin:4px 0;"/>
                    <b>اقلام:</b> ${r.total_details ?? 0} قلم / ${r.total_packs ?? 0} کارتن / ${r.total_tedad ?? 0} عدد
                    <hr style="margin:4px 0;border-top:1px solid #eee"/>
                    <small>نقطه ${i} از ${totalStops - 1}</small>
                </div>
            `;
                }

                // ساخت مارکر در نقشه
                new nmp_mapboxgl.Marker({ color })
                    .setLngLat([lng, lat])
                    .setPopup(new nmp_mapboxgl.Popup({ offset: 8 }).setHTML(popupHtml))
                    .addTo(map);
            });

            // تنظیم فوکوس روی همه نقاط
            setTimeout(() => {
                const bounds = new nmp_mapboxgl.LngLatBounds();
                routesData.forEach(r => {
                    const lat = parseFloat(r.origin_lat ?? r.destination_lat);
                    const lng = parseFloat(r.origin_lng ?? r.destination_lng);
                    if (!isNaN(lat) && !isNaN(lng)) bounds.extend([lng, lat]);
                });
                if (!bounds.isEmpty()) map.fitBounds(bounds, { padding: 60, maxZoom: 13 });
            }, 600);
        }
    </script>
@endif

</body>

</html>

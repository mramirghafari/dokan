<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مشتریان مسیر - دکان دارمینو</title>
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
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>

    <style>
        @media(min-width: 768px) {
            .address {
                font-size: 20px
            }
        }
        @media(max-width: 768px) {
            .address {
                font-size: 14px;
            }
            .btn_adress {
                font-size: 14px;
            }
            .btn_adress img{
                width: 30px;
            }
        }
    </style>
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
                    <div class="row justify-content-between">
                        <div class="col-12 col-md-6">
                            <h4 class="py-3 mb-4">
                                <span class="text-muted fw-light">مسیرها /</span>
                                <a href="{{ route('tasks.MyTasks') }}" class="text-muted fw-light">مسیر های من /</a>
                                {{ $task_region->name }} - {{ $task_area->name }}
                            </h4>
                        </div>
                        <div class="col-12 col-md-6 text-md-end pt-md-3">
                            <button onclick="getLocation()" type="button" class="btn btn-success">چینش مسیر</button>
                        </div>
                    </div>
                    <p id="myloc"></p>

                    <script>
                        const x = document.getElementById("myloc");

                        function getLocation() {
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(success, error);
                            } else {
                                x.innerHTML = "Geolocation is not supported by this browser.";
                            }
                        }

                        function addQueryParamsAndReload(params) {
                            const currentUrl = new URL(window.location.href);
                            const searchParams = currentUrl.searchParams;

                            for (const key in params) {
                                if (Object.hasOwnProperty.call(params, key)) {
                                    searchParams.set(key, params[key]);
                                }
                            }

                            currentUrl.search = searchParams.toString();
                            window.location.href = currentUrl.toString(); // This triggers the reload
                        }

                        function success(position) {
                            //  x.innerHTML = "Latitude: " + position.coords.latitude + "<br>Longitude: " + position.coords.longitude;

                            const newParams = {
                                lat: position.coords.latitude,
                                long: position.coords.longitude,
                            };

                            addQueryParamsAndReload(newParams);

                        }

                        function error() {
                            alert("Sorry, no position available.");
                        }
                    </script>
                    <!-- Sticky Actions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card mb-2">
                                <h5 class="card-header">تارگت تعداد فروش</h5>
                                <p class="px-4 mb-0">شما <strong class="text-primary">{{ $Factors_In_Task }} سفارش</strong> از کل مشتریان <strong class="text-primary">{{ count($Customers) }} مشتری </strong>در این مسیر ثبت کرده اید.</p>
                                <div class="card-body">
                                    <div class="demo-vertical-spacing demo-only-element">
                                        <div class="progress">
                                            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ count($Customers) }}" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 40%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-12 mb-3">
                            <input id="searchInput" type="text" class="form-control py-2" placeholder="جستجو در مشتریان این مسیر..." />
                        </div>
                        <div class="col-12 customers_list">
                            @if(count($validCustomers) > 0)
                                @foreach($validCustomers as $customer)
                                        <?php $Check = DB::table('pishfactors')->where('task_id', $task->id)->where('customer_id',$customer->id)->count() ?>
                                    <div class="card mb-3 @if($Check > 0) ok @endif" @if($Check > 0) style="background-color: #ebfcf4" @endif>

                                        <div class="card-header pt-2 px-2 pb-0 header-elements">
                                            <h5 class="card-title d-inline-block me-2">{{ $customer->tablo }} </h5>
                                            <span class="badge bg-label-success rounded-pill d-inline-block me-2">{{ $customer->duration }}</span>
                                            <span class="badge bg-label-info rounded-pill d-inline-block me-2">{{ $customer->distance }}</span>

                                            <div class="card-header-elements ms-auto">
                                                @if($customer->shop_lat != null)
                                                    <span class="badge bg-label-success rounded-pill">لوکیشن دارد</span>
                                                @else
                                                    <span class="badge bg-label-danger rounded-pill">لوکیشن ثبت نشده</span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ asset("/MyTaks/$task->id/CustomerInfo/") }}/{{ $customer->id }}" class="row d-flex justify-content-start g-0">
                                            <div class="col-7">
                                                <div class="card-body pt-0 pb-3 px-2">

                                                    <p class="card-text justify-text mb-1" style="color: #545454;">نام مشتری: <strong>{{ $customer->name }}</strong></p>
                                                    <p class="card-text justify-text mb-1" style="color: #545454;">کانال / صنف: <strong>{{ $customer->channel }} / {{ $customer->senf }}</strong></p>
                                                    <p class="card-text address" style="color: #524595;">آدرس: {{ $customer->address }}</p>
                                                </div>
                                            </div>
                                            <div class="col-5 d-flex justify-content-md-end justify-content-center align-items-center pe-md-5">
                                                <button class="btn rounded-pill btn-outline-vimeo waves-effect mb-3 p-2 p-md-3 mb-md-0 btn_adress" type="button">
                                                    <img src="{{ asset('/img/location_icon.png') }}" />    مشاهده جزئیات/نقشه
                                                </button>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            @endif

                            <hr />
                            @if(count($invalidCustomers) > 0)
                                @foreach($invalidCustomers as $customer)
                                        <?php $Check = DB::table('pishfactors')->where('task_id', $task->id)->where('customer_id',$customer->id)->count() ?>
                                    <div class="card mb-3 @if($Check > 0) ok @endif" @if($Check > 0) style="background-color: #ebfcf4" @endif>

                                        <div class="card-header pt-2 px-2 pb-0 header-elements">
                                            <h5 class="card-title d-inline-block me-2">{{ $customer->tablo }} </h5>

                                            <div class="card-header-elements ms-auto">
                                                @if($customer->shop_lat != null)
                                                    <span class="badge bg-label-success rounded-pill">لوکیشن دارد</span>
                                                @else
                                                    <span class="badge bg-label-danger rounded-pill">لوکیشن ثبت نشده</span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ asset("/MyTaks/$task->id/CustomerInfo/") }}/{{ $customer->id }}" class="row d-flex justify-content-start g-0">
                                            <div class="col-7">
                                                <div class="card-body pt-0 pb-3 px-2">

                                                    <p class="card-text justify-text mb-1" style="color: #545454;">نام مشتری: <strong>{{ $customer->name }}</strong></p>
                                                    <p class="card-text justify-text mb-1" style="color: #545454;">کانال / صنف: <strong>{{ $customer->channel }} / {{ $customer->senf }}</strong></p>
                                                    <p class="card-text address" style="color: #524595;">آدرس: {{ $customer->address }}</p>
                                                </div>
                                            </div>
                                            <div class="col-5 d-flex justify-content-md-end justify-content-center align-items-center pe-md-5">
                                                <button class="btn rounded-pill btn-outline-vimeo waves-effect mb-3 p-2 p-md-3 mb-md-0 btn_adress" type="button">
                                                    <img src="{{ asset('/img/location_icon.png') }}" />    مشاهده جزئیات/نقشه
                                                </button>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                @foreach($Customers as $customer)
                                        <?php $Check = DB::table('pishfactors')->where('task_id', $task->id)->where('customer_id',$customer->id)->count() ?>
                                    <div class="card mb-3 @if($Check > 0) ok @endif" @if($Check > 0) style="background-color: #ebfcf4" @endif>

                                        <div class="card-header pt-2 px-2 pb-0 header-elements">
                                            <h5 class="card-title d-inline-block me-2">{{ $customer->tablo }} </h5>

                                            <div class="card-header-elements ms-auto">
                                                @if($customer->shop_lat != null)
                                                    <span class="badge bg-label-success rounded-pill">لوکیشن دارد</span>
                                                @else
                                                    <span class="badge bg-label-danger rounded-pill">لوکیشن ثبت نشده</span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ asset("/CustomerInfo/") }}/{{ $customer->id }}/MyTaks/{{ $task->id }}" class="row d-flex justify-content-start g-0">
                                            <div class="col-7">
                                                <div class="card-body pt-0 pb-3 px-2">

                                                    <p class="card-text justify-text mb-1" style="color: #545454;">نام مشتری: <strong>{{ $customer->name }}</strong></p>
                                                    <p class="card-text justify-text mb-1" style="color: #545454;">کانال / صنف: <strong>{{ $customer->channel }} / {{ $customer->senf }}</strong></p>
                                                    <p class="card-text address" style="color: #524595;">آدرس: {{ $customer->address }}</p>
                                                </div>
                                            </div>
                                            <div class="col-5 d-flex justify-content-md-end justify-content-center align-items-center pe-md-5">
                                                <button class="btn rounded-pill btn-outline-vimeo waves-effect mb-3 p-2 p-md-3 mb-md-0 btn_adress" type="button">
                                                    <img src="{{ asset('/img/location_icon.png') }}" />    مشاهده جزئیات/نقشه
                                                </button>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
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

<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<script>
    $('.tasks').addClass('open')
    $('.tasks .mytasks').addClass('active open');


    $(document).ready(function() {
        $('#searchInput').on('keyup', function() {
            var searchText = $(this).val().toLowerCase(); // Get search text and convert to lowercase for case-insensitive search

            $('.customers_list .card').each(function() {
                var listItemText = $(this).text().toLowerCase(); // Get list item text and convert to lowercase

                if (listItemText.indexOf(searchText) > -1) {
                    $(this).show(); // Show if text matches
                } else {
                    $(this).hide(); // Hide if text does not match
                }
            });
        });
    });

</script>
</body>

</html>

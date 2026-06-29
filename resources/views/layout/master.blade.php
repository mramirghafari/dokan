<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>پیشخوان دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/swiper/swiper.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>
    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/pages/cards-advance.css" rel="stylesheet"/>
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
</head>

<body>
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
                    <div class="row">
                        <!-- Earning Reports -->
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header pb-0 d-flex justify-content-between mb-lg-n4">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">گزارش فروش</h5>
                                        <small class="text-muted">آمار فروش امروز</small>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-4 mt-md-10 d-flex flex-column align-self-end">
                                            <div class="d-flex gap-2 align-items-center mb-2 pb-1 flex-wrap pt-3">
                                                <h1 class="mb-0 ">
                                                    <bdi><svg class="toman" width="1rem" height="1rem">
                                                            <use xlink:href="#toman">
                                                                <symbol id="toman" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                                                    <path clip-rule="evenodd" d="M3.057 1.742L3.821 1l.78.75-.776.741-.768-.749zm3.23 2.48c0 .622-.16 1.111-.478 1.467-.201.221-.462.39-.783.505a3.251 3.251 0 01-1.083.163h-.555c-.421 0-.801-.074-1.139-.223a2.045 2.045 0 01-.9-.738A2.238 2.238 0 011 4.148c0-.059.001-.117.004-.176.03-.55.204-1.158.525-1.827l1.095.484c-.257.532-.397 1-.419 1.403-.002.04-.004.08-.004.12 0 .252.055.458.166.618a.887.887 0 00.5.354c.085.028.178.048.278.06.079.01.16.014.243.014h.555c.458 0 .769-.081.933-.244.14-.139.21-.383.21-.731V2.02h1.2v2.202zm5.433 3.184l-.72-.7.709-.706.735.707-.724.7zm-2.856.308c.542 0 .973.19 1.293.569.297.346.445.777.445 1.293v.364h.18v-.004h.41c.221 0 .377-.028.467-.084.093-.055.14-.14.14-.258v-.069c.004-.243.017-1.044 0-1.115L13 8.05v1.574a1.4 1.4 0 01-.287.863c-.306.405-.804.607-1.495.607h-.627c-.061.733-.434 1.257-1.117 1.573-.267.122-.58.21-.937.265a5.845 5.845 0 01-.914.067v-1.159c.612 0 1.072-.082 1.38-.247.25-.132.376-.298.376-.499h-.515c-.436 0-.807-.113-1.113-.339-.367-.273-.55-.667-.55-1.18 0-.488.122-.901.367-1.24.296-.415.728-.622 1.296-.622zm.533 2.226v-.364c0-.217-.048-.389-.143-.516a.464.464 0 00-.39-.187.478.478 0 00-.396.187.705.705 0 00-.136.449.65.65 0 00.003.067c.008.125.066.22.177.283.093.054.21.08.352.08h.533zM9.5 6.707l.72.7.724-.7L10.209 6l-.709.707zm-6.694 4.888h.03c.433-.01.745-.106.937-.29.024.012.065.035.12.068l.074.039.081.042c.135.073.261.133.379.18.345.146.67.22.977.22a1.216 1.216 0 00.87-.34c.3-.285.449-.714.449-1.286a2.19 2.19 0 00-.335-1.145c-.299-.457-.732-.685-1.3-.685-.502 0-.916.192-1.242.575-.113.132-.21.284-.294.456-.032.062-.06.125-.084.191a.504.504 0 00-.03.078 1.67 1.67 0 00-.022.06c-.103.309-.171.485-.205.53-.072.09-.214.14-.427.147-.123-.005-.209-.03-.256-.076-.057-.054-.085-.153-.085-.297V7l-1.201-.5v3.562c0 .261.048.496.143.703.071.158.168.296.29.413.123.118.266.211.43.28.198.084.42.13.665.136v.001h.036zm2.752-1.014a.778.778 0 00.044-.353.868.868 0 00-.165-.47c-.1-.134-.217-.201-.35-.201-.18 0-.33.103-.447.31-.042.071-.08.158-.114.262a2.434 2.434 0 00-.04.12l-.015.053-.015.046c.142.118.323.216.544.293.18.062.325.092.433.092.044 0 .086-.05.125-.152z" fill-rule="evenodd"></path>
                                                                </symbol>
                                                            </use>
                                                        </svg>468</bdi>
                                                </h1>
                                                <div class="badge rounded bg-label-success">
                                                    <bdi>+4.2%</bdi>
                                                </div>
                                            </div>
                                            <small>نمودار درآمد شما برای هر روز در هفته جاری قابل مشاهده می باشد.</small>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <div id="weeklyEarningReports"></div>
                                        </div>
                                    </div>
                                    <div class="border rounded p-3 mt-4">
                                        <div class="row gap-4 gap-sm-0">
                                            <div class="col-12 col-sm-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="badge rounded bg-label-primary p-1">
                                                        <x-ui.icon name="currency-dollar" class="ti-sm" />
                                                    </div>
                                                    <h6 class="mb-0">فروش امروز</h6>
                                                </div>
                                                <h4 class="my-2 pt-1">
                                                    <bdi><svg class="toman" width="1rem" height="1rem">
                                                            <use xlink:href="#toman">
                                                                <symbol id="toman" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                                                    <path clip-rule="evenodd" d="M3.057 1.742L3.821 1l.78.75-.776.741-.768-.749zm3.23 2.48c0 .622-.16 1.111-.478 1.467-.201.221-.462.39-.783.505a3.251 3.251 0 01-1.083.163h-.555c-.421 0-.801-.074-1.139-.223a2.045 2.045 0 01-.9-.738A2.238 2.238 0 011 4.148c0-.059.001-.117.004-.176.03-.55.204-1.158.525-1.827l1.095.484c-.257.532-.397 1-.419 1.403-.002.04-.004.08-.004.12 0 .252.055.458.166.618a.887.887 0 00.5.354c.085.028.178.048.278.06.079.01.16.014.243.014h.555c.458 0 .769-.081.933-.244.14-.139.21-.383.21-.731V2.02h1.2v2.202zm5.433 3.184l-.72-.7.709-.706.735.707-.724.7zm-2.856.308c.542 0 .973.19 1.293.569.297.346.445.777.445 1.293v.364h.18v-.004h.41c.221 0 .377-.028.467-.084.093-.055.14-.14.14-.258v-.069c.004-.243.017-1.044 0-1.115L13 8.05v1.574a1.4 1.4 0 01-.287.863c-.306.405-.804.607-1.495.607h-.627c-.061.733-.434 1.257-1.117 1.573-.267.122-.58.21-.937.265a5.845 5.845 0 01-.914.067v-1.159c.612 0 1.072-.082 1.38-.247.25-.132.376-.298.376-.499h-.515c-.436 0-.807-.113-1.113-.339-.367-.273-.55-.667-.55-1.18 0-.488.122-.901.367-1.24.296-.415.728-.622 1.296-.622zm.533 2.226v-.364c0-.217-.048-.389-.143-.516a.464.464 0 00-.39-.187.478.478 0 00-.396.187.705.705 0 00-.136.449.65.65 0 00.003.067c.008.125.066.22.177.283.093.054.21.08.352.08h.533zM9.5 6.707l.72.7.724-.7L10.209 6l-.709.707zm-6.694 4.888h.03c.433-.01.745-.106.937-.29.024.012.065.035.12.068l.074.039.081.042c.135.073.261.133.379.18.345.146.67.22.977.22a1.216 1.216 0 00.87-.34c.3-.285.449-.714.449-1.286a2.19 2.19 0 00-.335-1.145c-.299-.457-.732-.685-1.3-.685-.502 0-.916.192-1.242.575-.113.132-.21.284-.294.456-.032.062-.06.125-.084.191a.504.504 0 00-.03.078 1.67 1.67 0 00-.022.06c-.103.309-.171.485-.205.53-.072.09-.214.14-.427.147-.123-.005-.209-.03-.256-.076-.057-.054-.085-.153-.085-.297V7l-1.201-.5v3.562c0 .261.048.496.143.703.071.158.168.296.29.413.123.118.266.211.43.28.198.084.42.13.665.136v.001h.036zm2.752-1.014a.778.778 0 00.044-.353.868.868 0 00-.165-.47c-.1-.134-.217-.201-.35-.201-.18 0-.33.103-.447.31-.042.071-.08.158-.114.262a2.434 2.434 0 00-.04.12l-.015.053-.015.046c.142.118.323.216.544.293.18.062.325.092.433.092.044 0 .086-.05.125-.152z" fill-rule="evenodd"></path>
                                                                </symbol>
                                                            </use>
                                                        </svg>545.69</bdi>
                                                </h4>
                                                <div class="progress w-75" style="height: 4px">
                                                    <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="65" class="progress-bar" role="progressbar" style="width: 65%"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="badge rounded bg-label-info p-1">
                                                        <x-ui.icon name="chart-pie-2" class="ti-sm" />
                                                    </div>
                                                    <h6 class="mb-0">فروش هفته</h6>
                                                </div>
                                                <h4 class="my-2 pt-1">
                                                    <bdi><svg class="toman" width="1rem" height="1rem">
                                                            <use xlink:href="#toman">
                                                                <symbol id="toman" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                                                    <path clip-rule="evenodd" d="M3.057 1.742L3.821 1l.78.75-.776.741-.768-.749zm3.23 2.48c0 .622-.16 1.111-.478 1.467-.201.221-.462.39-.783.505a3.251 3.251 0 01-1.083.163h-.555c-.421 0-.801-.074-1.139-.223a2.045 2.045 0 01-.9-.738A2.238 2.238 0 011 4.148c0-.059.001-.117.004-.176.03-.55.204-1.158.525-1.827l1.095.484c-.257.532-.397 1-.419 1.403-.002.04-.004.08-.004.12 0 .252.055.458.166.618a.887.887 0 00.5.354c.085.028.178.048.278.06.079.01.16.014.243.014h.555c.458 0 .769-.081.933-.244.14-.139.21-.383.21-.731V2.02h1.2v2.202zm5.433 3.184l-.72-.7.709-.706.735.707-.724.7zm-2.856.308c.542 0 .973.19 1.293.569.297.346.445.777.445 1.293v.364h.18v-.004h.41c.221 0 .377-.028.467-.084.093-.055.14-.14.14-.258v-.069c.004-.243.017-1.044 0-1.115L13 8.05v1.574a1.4 1.4 0 01-.287.863c-.306.405-.804.607-1.495.607h-.627c-.061.733-.434 1.257-1.117 1.573-.267.122-.58.21-.937.265a5.845 5.845 0 01-.914.067v-1.159c.612 0 1.072-.082 1.38-.247.25-.132.376-.298.376-.499h-.515c-.436 0-.807-.113-1.113-.339-.367-.273-.55-.667-.55-1.18 0-.488.122-.901.367-1.24.296-.415.728-.622 1.296-.622zm.533 2.226v-.364c0-.217-.048-.389-.143-.516a.464.464 0 00-.39-.187.478.478 0 00-.396.187.705.705 0 00-.136.449.65.65 0 00.003.067c.008.125.066.22.177.283.093.054.21.08.352.08h.533zM9.5 6.707l.72.7.724-.7L10.209 6l-.709.707zm-6.694 4.888h.03c.433-.01.745-.106.937-.29.024.012.065.035.12.068l.074.039.081.042c.135.073.261.133.379.18.345.146.67.22.977.22a1.216 1.216 0 00.87-.34c.3-.285.449-.714.449-1.286a2.19 2.19 0 00-.335-1.145c-.299-.457-.732-.685-1.3-.685-.502 0-.916.192-1.242.575-.113.132-.21.284-.294.456-.032.062-.06.125-.084.191a.504.504 0 00-.03.078 1.67 1.67 0 00-.022.06c-.103.309-.171.485-.205.53-.072.09-.214.14-.427.147-.123-.005-.209-.03-.256-.076-.057-.054-.085-.153-.085-.297V7l-1.201-.5v3.562c0 .261.048.496.143.703.071.158.168.296.29.413.123.118.266.211.43.28.198.084.42.13.665.136v.001h.036zm2.752-1.014a.778.778 0 00.044-.353.868.868 0 00-.165-.47c-.1-.134-.217-.201-.35-.201-.18 0-.33.103-.447.31-.042.071-.08.158-.114.262a2.434 2.434 0 00-.04.12l-.015.053-.015.046c.142.118.323.216.544.293.18.062.325.092.433.092.044 0 .086-.05.125-.152z" fill-rule="evenodd"></path>
                                                                </symbol>
                                                            </use>
                                                        </svg>256.34</bdi>
                                                </h4>
                                                <div class="progress w-75" style="height: 4px">
                                                    <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="50" class="progress-bar bg-info" role="progressbar" style="width: 50%"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="badge rounded bg-label-danger p-1">
                                                        <x-ui.icon name="brand-paypal" class="ti-sm" />
                                                    </div>
                                                    <h6 class="mb-0">فروش ماه</h6>
                                                </div>
                                                <h4 class="my-2 pt-1">
                                                    <bdi><svg class="toman" width="1rem" height="1rem">
                                                            <use xlink:href="#toman">
                                                                <symbol id="toman" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                                                    <path clip-rule="evenodd" d="M3.057 1.742L3.821 1l.78.75-.776.741-.768-.749zm3.23 2.48c0 .622-.16 1.111-.478 1.467-.201.221-.462.39-.783.505a3.251 3.251 0 01-1.083.163h-.555c-.421 0-.801-.074-1.139-.223a2.045 2.045 0 01-.9-.738A2.238 2.238 0 011 4.148c0-.059.001-.117.004-.176.03-.55.204-1.158.525-1.827l1.095.484c-.257.532-.397 1-.419 1.403-.002.04-.004.08-.004.12 0 .252.055.458.166.618a.887.887 0 00.5.354c.085.028.178.048.278.06.079.01.16.014.243.014h.555c.458 0 .769-.081.933-.244.14-.139.21-.383.21-.731V2.02h1.2v2.202zm5.433 3.184l-.72-.7.709-.706.735.707-.724.7zm-2.856.308c.542 0 .973.19 1.293.569.297.346.445.777.445 1.293v.364h.18v-.004h.41c.221 0 .377-.028.467-.084.093-.055.14-.14.14-.258v-.069c.004-.243.017-1.044 0-1.115L13 8.05v1.574a1.4 1.4 0 01-.287.863c-.306.405-.804.607-1.495.607h-.627c-.061.733-.434 1.257-1.117 1.573-.267.122-.58.21-.937.265a5.845 5.845 0 01-.914.067v-1.159c.612 0 1.072-.082 1.38-.247.25-.132.376-.298.376-.499h-.515c-.436 0-.807-.113-1.113-.339-.367-.273-.55-.667-.55-1.18 0-.488.122-.901.367-1.24.296-.415.728-.622 1.296-.622zm.533 2.226v-.364c0-.217-.048-.389-.143-.516a.464.464 0 00-.39-.187.478.478 0 00-.396.187.705.705 0 00-.136.449.65.65 0 00.003.067c.008.125.066.22.177.283.093.054.21.08.352.08h.533zM9.5 6.707l.72.7.724-.7L10.209 6l-.709.707zm-6.694 4.888h.03c.433-.01.745-.106.937-.29.024.012.065.035.12.068l.074.039.081.042c.135.073.261.133.379.18.345.146.67.22.977.22a1.216 1.216 0 00.87-.34c.3-.285.449-.714.449-1.286a2.19 2.19 0 00-.335-1.145c-.299-.457-.732-.685-1.3-.685-.502 0-.916.192-1.242.575-.113.132-.21.284-.294.456-.032.062-.06.125-.084.191a.504.504 0 00-.03.078 1.67 1.67 0 00-.022.06c-.103.309-.171.485-.205.53-.072.09-.214.14-.427.147-.123-.005-.209-.03-.256-.076-.057-.054-.085-.153-.085-.297V7l-1.201-.5v3.562c0 .261.048.496.143.703.071.158.168.296.29.413.123.118.266.211.43.28.198.084.42.13.665.136v.001h.036zm2.752-1.014a.778.778 0 00.044-.353.868.868 0 00-.165-.47c-.1-.134-.217-.201-.35-.201-.18 0-.33.103-.447.31-.042.071-.08.158-.114.262a2.434 2.434 0 00-.04.12l-.015.053-.015.046c.142.118.323.216.544.293.18.062.325.092.433.092.044 0 .086-.05.125-.152z" fill-rule="evenodd"></path>
                                                                </symbol>
                                                            </use>
                                                        </svg>74.19</bdi>
                                                </h4>
                                                <div class="progress w-75" style="height: 4px">
                                                    <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="65" class="progress-bar bg-danger" role="progressbar" style="width: 65%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ Earning Reports -->
                        <!-- Support Tracker -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between pb-0">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">تارگت فروش امروز</h5>
                                        <small class="text-muted">مجموع تارگت های ثبت شده برای امروز</small>
                                    </div>
                                    <div class="dropdown">
                                        <button aria-expanded="false" aria-haspopup="true" class="btn p-0" data-bs-toggle="dropdown" id="supportTrackerMenu" type="button">
                                            <x-ui.icon name="dots-vertical" class="ti-sm text-muted" />
                                        </button>
                                        <div aria-labelledby="supportTrackerMenu" class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس تعداد فروش</a>
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس مبلغ فروش</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-sm-4 col-md-12 col-lg-4">
                                            <div class="mt-lg-4 mt-lg-2 mb-lg-4 mb-2 pt-3">
                                                <h2 class="mb-0 lh-80p">820,000,000</h2>
                                                <p class="mb-0">تارگت امروز</p>
                                            </div>
                                            <ul class="p-0 m-0">
                                                <li class="d-flex gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                                                    <div class="badge rounded bg-label-primary p-1">
                                                        <x-ui.icon name="ticket" class="ti-sm" />
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">مسیرهای فعال</h6>
                                                        <small class="text-muted">9</small>
                                                    </div>
                                                </li>
                                                <li class="d-flex gap-3 align-items-center mb-lg-3 pb-1">
                                                    <div class="badge rounded bg-label-info p-1">
                                                        <x-ui.icon name="circle-check" class="ti-sm" />
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تعداد مشتریان</h6>
                                                        <small class="text-muted">210</small>
                                                    </div>
                                                </li>
                                                <li class="d-flex gap-3 align-items-center pb-1">
                                                    <div class="badge rounded bg-label-warning p-1">
                                                        <x-ui.icon name="clock" class="ti-sm" />
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">فاکتورهای ثبت شده</h6>
                                                        <small class="text-muted">36 عدد</small>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-12 col-sm-8 col-md-12 col-lg-8">
                                            <div id="supportTracker"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--/ Support Tracker -->
                        <!-- Sales By Country -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title mb-0">
                                        <h5 class="m-0 me-2">فروش بر اساس محصول</h5>
                                        <small class="text-muted">پر فروش ترین محصولات روز</small>
                                    </div>
                                    <div class="dropdown">
                                        <button aria-expanded="false" aria-haspopup="true" class="btn p-0" data-bs-toggle="dropdown" id="salesByCountry" type="button">
                                            <x-ui.icon name="dots-vertical" class="ti-sm text-muted" />
                                        </button>
                                        <div aria-labelledby="salesByCountry" class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس روز</a>
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس هفته</a>
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس ماه</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="p-0 m-0">
                                        <li class="d-flex align-items-center mb-4">
                                            <img alt="User" class="rounded-circle me-3" src="{{ asset('assets/') }}/img/x4-2.jpg" width="40"/>
                                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="me-2">
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="mb-0 me-1">
                                                            دستمال دلسی 2 قلو
                                                        </h6>
                                                    </div>
                                                    <small class="text-muted m-0">27,000 {{ currency_label() }}</small>
                                                </div>
                                                <div class="user-progress">
                                                    <p class="text-success fw-medium mb-0 d-flex justify-content-center gap-1">
                                                        181 عدد فروش
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-center mb-4">
                                            <img alt="User" class="rounded-circle me-3" src="{{ asset('assets/') }}/img/x4-2.jpg" width="40"/>
                                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="me-2">
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="mb-0 me-1">
                                                            دستمال دلسی 2 قلو
                                                        </h6>
                                                    </div>
                                                    <small class="text-muted m-0">27,000 {{ currency_label() }}</small>
                                                </div>
                                                <div class="user-progress">
                                                    <p class="text-success fw-medium mb-0 d-flex justify-content-center gap-1">
                                                        181 عدد فروش
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-center mb-4">
                                            <img alt="User" class="rounded-circle me-3" src="{{ asset('assets/') }}/img/x4-2.jpg" width="40"/>
                                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="me-2">
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="mb-0 me-1">
                                                            دستمال دلسی 2 قلو
                                                        </h6>
                                                    </div>
                                                    <small class="text-muted m-0">27,000 {{ currency_label() }}</small>
                                                </div>
                                                <div class="user-progress">
                                                    <p class="text-success fw-medium mb-0 d-flex justify-content-center gap-1">
                                                        181 عدد فروش
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-center mb-4">
                                            <img alt="User" class="rounded-circle me-3" src="{{ asset('assets/') }}/img/x4-2.jpg" width="40"/>
                                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="me-2">
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="mb-0 me-1">
                                                            دستمال دلسی 2 قلو
                                                        </h6>
                                                    </div>
                                                    <small class="text-muted m-0">27,000 {{ currency_label() }}</small>
                                                </div>
                                                <div class="user-progress">
                                                    <p class="text-success fw-medium mb-0 d-flex justify-content-center gap-1">
                                                        181 عدد فروش
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-center mb-4">
                                            <img alt="User" class="rounded-circle me-3" src="{{ asset('assets/') }}/img/x4-2.jpg" width="40"/>
                                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="me-2">
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="mb-0 me-1">
                                                            دستمال دلسی 2 قلو
                                                        </h6>
                                                    </div>
                                                    <small class="text-muted m-0">27,000 {{ currency_label() }}</small>
                                                </div>
                                                <div class="user-progress">
                                                    <p class="text-success fw-medium mb-0 d-flex justify-content-center gap-1">
                                                        181 عدد فروش
                                                    </p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--/ Sales By Country -->

                        <!-- Monthly Campaign State -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">سرپرست های برتر هفته</h5>
                                        <small class="text-muted">فعال ترین سرپرست های هفته</small>
                                    </div>
                                    <div class="dropdown">
                                        <button aria-expanded="false" aria-haspopup="true" class="btn p-0" data-bs-toggle="dropdown" id="MonthlyCampaign" type="button">
                                            <x-ui.icon name="dots-vertical" class="ti-sm text-muted" />
                                        </button>
                                        <div aria-labelledby="MonthlyCampaign" class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس روز</a>
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس هفته</a>
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس ماه</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="p-0 m-0">
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-success rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">سیده لیلا محمودی</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-info rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">هادی خاوری</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-success rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">سیده لیلا محمودی</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-info rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">هادی خاوری</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-success rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">سیده لیلا محمودی</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--/ Monthly Campaign State -->

                        <!-- Monthly Campaign State -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">بازاریاب های برتر هفته</h5>
                                        <small class="text-muted">فعال ترین بازاریاب های مجموعه</small>
                                    </div>
                                    <div class="dropdown">
                                        <button aria-expanded="false" aria-haspopup="true" class="btn p-0" data-bs-toggle="dropdown" id="MonthlyCampaign" type="button">
                                            <x-ui.icon name="dots-vertical" class="ti-sm text-muted" />
                                        </button>
                                        <div aria-labelledby="MonthlyCampaign" class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس روز</a>
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس هفته</a>
                                            <a class="dropdown-item" href="javascript:void(0);">بر اساس ماه</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="p-0 m-0">
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-success rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">سیده لیلا محمودی</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-info rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">هادی خاوری</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-success rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">سیده لیلا محمودی</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-info rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">هادی خاوری</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                            <div class="badge bg-label-success rounded p-2">
                                                <x-ui.icon name="user" class="ti-sm" />
                                            </div>
                                            <div class="d-flex justify-content-between w-100 flex-wrap">
                                                <h6 class="mb-0 ms-3">سیده لیلا محمودی</h6>
                                                <div class="d-flex">
                                                    <p class="mb-0 fw-medium">140 فاکتور</p>
                                                    <p class="ms-3 text-success mb-0">80 تحویل شده</p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--/ Monthly Campaign State -->
                        <!-- Projects table -->
                        <div class="col-12 col-sm-12 order-1 order-lg-2 mb-5 mb-lg-0">

                            <div class="card">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">مسیرهای فعال</h5>
                                    </div>
                                </div>
                                <div class="card-datatable table-responsive pt-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>مسیر</th>
                                            <th>ناحیه</th>
                                            <th>منطقه</th>
                                            <th>سرپرست</th>
                                            <th>بازاریاب</th>
                                            <th>تعداد مشتریان</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><bdi>+1</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/us.svg" width="32">
                                            </td>
                                            <td>
                                                آمریکا
                                            </td>
                                            <td>
                                                9,834,000
                                            </td>
                                            <td>
                                                331,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+61</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/au.svg" width="32">
                                            </td>
                                            <td>
                                                استرالیا
                                            </td>
                                            <td>
                                                7,688,000
                                            </td>
                                            <td>
                                                25,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+55</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/br.svg" width="32">
                                            </td>
                                            <td>
                                                برزیل
                                            </td>
                                            <td>
                                                8,512,000
                                            </td>
                                            <td>
                                                214,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+33</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/fr.svg" width="32">
                                            </td>
                                            <td>
                                                فرانسه
                                            </td>
                                            <td>
                                                643,801
                                            </td>
                                            <td>
                                                68,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+351</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/pt.svg" width="32">
                                            </td>
                                            <td>
                                                پرتغال
                                            </td>
                                            <td>
                                                92,152
                                            </td>
                                            <td>
                                                10,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+86</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/cn.svg" width="32">
                                            </td>
                                            <td>
                                                چین
                                            </td>
                                            <td>
                                                9,597,000
                                            </td>
                                            <td>
                                                1,412,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+91</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/in.svg" width="32">
                                            </td>
                                            <td>
                                                هند
                                            </td>
                                            <td>
                                                3,287,000
                                            </td>
                                            <td>
                                                1,408,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!--/ Projects table -->
                    </div>
                    <div class="row mt-5">
                        <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">گزارشات</h5>
                                    </div>
                                </div>
                                <div class="card-datatable table-responsive pt-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>مسیر</th>
                                            <th>ناحیه</th>
                                            <th>منطقه</th>
                                            <th>سرپرست</th>
                                            <th>بازاریاب</th>
                                            <th>تعداد مشتریان</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><bdi>+1</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/us.svg" width="32">
                                            </td>
                                            <td>
                                                آمریکا
                                            </td>
                                            <td>
                                                9,834,000
                                            </td>
                                            <td>
                                                331,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+61</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/au.svg" width="32">
                                            </td>
                                            <td>
                                                استرالیا
                                            </td>
                                            <td>
                                                7,688,000
                                            </td>
                                            <td>
                                                25,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+55</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/br.svg" width="32">
                                            </td>
                                            <td>
                                                برزیل
                                            </td>
                                            <td>
                                                8,512,000
                                            </td>
                                            <td>
                                                214,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+33</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/fr.svg" width="32">
                                            </td>
                                            <td>
                                                فرانسه
                                            </td>
                                            <td>
                                                643,801
                                            </td>
                                            <td>
                                                68,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+351</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/pt.svg" width="32">
                                            </td>
                                            <td>
                                                پرتغال
                                            </td>
                                            <td>
                                                92,152
                                            </td>
                                            <td>
                                                10,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+86</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/cn.svg" width="32">
                                            </td>
                                            <td>
                                                چین
                                            </td>
                                            <td>
                                                9,597,000
                                            </td>
                                            <td>
                                                1,412,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><bdi>+91</bdi></td>
                                            <td>
                                                <img alt="User" class="rounded-circle" src="../../assets/svg/flags/in.svg" width="32">
                                            </td>
                                            <td>
                                                هند
                                            </td>
                                            <td>
                                                3,287,000
                                            </td>
                                            <td>
                                                1,408,000,000
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="javascript:;" class="dropdown-item">جزئیات</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">بایگانی</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                <a href="javascript:;" class="btn btn-sm btn-icon item-edit"><x-ui.icon name="pencil" class="text-primary" /></a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script><script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<!-- endbuild -->
@include('partials.ui-icons-runtime')
<!-- Vendors JS -->
<script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/swiper/swiper.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/dashboards-analytics.js"></script>
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

</script>
</body>

</html>

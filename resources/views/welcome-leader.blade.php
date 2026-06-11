<?php

use Hekmatinasser\Verta\Verta; ?>
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پیشخوان دکان دارمینو</title>
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
    <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/swiper/swiper.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />
    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/pages/cards-advance.css" rel="stylesheet" />
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        @media(max-width: 768px) {
            .svgicon {
                width: 60%;
                margin-left: 8px;
                height: auto;
                margin-bottom: 15px;
            }

            .dataTables_filter .form-control {
                width: 190px !important;
            }
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')
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
                            @if (\App\Services\TenantSettings::enabled('feature_route_management'))
                                <div class="mb-4 col-sm-6 col-12">
                                    <a class="btn btn-primary waves-effect waves-light w-100"
                                        href="{{ route('tasks.create') }}"><i class="ti ti-plus me-md-2"></i> ثبت مسیر
                                        جدید</a>
                                </div>
                            @endif
                            <div class="mb-4 col-sm-6 col-12">
                                <a class="btn btn-primary waves-effect waves-light w-100"
                                    href="{{ route('products.index') }}"><i class="ti ti-plus me-md-2"></i> ثبت سفارش
                                    جدید</a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between py-2">
                                        <div class="card-title mb-0">
                                            <h5 class="m-0 me-2">اطلاعات شما</h5>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-borderless border-top">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div
                                                            class="d-flex justify-content-start align-items-center mt-lg-2">
                                                            <div class="avatar me-3 avatar-sm">
                                                                <img alt="آواتار" class="rounded-circle"
                                                                    src="{{ asset('assets/') }}/img/avatars/1.png">
                                                            </div>
                                                            <div class="d-flex flex-column">
                                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                                @foreach ($user->roles as $role)
                                                                    <small
                                                                        class="text-truncate text-muted">{{ $role->description }}</small>
                                                                @endforeach

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <button
                                                            class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2"
                                                            type="button">
                                                            <span class="text-dark">
                                                                <svg width="20" height="23" viewBox="0 0 12 15"
                                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.50033 3.5C8.50033 4.16304 8.23691 4.79892 7.768 5.26776C7.2991 5.7366 6.66313 6 6 6C5.33687 6 4.7009 5.7366 4.232 5.26776C3.76309 4.79892 3.49967 4.16304 3.49967 3.5C3.49967 2.83696 3.76309 2.20107 4.232 1.73223C4.7009 1.26339 5.33687 1 6 1C6.66313 1 7.2991 1.26339 7.768 1.73223C8.23691 2.20107 8.50033 2.83696 8.50033 3.5ZM1 12.912C1.02143 11.6002 1.55763 10.3494 2.49298 9.42936C3.42833 8.50928 4.68788 7.99364 6 7.99364C7.31212 7.99364 8.57166 8.50928 9.50702 9.42936C10.4424 10.3494 10.9786 11.6002 11 12.912C9.43138 13.6312 7.72566 14.0023 6 14C4.21576 14 2.5222 13.6107 1 12.912Z"
                                                                        stroke="#543C92" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>

                                                                مدیر فروش:
                                                            </span>
                                                            <?php $Leader = App\Models\User::find(auth()->user()->leader_id); ?>
                                                            <strong
                                                                class="text-dark">{{ isset($Leader) ?? $Leader->name }}</strong>
                                                        </button>
                                                        <a href="{{ route('tasks.MyTasks') }}"
                                                            class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between">
                                                            <span class="text-dark">
                                                                <svg width="20" height="20" viewBox="0 0 14 14"
                                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M5 3.44728V9.02971M9 4.96976V10.5522M9.33533 12.9191L12.5853 11.2701C12.8393 11.1416 13 10.8777 13 10.5894V2.14133C13 1.57564 12.4133 1.20754 11.9147 1.46061L9.33533 2.76927C9.124 2.87685 8.87533 2.87685 8.66467 2.76927L5.33533 1.08033C5.23121 1.0275 5.1164 1 5 1C4.8836 1 4.76879 1.0275 4.66467 1.08033L1.41467 2.72934C1.16 2.85859 1 3.12248 1 3.41006V11.8581C1 12.4238 1.58667 12.7919 2.08533 12.5389L4.66467 11.2302C4.876 11.1226 5.12467 11.1226 5.33533 11.2302L8.66467 12.9198C8.876 13.0267 9.12467 13.0267 9.33533 12.9198V12.9191Z"
                                                                        stroke="#543C92" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>

                                                                مسیر های فعال: </span>
                                                            <strong class="text-dark">{{ count($MyTasks) }} مسیر
                                                            </strong>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @if ($Target)
                                                    <tr>
                                                        <td>
                                                            <button
                                                                class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2"
                                                                type="button">
                                                                <span class="text-dark">
                                                                    <svg width="21" height="24"
                                                                        viewBox="0 0 21 24" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M7.04545 12H11.3636M7.04545 15.3846H11.3636M7.04545 18.7692H11.3636M14.8182 19.6154H17.4091C18.0962 19.6154 18.7552 19.3479 19.2411 18.8719C19.727 18.3958 20 17.7502 20 17.0769V5.35262C20 4.0721 19.027 2.98564 17.7246 2.87959C17.2939 2.8446 16.8628 2.81451 16.4315 2.78933M16.4315 2.78933C16.5079 3.03199 16.5455 3.28451 16.5455 3.53846C16.5455 3.76288 16.4545 3.9781 16.2925 4.13678C16.1305 4.29547 15.9109 4.38462 15.6818 4.38462H10.5C10.0233 4.38462 9.63636 4.00554 9.63636 3.53846C9.63636 3.27785 9.67667 3.02626 9.75152 2.78933M16.4315 2.78933C16.1056 1.75364 15.1199 1 13.9545 1H12.2273C11.6737 1.00013 11.1347 1.17391 10.6891 1.49589C10.2436 1.81787 9.91506 2.27115 9.75152 2.78933M9.75152 2.78933C9.31855 2.81528 8.88788 2.84574 8.45721 2.87959C7.15485 2.98564 6.18182 4.0721 6.18182 5.35262V7.76923M6.18182 7.76923H2.29545C1.58036 7.76923 1 8.33785 1 9.03846V21.7308C1 22.4314 1.58036 23 2.29545 23H13.5227C14.2378 23 14.8182 22.4314 14.8182 21.7308V9.03846C14.8182 8.33785 14.2378 7.76923 13.5227 7.76923H6.18182ZM4.45455 12H4.46376V12.009H4.45455V12ZM4.45455 15.3846H4.46376V15.3936H4.45455V15.3846ZM4.45455 18.7692H4.46376V18.7783H4.45455V18.7692Z"
                                                                            stroke="#524595" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                    تاریخ شروع تارگت
                                                                </span>
                                                                <strong
                                                                    class="text-dark">{{ $Target->start_date_fa }}</strong>
                                                            </button>
                                                            <button
                                                                class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2"
                                                                type="button">
                                                                <span class="text-dark">
                                                                    <svg width="21" height="24"
                                                                        viewBox="0 0 21 24" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M7.04545 12H11.3636M7.04545 15.3846H11.3636M7.04545 18.7692H11.3636M14.8182 19.6154H17.4091C18.0962 19.6154 18.7552 19.3479 19.2411 18.8719C19.727 18.3958 20 17.7502 20 17.0769V5.35262C20 4.0721 19.027 2.98564 17.7246 2.87959C17.2939 2.8446 16.8628 2.81451 16.4315 2.78933M16.4315 2.78933C16.5079 3.03199 16.5455 3.28451 16.5455 3.53846C16.5455 3.76288 16.4545 3.9781 16.2925 4.13678C16.1305 4.29547 15.9109 4.38462 15.6818 4.38462H10.5C10.0233 4.38462 9.63636 4.00554 9.63636 3.53846C9.63636 3.27785 9.67667 3.02626 9.75152 2.78933M16.4315 2.78933C16.1056 1.75364 15.1199 1 13.9545 1H12.2273C11.6737 1.00013 11.1347 1.17391 10.6891 1.49589C10.2436 1.81787 9.91506 2.27115 9.75152 2.78933M9.75152 2.78933C9.31855 2.81528 8.88788 2.84574 8.45721 2.87959C7.15485 2.98564 6.18182 4.0721 6.18182 5.35262V7.76923M6.18182 7.76923H2.29545C1.58036 7.76923 1 8.33785 1 9.03846V21.7308C1 22.4314 1.58036 23 2.29545 23H13.5227C14.2378 23 14.8182 22.4314 14.8182 21.7308V9.03846C14.8182 8.33785 14.2378 7.76923 13.5227 7.76923H6.18182ZM4.45455 12H4.46376V12.009H4.45455V12ZM4.45455 15.3846H4.46376V15.3936H4.45455V15.3846ZM4.45455 18.7692H4.46376V18.7783H4.45455V18.7692Z"
                                                                            stroke="#524595" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                    تاریخ پایان تارگت
                                                                </span>
                                                                <strong
                                                                    class="text-dark">{{ $Target->end_date_fa }}</strong>
                                                            </button>
                                                            <a href="{{ route('tasks.MyTasks') }}"
                                                                class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between">
                                                                <span class="text-dark">
                                                                    <svg width="20" height="20"
                                                                        viewBox="0 0 14 14" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M5 3.44728V9.02971M9 4.96976V10.5522M9.33533 12.9191L12.5853 11.2701C12.8393 11.1416 13 10.8777 13 10.5894V2.14133C13 1.57564 12.4133 1.20754 11.9147 1.46061L9.33533 2.76927C9.124 2.87685 8.87533 2.87685 8.66467 2.76927L5.33533 1.08033C5.23121 1.0275 5.1164 1 5 1C4.8836 1 4.76879 1.0275 4.66467 1.08033L1.41467 2.72934C1.16 2.85859 1 3.12248 1 3.41006V11.8581C1 12.4238 1.58667 12.7919 2.08533 12.5389L4.66467 11.2302C4.876 11.1226 5.12467 11.1226 5.33533 11.2302L8.66467 12.9198C8.876 13.0267 9.12467 13.0267 9.33533 12.9198V12.9191Z"
                                                                            stroke="#543C92" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>

                                                                    فاکتورهای ثبت شده: </span>
                                                                <strong class="text-dark">{{ $AllFactorCount }} فاکتور
                                                                </strong>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between py-2">
                                        <div class="card-title mb-0">
                                            <h5 class="m-0 me-2">جزئیات تارگت</h5>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <div class="text-center px-3">
                                            <circle-progress text-format="vertical" indeterminateText="تارگت شما"
                                                max="{{ $Target ? $Target->target_price : 0 }}"
                                                value="{{ $AllFactorPrices }}"></circle-progress>
                                            <p class="text-success" style="font-size: 13px;font-weight: bold">تا این
                                                لحظه {{ number_format($AllFactorPrices) }} ریال از تارگت خود را به دست
                                                آورده اید.</p>
                                            <p class="text-warning" style="font-size: 13px">
                                                هنوز
                                                <strong>{{ $Target ? verta("$Target->end_date_en")->diffDays() : 0 }}</strong>
                                                روز برای به پایان رساندن تارگت این ماهتان زمان دارید.
                                            </p>
                                        </div>
                                        <div class="card green m-3">
                                            <div class="card-header py-2" style="background-color: #248230">
                                                <div class="card-title mb-0 text-white text-center">
                                                    تارگت این ماه: <strong
                                                        style="font-size: 18px">{{ $Target ? number_format($Target->target_price) : 0 }}</strong>
                                                    <small>ریال</small>
                                                </div>
                                            </div>
                                            <div class="card-body  pb-0"
                                                style="background-color: rgba(36,130,48,0.1)">
                                                <p class="text-danger text-center my-2">کسر شده از تارگت: <strong
                                                        style="display: inline-block;direction: ltr"> -
                                                        {{ $Target ? number_format($AllFactorPrices) : 0 }}</strong>
                                                </p>
                                                <?php if ($Target) {
                                                    $Mande = intval($Target->target_price) - intval($AllFactorPrices);
                                                } ?>
                                                <p class="text-success text-center">مانده به ریال: <strong
                                                        style="display: inline-block;direction: ltr">
                                                        {{ $Target ? number_format($Mande) : 0 }}</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php /*
                    <div class="row">

                        @if($Target)
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
                                                    <bdi> ریال{{ number_format($todaySum) }}</bdi>
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
                                                        <i class="ti ti-currency-dollar ti-sm"></i>
                                                    </div>
                                                    <h6 class="mb-0">فروش امروز</h6>
                                                </div>
                                                <h4 class="my-2 pt-1">
                                                    <bdi>{{ number_format($todaySum) }} <small>ریال</small></bdi>
                                                </h4>
                                                <div class="progress w-75" style="height: 4px">
                                                    <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="65" class="progress-bar" role="progressbar" style="width: 65%"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="badge rounded bg-label-info p-1">
                                                        <i class="ti ti-chart-pie-2 ti-sm"></i>
                                                    </div>
                                                    <h6 class="mb-0">فروش هفته</h6>
                                                </div>
                                                <h4 class="my-2 pt-1">
                                                    <bdi>{{ number_format($weekSum) }} <small>ریال</small></bdi>
                                                </h4>
                                                <div class="progress w-75" style="height: 4px">
                                                    <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="50" class="progress-bar bg-info" role="progressbar" style="width: 50%"></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="badge rounded bg-label-danger p-1">
                                                        <i class="ti ti-brand-paypal ti-sm"></i>
                                                    </div>
                                                    <h6 class="mb-0">فروش ماه</h6>
                                                </div>
                                                <h4 class="my-2 pt-1">
                                                    <bdi>{{ number_format($monthSum) }} <small>ریال</small></bdi>
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
                                        <h5 class="mb-0">تارگت فروش </h5>
                                        <small class="text-muted">گزارش تارگت ثبت شده</small>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-sm-5 col-md-12 col-lg-5">
                                            <div class="mt-lg-4 mt-lg-2 mb-lg-4 mb-2 pt-3">
                                                <h2 class="mb-0 lh-80p" style="font-size: 30px">{{ number_format($Target->target_price) }}</h2>
                                                <p class="mb-0">تارگت کل</p>
                                            </div>
                                            <ul class="row p-0 m-0">
                                                <li class="d-flex col-12 gap-3 align-items-center mb-lg-3 pb-1">
                                                    <div class="badge rounded bg-label-success p-1">
                                                        <i class="ti ti-circle-check ti-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تاریخ شروع و پایان</h6>
                                                        <small class="text-muted">{{ $Target->start_date_fa }} تا {{ $Target->end_date_fa }}</small>
                                                    </div>
                                                </li>
                                                <li class="d-flex col-12 gap-3 align-items-center pb-1">
                                                    <div class="badge rounded bg-label-danger p-1">
                                                        <i class="ti ti-clock ti-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تاریخ پایان</h6>
                                                        <small class="text-muted">{{ $Target->end_date_fa }}</small>
                                                    </div>
                                                </li>
                                                <li class="d-flex col-12 gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                                                    <div class="badge rounded bg-label-primary p-1">
                                                        <i class="ti ti-target ti-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تعداد فاکتورهای تعیین شده</h6>
                                                        @if($Target->orders_count > 0)
                                                            <span class="badge bg-label-primary me-1">{{ $Target->orders_count }} فاکتور </span>
                                                        @else
                                                            <span class="badge bg-label-info me-1">تعداد فاکتور مشخص نشده</span>
                                                        @endif
                                                    </div>
                                                </li>
                                                <li class="d-flex col-12 gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                                                    <div class="badge rounded bg-label-success p-1">
                                                        <i class="ti ti-star ti-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تعداد فاکتورهای ثبت شده</h6>
                                                        <span class="badge bg-label-success me-1">{{ $AllFactorCount }} فاکتور </span>
                                                    </div>
                                                </li>
                                                <li class="d-flex col-12 col-md-12 gap-3 align-items-center pb-1">
                                                    <div class="badge rounded bg-label-success p-1">
                                                        <i class="ti ti-moneybag ti-sm"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">مجموع درآمد فاکتورها</h6>
                                                        <strong class="text-darg">{{ number_format($AllFactorPrices) }} ریال</strong>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-12 col-sm-7 col-md-12 col-lg-7">
                                            <div id="supportTracker"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                                <div class="col-12 card mb-4">
                                    <div class="card-body">
                                        <p class="text-center">تارگت فروش برای شما مشخص نشده است.</p>
                                    </div>
                                </div>
                        @endif

                    </div> */
                        ?>

                        <div class="row mb-3">
                            <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div class="card-header d-flex justify-content-between py-3">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">
                                                <svg width="22" height="21" viewBox="0 0 22 21"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M14 17.8057C14.853 18.0534 15.7368 18.1786 16.625 18.1777C18.0534 18.1798 19.4633 17.8541 20.746 17.2257C20.7839 16.3294 20.5286 15.4452 20.0188 14.7071C19.509 13.9689 18.7724 13.4171 17.9207 13.1352C17.0691 12.8534 16.1487 12.8569 15.2992 13.1451C14.4497 13.4334 13.7173 13.9908 13.213 14.7327M14 17.8057V17.8027C14 16.6897 13.714 15.6427 13.213 14.7327M14 17.8057V17.9117C12.0755 19.0708 9.87064 19.6815 7.62402 19.6777C5.29302 19.6777 3.11202 19.0327 1.25002 17.9117L1.24902 17.8027C1.24826 16.3872 1.71864 15.0117 2.58601 13.893C3.45338 12.7743 4.6684 11.9762 6.03951 11.6243C7.41063 11.2725 8.85985 11.387 10.1587 11.9498C11.4575 12.5126 12.5321 13.4917 13.213 14.7327M11 5.05273C11 5.94784 10.6444 6.80628 10.0115 7.43922C9.37857 8.07215 8.52013 8.42773 7.62502 8.42773C6.72992 8.42773 5.87147 8.07215 5.23854 7.43922C4.6056 6.80628 4.25002 5.94784 4.25002 5.05273C4.25002 4.15763 4.6056 3.29918 5.23854 2.66625C5.87147 2.03331 6.72992 1.67773 7.62502 1.67773C8.52013 1.67773 9.37857 2.03331 10.0115 2.66625C10.6444 3.29918 11 4.15763 11 5.05273ZM19.25 7.30273C19.25 7.99893 18.9735 8.66661 18.4812 9.15889C17.9889 9.65117 17.3212 9.92773 16.625 9.92773C15.9288 9.92773 15.2612 9.65117 14.7689 9.15889C14.2766 8.66661 14 7.99893 14 7.30273C14 6.60654 14.2766 5.93886 14.7689 5.44658C15.2612 4.9543 15.9288 4.67773 16.625 4.67773C17.3212 4.67773 17.9889 4.9543 18.4812 5.44658C18.9735 5.93886 19.25 6.60654 19.25 7.30273Z"
                                                        stroke="#543C92" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                وضعیت بازاریاب ها
                                            </h5>
                                        </div>
                                        <div class="col-6 px-2">
                                            <form method="post" action="" class="row" id="date_filter">
                                                <div class="col-5 px-0 pe-1">
                                                    <input type="text" class="form-control" placeholder="از تاریخ"
                                                        data-jdp>
                                                </div>
                                                <div class="col-5 px-0 pe-1">
                                                    <input type="text" class="form-control" placeholder="تا تاریخ"
                                                        data-jdp>
                                                </div>
                                                <div class="col-2 px-0">
                                                    <button class="btn btn-success waves-effect waves-light px-2"
                                                        type="submit">
                                                        فیلتر
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="card-datatable table-responsive purplebox tablelist py-0">
                                        <table class="datatables-direct-basic purple table">
                                            <thead>
                                                <tr>
                                                    <th width="20">#</th>
                                                    <th>نام بازاریاب</th>
                                                    <th>کد بازاریاب</th>
                                                    <th>مشتریان</th>
                                                    <th>تعداد فاکتور</th>
                                                    <th>مبلغ کل فاکتور</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($MyVisitors as $visitor)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td><small data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ $visitor['name'] }}"><a
                                                                    href="{{ route('userInvoiceList', $visitor['id']) }}">{{ strlen($visitor['name']) > 12 ? mb_substr($visitor['name'], 0, 15, 'UTF-8') . '...' : $visitor['name'] }}</a>
                                                            </small></td>
                                                        <td><small>{{ $visitor['username'] }}</small></td>
                                                        <td>
                                                            <?php
                                                            $VisitorTasks = DB::table('tasks')->where('user_id', $visitor['id'])->pluck('area_id');
                                                            $VisitorAreas = DB::table('areas')->whereIn('id', $VisitorTasks)->pluck('id');
                                                            $VisitorCustomersCount = DB::table('customers')->whereIn('area', $VisitorAreas)->count();
                                                            ?>
                                                            <small>{{ $VisitorCustomersCount }}</small>

                                                        </td>
                                                        <td>
                                                            <small>{{ $visitor['factors_count'] }}</small>
                                                        </td>
                                                        <td><small>{{ number_format($visitor['FactorPrices']) }}</small>
                                                        </td>
                                                        <td>
                                                            @if ($visitor['isActive'] == 1)
                                                                <span class="badge bg-label-success me-1">فعال</span>
                                                            @else
                                                                <span class="badge bg-label-danger me-1">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php($x++)
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">
                                                <svg width="21" height="20" viewBox="0 0 21 20"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M0.770508 1.09766H2.15651C2.66651 1.09766 3.11151 1.44066 3.24351 1.93266L3.62651 3.36966M3.62651 3.36966C9.19715 3.21354 14.7624 3.83281 20.1625 5.20966C19.3385 7.66366 18.3595 10.0477 17.2385 12.3477H6.02051M3.62651 3.36966L6.02051 12.3477M6.02051 12.3477C5.22486 12.3477 4.4618 12.6637 3.89919 13.2263C3.33658 13.7889 3.02051 14.552 3.02051 15.3477H18.7705M4.52051 18.3477C4.52051 18.5466 4.44149 18.7373 4.30084 18.878C4.16019 19.0186 3.96942 19.0977 3.77051 19.0977C3.5716 19.0977 3.38083 19.0186 3.24018 18.878C3.09953 18.7373 3.02051 18.5466 3.02051 18.3477C3.02051 18.1487 3.09953 17.958 3.24018 17.8173C3.38083 17.6767 3.5716 17.5977 3.77051 17.5977C3.96942 17.5977 4.16019 17.6767 4.30084 17.8173C4.44149 17.958 4.52051 18.1487 4.52051 18.3477ZM17.2705 18.3477C17.2705 18.5466 17.1915 18.7373 17.0508 18.878C16.9102 19.0186 16.7194 19.0977 16.5205 19.0977C16.3216 19.0977 16.1308 19.0186 15.9902 18.878C15.8495 18.7373 15.7705 18.5466 15.7705 18.3477C15.7705 18.1487 15.8495 17.958 15.9902 17.8173C16.1308 17.6767 16.3216 17.5977 16.5205 17.5977C16.7194 17.5977 16.9102 17.6767 17.0508 17.8173C17.1915 17.958 17.2705 18.1487 17.2705 18.3477Z"
                                                        stroke="#248230" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                سفارشات
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="card-datatable table-responsive tablelist py-0">
                                        <table class="datatables-direct-basic table table-wrap">
                                            <thead>
                                                <tr>
                                                    <th width="20">#</th>
                                                    <th class="big">نام مشتری</th>
                                                    <th class="big">کد مشتری</th>
                                                    <th>شماره سفارش</th>
                                                    <th>وضعیت</th>
                                                    <th class="big">تاریخ ثبت</th>
                                                    <th>تعداد اقلام</th>
                                                    <th class="big">مبلغ کل <small>ریال</small></th>
                                                    <th class="big">اطلاعات پرداخت</th>
                                                    <th>جزئیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($Factors as $factor)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td class="big">
                                                            <small data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ $factor->customer->name }}">
                                                                {{ strlen($factor->customer->name) > 12 ? mb_substr($factor->customer->name, 0, 15, 'UTF-8') . '...' : $factor->customer->name }}</small>
                                                        </td>
                                                        <td class="big">
                                                            <small>{{ $factor->customer->customer_code }}</small>
                                                        </td>
                                                        <td><small>{{ $factor->invoiceID }}</small></td>
                                                        <td>
                                                            @if ($factor->status == 0)
                                                                <span class="badge bg-label-warning me-1">منتظر
                                                                    تایید</span> <br />
                                                            @elseif($factor->status == 1)
                                                                <span class="badge bg-label-success me-1">تایید
                                                                    شده</span> <br />
                                                            @elseif($factor->status == 3)
                                                                <span class="badge bg-label-danger me-1">رد شده</span>
                                                                <br />
                                                            @elseif($factor->status == 5)
                                                                <span class="badge bg-label-warning me-1">مرجوعی</span>
                                                                <br />
                                                            @endif
                                                        </td>
                                                        <td class="big">
                                                            <small>{{ Verta($factor->created_at)->format('Y-m-d') }}</small>
                                                        </td>
                                                        <td>
                                                            <?php $details = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->count(); ?>
                                                            <small data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ $details }} قلم">
                                                                <?php
                                                                $Packs = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->sum('pack');
                                                                $tedad = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->sum('tedad');
                                                                ?>
                                                                @php($Organ = DB::table('organizations')->where('id', $factor->organization_id)->first())
                                                                @if ($Packs > 0)
                                                                    {{ $Packs }} {{ $Organ->sub_unit }}
                                                                @endif
                                                                @if ($tedad > 0)
                                                                    {{ $tedad }} {{ $Organ->unit_order }}
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td class="big">
                                                            <small>
                                                                {{ number_format(intval(str_replace(',', '', $factor->fullPrice))) }}
                                                            </small>
                                                        </td>
                                                        <td class="big"></td>
                                                        <td></td>
                                                    </tr>
                                                    @php($x++)
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">
                                                <svg width="21" height="17" viewBox="0 0 21 17"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M6.7881 14.521C6.7881 14.9188 6.63007 15.3003 6.34876 15.5816C6.06746 15.863 5.68593 16.021 5.2881 16.021C4.89028 16.021 4.50875 15.863 4.22744 15.5816C3.94614 15.3003 3.7881 14.9188 3.7881 14.521M6.7881 14.521C6.7881 14.1232 6.63007 13.7416 6.34876 13.4603C6.06746 13.179 5.68593 13.021 5.2881 13.021C4.89028 13.021 4.50875 13.179 4.22744 13.4603C3.94614 13.7416 3.7881 14.1232 3.7881 14.521M6.7881 14.521H12.7881M3.7881 14.521H1.9131C1.61474 14.521 1.32859 14.4025 1.11761 14.1915C0.906631 13.9805 0.788105 13.6944 0.788105 13.396V10.021M12.7881 14.521H15.0381M12.7881 14.521V10.021M0.788105 10.021V2.38599C0.786515 2.11216 0.886566 1.84747 1.06889 1.64316C1.25122 1.43885 1.50286 1.30945 1.7751 1.27999C5.10815 0.934015 8.46806 0.934015 11.8011 1.27999C12.3661 1.33799 12.7881 1.81799 12.7881 2.38599V3.34399M0.788105 10.021H12.7881M18.0381 14.521C18.0381 14.9188 17.8801 15.3003 17.5988 15.5816C17.3175 15.863 16.9359 16.021 16.5381 16.021C16.1403 16.021 15.7588 15.863 15.4774 15.5816C15.1961 15.3003 15.0381 14.9188 15.0381 14.521M18.0381 14.521C18.0381 14.1232 17.8801 13.7416 17.5988 13.4603C17.3175 13.179 16.9359 13.021 16.5381 13.021C16.1403 13.021 15.7588 13.179 15.4774 13.4603C15.1961 13.7416 15.0381 14.1232 15.0381 14.521M18.0381 14.521H19.1631C19.7841 14.521 20.2921 14.017 20.2531 13.397C20.0522 10.0946 18.94 6.91255 17.0401 4.20399C16.8591 3.95027 16.6229 3.741 16.3492 3.59202C16.0754 3.44303 15.7714 3.35822 15.4601 3.34399H12.7881M12.7881 3.34399V10.021"
                                                        stroke="#C1292E" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>

                                                حمل و نقل
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="card-datatable table-responsive redbox py-0">
                                        <style>
                                            .table tr th,
                                            .table tr td {
                                                padding: 7px !important;
                                            }

                                            .dataTables_filter {
                                                width: 365px
                                            }
                                        </style>
                                        <table class="datatables-direct-basic tablelist table red">
                                            <thead>
                                                <tr class="text-center">
                                                    <th width="20">#</th>
                                                    <th width="160">نام مشتری</th>
                                                    <th width="90">کد مشتری</th>
                                                    <th width="90">شماره سفارش</th>
                                                    <th>وضعیت</th>
                                                    <th width="110">تاریخ ثبت</th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: #fff">
                                                <?php $organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
                                                @php($x = 1)
                                                @foreach ($DriverFactors as $invoice)
                                                    <tr>
                                                        <th width="30" class="text-center">
                                                            <small>{{ $x }}</small>
                                                        </th>
                                                        <td width="160">
                                                            <a href="{{ route('pishFactorInfo', $invoice->id) }}">
                                                                <small>{{ isset($invoice->customer->name) ? $invoice->customer->name : 'وارد نشده' }}</small></a>
                                                        </td>
                                                        <td width="90">
                                                            <a href="{{ route('pishFactorInfo', $invoice->id) }}">
                                                                <small
                                                                    style="font-size: 12px">{{ isset($invoice->customer->customer_code) ? $invoice->customer->customer_code : '--' }}</small></a>
                                                        </td>
                                                        <td width="160">
                                                            <a href="{{ route('pishFactorInfo', $invoice->id) }}">
                                                                <small>{{ $invoice->invoiceID }}</small></a>
                                                        </td>
                                                        <td>
                                                            @if ($invoice->status == 0)
                                                                <small class="badge bg-label-warning me-1">منتظر
                                                                    تایید</small> <br />
                                                            @elseif($invoice->status == 1)
                                                                <small class="badge bg-label-success me-1">تایید
                                                                    شده</small> <br />
                                                            @elseif($invoice->status == 3)
                                                                <small class="badge bg-label-danger me-1">رد
                                                                    شده</small> <br />
                                                            @elseif($invoice->status == 5)
                                                                <small
                                                                    class="badge bg-label-warning me-1">مرجوعی</small>
                                                                <br />
                                                            @endif

                                                        </td>
                                                        <td width="110"><small data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="ساعت {{ Verta($invoice->created_at)->format('H:i') }}">{{ Verta($invoice->created_at)->format('Y-m-d') }}</small>
                                                        </td>
                                                    </tr>
                                                    @php($x++)
                                                @endforeach
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <!-- endbuild -->
    <!-- Vendors JS -->
    <script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/swiper/swiper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            jalaliDatepicker.startWatch();
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
                    pageLength: 25,
                    language: {
                        search: 'جستجو: ',
                        searchPlaceholder: 'جستجو کنید...',
                        info: 'نمایش صفحه _PAGE_ از _PAGES_',
                        infoEmpty: 'موردی وجود ندارد.',
                        infoFiltered: '(فیلتر شده _MAX_ از records)',
                        lengthMenu: 'نمایش _MENU_ مورد در صفحه',
                        zeroRecords: 'متاسفانه موردی پیدا نشد',
                        paginate: {
                            previous: 'قبلی',
                            next: 'بعدی',
                        }
                    },
                });

            }


        });
    </script>
    @if ($Target)
        <script src="{{ asset('/js/circle-progress.min.js') }}" type="module"></script>
        <style>
            circle-progress::part(base) {
                width: 150px;
                height: auto;
            }

            circle-progress::part(value) {
                stroke-width: 6px;
                stroke: #248230;
                stroke-linecap: round;
            }

            circle-progress::part(circle) {
                stroke-width: 8px;
                stroke: #D0E4D3;
            }

            circle-progress::part(text-value),
            circle-progress::part(text-max) {
                font-size: 11px;
                font-family: 'font-primary';
            }
        </style>
    @endif

</body>

</html>

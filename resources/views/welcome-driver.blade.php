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
@php(
    $Organ = DB::table('organizations')->where('id', auth()->user()->organization_id)->first()
)

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

                                                                ظرفیت ناوگان:
                                                            </span>
                                                            <strong class="text-dark">{{ $Cargo->cartons }}
                                                                کارتن</strong>
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

                                                                سفرهای فعال: </span>
                                                            <strong class="text-dark">{{ count($ActiveShipments) }} سفر
                                                            </strong>
                                                        </a>
                                                    </td>
                                                </tr>
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
                                                                تعداد کل مسیرها
                                                            </span>
                                                            <strong class="text-dark">{{ $TotalActiveRoutes }}
                                                                مسیر</strong>
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
                                                                مسیرهای انجام شده
                                                            </span>
                                                            <strong class="text-dark">{{ $DoneActiveRoutes }}
                                                                مسیر</strong>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between py-2">
                                        <div class="card-title mb-0">
                                            <h5 class="m-0 me-2">جزئیات سفرهای امروز
                                                <small>{{ Verta::now()->format('Y/m/d') }}</small></h5>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-borderless border-top">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <button
                                                            class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2"
                                                            type="button">
                                                            <span class="text-dark">
                                                                <svg width="20" height="23"
                                                                    viewBox="0 0 12 15" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M8.50033 3.5C8.50033 4.16304 8.23691 4.79892 7.768 5.26776C7.2991 5.7366 6.66313 6 6 6C5.33687 6 4.7009 5.7366 4.232 5.26776C3.76309 4.79892 3.49967 4.16304 3.49967 3.5C3.49967 2.83696 3.76309 2.20107 4.232 1.73223C4.7009 1.26339 5.33687 1 6 1C6.66313 1 7.2991 1.26339 7.768 1.73223C8.23691 2.20107 8.50033 2.83696 8.50033 3.5ZM1 12.912C1.02143 11.6002 1.55763 10.3494 2.49298 9.42936C3.42833 8.50928 4.68788 7.99364 6 7.99364C7.31212 7.99364 8.57166 8.50928 9.50702 9.42936C10.4424 10.3494 10.9786 11.6002 11 12.912C9.43138 13.6312 7.72566 14.0023 6 14C4.21576 14 2.5222 13.6107 1 12.912Z"
                                                                        stroke="#543C92" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>

                                                                سفرهای امروز:
                                                            </span>
                                                            <strong class="text-dark">{{ count($DayShipments) }} سفر
                                                            </strong>
                                                        </button>
                                                        <a href="#"
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

                                                                تعداد کل مسیر های امروز </span>
                                                            <strong class="text-dark">{{ $TotalDayRoutes }} مسیر
                                                            </strong>
                                                        </a>
                                                    </td>
                                                </tr>
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
                                                                مسیرهای انجام شده امروز
                                                            </span>
                                                            <strong class="text-dark">{{ $DoneDayRoutes }}
                                                                مسیر</strong>
                                                        </button>
                                                    </td>
                                                </tr>
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
                                                سفرهای فعال امروز
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="card-datatable table-responsive tablelist py-0">
                                        <table class="datatables-direct-basic table table-wrap">
                                            <thead>
                                                <tr>
                                                    <th width="20">#</th>
                                                    <th class="big">شماره سفر</th>
                                                    <th class="big">تاریخ سفر</th>
                                                    <th>بازه ساعت</th>
                                                    <th>تعداد مسیر</th>
                                                    <th class="big">مسیرهای انجام شده</th>
                                                    <th>جزئیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($DayShipments as $dship)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td><a
                                                                href="{{ route('deliveries.myShipment', $dship->id) }}">{{ $dship->number }}</a>
                                                        </td>
                                                        <td><a
                                                                href="{{ route('deliveries.myShipment', $dship->id) }}">{{ $dship->date_fa }}</a>
                                                        </td>
                                                        <td>{{ $dship->hours }}</td>
                                                        <td>{{ $dship->routes_count }}</td>
                                                        <td>{{ $dship->done_routes_count }}</td>
                                                        <td>
                                                            <a href="{{ route('deliveries.myShipment', $dship->id) }}">
                                                                <svg width="24" height="20"
                                                                    viewBox="0 0 14 10" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M0.531055 5.1932C0.489648 5.06875 0.489648 4.93425 0.531055 4.8098C1.36326 2.306 3.72546 0.5 6.50946 0.5C9.29226 0.5 11.6533 2.3042 12.4873 4.8068C12.5293 4.931 12.5293 5.0654 12.4873 5.1902C11.6557 7.694 9.29346 9.5 6.50946 9.5C3.72666 9.5 1.36506 7.6958 0.531055 5.1932Z"
                                                                        stroke="#248230" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                    <path
                                                                        d="M8.30996 4.99995C8.30996 5.47734 8.12032 5.93518 7.78275 6.27274C7.44519 6.61031 6.98735 6.79995 6.50996 6.79995C6.03257 6.79995 5.57473 6.61031 5.23717 6.27274C4.8996 5.93518 4.70996 5.47734 4.70996 4.99995C4.70996 4.52256 4.8996 4.06472 5.23717 3.72716C5.57473 3.38959 6.03257 3.19995 6.50996 3.19995C6.98735 3.19995 7.44519 3.38959 7.78275 3.72716C8.12032 4.06472 8.30996 4.52256 8.30996 4.99995Z"
                                                                        stroke="#248230" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>
                                                            </a>
                                                        </td>
                                                    </tr>
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

                                                همه سفرهای فعال
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
                                                    <th class="big">شماره سفر</th>
                                                    <th class="big">تاریخ سفر</th>
                                                    <th>بازه ساعت</th>
                                                    <th>تعداد مسیر</th>
                                                    <th class="big">مسیرهای انجام شده</th>
                                                    <th>جزئیات</th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: #fff">
                                                @php($x = 1)
                                                @foreach ($ActiveShipments as $dship)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td><a
                                                                href="{{ route('deliveries.myShipment', $dship->id) }}">{{ $dship->number }}</a>
                                                        </td>
                                                        <td><a
                                                                href="{{ route('deliveries.myShipment', $dship->id) }}">{{ $dship->date_fa }}</a>
                                                        </td>
                                                        <td>{{ $dship->hours }}</td>
                                                        <td>{{ $dship->routes_count }}</td>
                                                        <td>{{ $dship->done_routes_count }}</td>
                                                        <td>
                                                            <a href="{{ route('deliveries.myShipment', $dship->id) }}">
                                                                <svg width="24" height="20"
                                                                    viewBox="0 0 14 10" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M0.531055 5.1932C0.489648 5.06875 0.489648 4.93425 0.531055 4.8098C1.36326 2.306 3.72546 0.5 6.50946 0.5C9.29226 0.5 11.6533 2.3042 12.4873 4.8068C12.5293 4.931 12.5293 5.0654 12.4873 5.1902C11.6557 7.694 9.29346 9.5 6.50946 9.5C3.72666 9.5 1.36506 7.6958 0.531055 5.1932Z"
                                                                        stroke="#248230" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                    <path
                                                                        d="M8.30996 4.99995C8.30996 5.47734 8.12032 5.93518 7.78275 6.27274C7.44519 6.61031 6.98735 6.79995 6.50996 6.79995C6.03257 6.79995 5.57473 6.61031 5.23717 6.27274C4.8996 5.93518 4.70996 5.47734 4.70996 4.99995C4.70996 4.52256 4.8996 4.06472 5.23717 3.72716C5.57473 3.38959 6.03257 3.19995 6.50996 3.19995C6.98735 3.19995 7.44519 3.38959 7.78275 3.72716C8.12032 4.06472 8.30996 4.52256 8.30996 4.99995Z"
                                                                        stroke="#248230" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>

                                                            </a>
                                                        </td>
                                                    </tr>
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


</body>

</html>

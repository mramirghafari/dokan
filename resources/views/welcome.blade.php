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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS -->@if (empty($showSetupCard))
        @include('partials.assets.datatables-styles', ['bundle' => 'basic'])
        <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" />
        <link href="{{ asset('assets/') }}/vendor/css/pages/cards-advance.css" rel="stylesheet" />
    @endif
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css?v={{ filemtime(public_path('assets/css/rtl.css')) }}" rel="stylesheet" />
    @if (!empty($showSetupCard) || !empty($panelOnboarding['show_welcome_modal']) || !empty($panelOnboarding['show_tour']))
        @include('partials.panel-onboarding-styles')
    @endif
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

        .datatables-direct-basic thead th {
            text-align: center !important;
        }

        .datatables-direct-basic td:nth-child(1),
        .datatables-direct-basic th:nth-child(1) {
            width: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            white-space: nowrap;
            text-align: center;
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
                        @include('partials.panel-setup-dashboard')

                        @if (empty($showSetupCard) && ($dashboardWidgets['dashboard_widget_quick_actions'] ?? true))
                        <div class="row">
                            @if (\App\Services\TenantSettings::enabled('feature_route_management'))
                                <div class="mb-4 col-sm-6 col-12">
                                    <a class="btn btn-primary waves-effect waves-light w-100"
                                        href="{{ route('tasks.create') }}"><x-ui.icon name="plus" class="me-md-2" /> ثبت مسیر
                                        جدید</a>
                                </div>
                            @endif
                            <div class="mb-4 col-sm-6 col-12">
                                <a class="btn btn-primary waves-effect waves-light w-100"
                                    href="{{ route('products.index') }}"><x-ui.icon name="plus" class="me-md-2" /> ثبت سفارش
                                    جدید</a>
                            </div>
                        </div>
                        @endif

                        @if (
                            empty($showSetupCard) && (
                                ($dashboardWidgets['dashboard_widget_user_info'] ?? true)
                                || ($dashboardWidgets['dashboard_widget_org_stats'] ?? true)
                            )
                        )
                        <div class="row mb-3">
                            @if (empty($showSetupCard) && ($dashboardWidgets['dashboard_widget_user_info'] ?? true))
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
                                                @foreach ($OrganInfos as $organinfo)
                                                    <tr>
                                                        <td>
                                                            <button
                                                                class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2"
                                                                type="button">
                                                                <span class="text-dark">
                                                                    <svg width="18" height="11"
                                                                        viewBox="0 0 18 11" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M1 10L6.50739 4.70034L10.0207 8.08113C11.0364 6.15454 12.712 4.62428 14.7644 3.74876L17 2.79089M17 2.79089L12.1535 1M17 2.79089L15.1397 7.45459"
                                                                            stroke="#543C92" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>

                                                                    </svg>

                                                                    فروش ماه گذشته {{ $organinfo['title'] }}:
                                                                </span>
                                                                <strong class="text-dark"
                                                                    id="last_month_organ{{ $organinfo['id'] }}"></strong>
                                                            </button>
                                                            <a href="{{ route('tasks.MyTasks') }}"
                                                                class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between">
                                                                <span style="color: #594295">
                                                                    <svg width="18" height="11"
                                                                        viewBox="0 0 18 11" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M1 10L6.50739 4.70034L10.0207 8.08113C11.0364 6.15454 12.712 4.62428 14.7644 3.74876L17 2.79089M17 2.79089L12.1535 1M17 2.79089L15.1397 7.45459"
                                                                            stroke="#543C92" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>


                                                                    فروش ماه جاری {{ $organinfo['title'] }}: </span>
                                                                <strong class="text-dark"
                                                                    id="this_month_organ{{ $organinfo['id'] }}"></strong>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td style="padding-bottom: 0px !important;">
                                                        <button
                                                            class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2"
                                                            type="button">
                                                            <span class="text-dark">
                                                                <svg width="18" height="11"
                                                                    viewBox="0 0 18 11" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M1 10L6.50739 4.70034L10.0207 8.08113C11.0364 6.15454 12.712 4.62428 14.7644 3.74876L17 2.79089M17 2.79089L12.1535 1M17 2.79089L15.1397 7.45459"
                                                                        stroke="#543C92" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>

                                                                </svg>

                                                                فاکتورهای تاییدی این ماه:
                                                            </span>
                                                            <strong class="text-dark">123</strong>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-top: 0px !important;">
                                                        <button
                                                            class="btn btn-label-secondary waves-effect w-100 d-flex justify-content-between mb-2"
                                                            type="button">
                                                            <span class="text-dark">
                                                                <svg width="18" height="11"
                                                                    viewBox="0 0 18 11" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M1 10L6.50739 4.70034L10.0207 8.08113C11.0364 6.15454 12.712 4.62428 14.7644 3.74876L17 2.79089M17 2.79089L12.1535 1M17 2.79089L15.1397 7.45459"
                                                                        stroke="#543C92" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>

                                                                </svg>

                                                                فاکتورهای تحویل شده به مشتری این ماه:
                                                            </span>
                                                            <strong class="text-dark">123</strong>
                                                        </button>

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if (empty($showSetupCard) && ($dashboardWidgets['dashboard_widget_org_stats'] ?? true))
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between py-2">
                                        <div class="card-title mb-0">
                                            <ul id="MyOrgans" class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <button aria-controls="navs-top-home" aria-selected="true"
                                                        class="nav-link active" data-bs-target="#amar"
                                                        data-bs-toggle="tab" role="tab" type="button">
                                                        آمار کلی
                                                    </button>
                                                </li>
                                                @php($oc = 1)
                                                @foreach ($OrganInfos as $organinfo)
                                                    <li class="nav-item" data-org-id="{{ $organinfo['id'] }}">
                                                        <button aria-controls="navs-top-home" aria-selected="true"
                                                            class="nav-link "
                                                            data-bs-target="#organ{{ $oc }}"
                                                            data-bs-toggle="tab" role="tab" type="button">
                                                            {{ $organinfo['title'] }}
                                                        </button>
                                                    </li>
                                                    @php($oc++)
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="amar" role="tabpanel">
                                                <div class="text-center px-3">
                                                    <circle-progress text-format="vertical"
                                                        indeterminateText="تارگت شما" max="{{ $FullTargetsPrices }}"
                                                        value="{{ $AllFactorPrices }}"></circle-progress>
                                                    <p class="text-info" style="font-size: 13px;font-weight: bold">تا
                                                        این لحظه {{ number_format($AllFactorPrices) }} ریال از تارگت
                                                        خود را به دست آورده اید.</p>
                                                    <p class="text-warning" style="font-size: 12px;font-weight: bold">
                                                        مبلغ فاکتورهای تایید شده تا این لحظه:
                                                        {{ number_format($AcceptedFactorFullPrices) }}</p>
                                                    <p class="text-success" style="font-size: 12px;font-weight: bold">
                                                        مبلغ فاکتورهای تحویل شده به مشتریان تا این لحظه:
                                                        {{ number_format($CompletedFactorFullPrices) }}</p>

                                                    @if ($EndTarget)
                                                        <p class="text-warning" style="font-size: 13px">
                                                            هنوز <strong>{{ verta("$EndTarget")->diffDays() }}</strong>
                                                            روز برای به پایان رساندن تارگت این ماهتان زمان دارید.
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="card green m-3">
                                                    <div class="card-header py-2" style="background-color: #248230">
                                                        <div class="card-title mb-0 text-white text-center">
                                                            تارگت این ماه: <strong
                                                                style="font-size: 18px">{{ number_format($FullTargetsPrices) }}</strong>
                                                            <small>ریال</small>
                                                        </div>
                                                    </div>
                                                    <div class="card-body  pb-0"
                                                        style="background-color: rgba(36,130,48,0.1)">
                                                        <p class="text-danger text-center my-2">کسر شده از تارگت:
                                                            <strong style="display: inline-block;direction: ltr"> -
                                                                {{ number_format($AllFactorPrices) }}</strong>
                                                        </p>
                                                        <?php $Mande = $FullTargetsPrices - intval($AllFactorPrices); ?>
                                                        <p class="text-success text-center">مانده به ریال: <strong
                                                                style="display: inline-block;direction: ltr">
                                                                {{ number_format($Mande) }}</strong></p>
                                                    </div>
                                                </div>
                                            </div>
                                            @php($oc = 1)
                                            @foreach ($OrganInfos as $organinfo)
                                                <div class="tab-pane fade " id="organ{{ $oc }}"
                                                    role="tabpanel">
                                                    <div id="chartContainer{{ $organinfo['id'] }}">
                                                        <div class="text-center px-3">
                                                            <circle-progress text-format="vertical"
                                                                indeterminateText="تارگت شما"
                                                                max="{{ $FullTargetsPrices }}"
                                                                value="{{ $AllFactorPrices }}"></circle-progress>
                                                            <p class="text-success"
                                                                style="font-size: 13px;font-weight: bold">تا این لحظه
                                                                {{ number_format($AllFactorPrices) }} ریال از تارگت خود
                                                                را به دست آورده اید.</p>
                                                            <p class="text-warning"
                                                                style="font-size: 12px;font-weight: bold">مبلغ
                                                                فاکتورهای تایید شده تا این لحظه:
                                                                {{ number_format($organinfo['AcceptedFactorFullPrices']) }}
                                                            </p>
                                                            <p class="text-success"
                                                                style="font-size: 12px;font-weight: bold">مبلغ
                                                                فاکتورهای تحویل شده به مشتریان تا این لحظه:
                                                                {{ number_format($organinfo['CompletedFactorFullPrices']) }}
                                                            </p>
                                                            @if ($EndTarget)
                                                                <p class="text-warning" style="font-size: 13px">
                                                                    هنوز
                                                                    <strong>{{ verta("$EndTarget")->diffDays() }}</strong>
                                                                    روز برای به پایان رساندن تارگت این ماهتان زمان
                                                                    دارید.
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div class="card green m-3">
                                                            <div class="card-header py-2"
                                                                style="background-color: #248230">
                                                                <div class="card-title mb-0 text-white text-center">
                                                                    تارگت این ماه: <strong
                                                                        style="font-size: 18px">{{ number_format($FullTargetsPrices) }}</strong>
                                                                    <small>ریال</small>
                                                                </div>
                                                            </div>
                                                            <div class="card-body  pb-0"
                                                                style="background-color: rgba(36,130,48,0.1)">
                                                                <p class="text-danger text-center my-2">کسر شده از
                                                                    تارگت: <strong
                                                                        style="display: inline-block;direction: ltr"> -
                                                                        {{ number_format($AllFactorPrices) }}</strong>
                                                                </p>
                                                                <?php $Mande = $FullTargetsPrices - intval($AllFactorPrices); ?>
                                                                <p class="text-success text-center">مانده به ریال:
                                                                    <strong
                                                                        style="display: inline-block;direction: ltr">
                                                                        {{ number_format($Mande) }}</strong>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php($oc++)
                                            @endforeach
                                        </div>

                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if (empty($showSetupCard) && ($dashboardWidgets['dashboard_widget_top_leaders'] ?? true))
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
                                                وضعیت سرپرست ها
                                            </h5>
                                        </div>
                                        <div class="col-6 px-2">
                                            <form method="post" action="" class="row justify-content-end"
                                                id="date_filter">
                                                @csrf
                                                <div class="col-5 px-0 pe-1">
                                                    <input type="text" class="form-control"
                                                        name="leader_from_date" placeholder="از تاریخ" data-jdp>
                                                </div>
                                                <div class="col-5 px-0 pe-1">
                                                    <input type="text" class="form-control" name="leader_to_date"
                                                        placeholder="تا تاریخ" data-jdp>
                                                </div>
                                                <div class="col-2 col-md-1 px-0">
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
                                                    <th
                                                        style="width: 10px !important; max-width: 40px !important; min-width: 40px !important">
                                                        #</th>
                                                    <th>کد سرپرست</th>
                                                    <th class="name-col">نام سرپرست</th>
                                                    <th>مشتری جدید</th>
                                                    <th>تعداد فاکتور</th>
                                                    <th>مبلغ کل فاکتور <small>ریال</small></th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($MyLeaders as $leader)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td><small>{{ $leader->leader->username }}</small></td>
                                                        <td class="name-col"><small data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ $leader->leader->name }}"><a
                                                                    href="{{ route('userInvoiceList', $leader->leader->id) }}">{{ $leader->leader->name }}</a></small>
                                                        </td>

                                                        <td>
                                                            <?php
                                                            /*$LeaderRegions = DB::table('regions')->where('leader_id',$leader->sarprast_id)->pluck('id');
                                                        $LeaderAreas = DB::table('areas')->whereIn('region_id',$LeaderRegions)->pluck('id');
                                                        $LeaderCustomersCount = DB::table('customers')->whereIn('area', $LeaderAreas)->count(); */
                                                            ?>
                                                            <small>{{ $user->direct_new_customers + $user->team_new_customers ?? 0 }}</small>

                                                        </td>
                                                        <td>
                                                            <small>{{ $leader->total_factors }}</small>
                                                        </td>
                                                        <td><small>{{ number_format($leader->total_fullPrice) }}</small>
                                                        </td>
                                                        <td>
                                                            @if ($leader->leader->isActive == 1)
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
                        @endif
                        @if (empty($showSetupCard) && ($dashboardWidgets['dashboard_widget_top_visitors'] ?? true))
                        <div class="row mb-3">
                            <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
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
                                    </div>
                                    <div class="card-datatable table-responsive yellowbox py-0">
                                        <style>
                                            .table tr th,
                                            .table tr td {
                                                padding: 7px !important;
                                            }

                                            .dataTables_filter {
                                                width: 365px
                                            }
                                        </style>
                                        <table class="datatables-direct-basic tablelist table yellow">
                                            <thead>
                                                <tr class="text-center">
                                                    <th width="20">#</th>
                                                    <th>کد بازاریاب</th>
                                                    <th class="name-col">نام بازاریاب</th>
                                                    <th>مشتری جدید</th>
                                                    <th>تعداد فاکتور</th>
                                                    <th>مبلغ کل فاکتور <small>ریال</small></th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: #fff">

                                                @php($x = 1)
                                                @foreach ($topVisitors as $visitor)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td><small>{{ $visitor->visitor->username }}</small></td>
                                                        <td class="name-col"><small data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ $visitor->visitor->name }}"><a
                                                                    href="{{ route('userInvoiceList', $visitor->visitor_id) }}">{{ $visitor->visitor->name }}</a></small>
                                                        </td>

                                                        <td>



                                                            <small>{{ $user->direct_new_customers + $user->team_new_customers ?? 0 }}</small>

                                                        </td>
                                                        <td>
                                                            <small>{{ $visitor->total_factors }}</small>
                                                        </td>
                                                        <td><small>{{ number_format($visitor->total_fullPrice) }}</small>
                                                        </td>
                                                        <td>
                                                            @if ($visitor->visitor->isActive == 1)
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
                        @endif

                        @if (empty($showSetupCard) && ($dashboardWidgets['dashboard_widget_recent_factors'] ?? true))
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
                                                    <th class="big">کد مشتری</th>
                                                    <th class="big">نام مشتری</th>
                                                    <th>شماره سفارش</th>
                                                    <th class="big">تاریخ تحویل</th>
                                                    <th>تعداد اقلام</th>
                                                    <th class="big">مبلغ کل <small>ریال</small></th>
                                                    <th>وضعیت</th>
                                                    <th class="big">اطلاعات پرداخت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($Factors as $factor)
                                                    <?php $Organ = App\Models\Organization::find($factor->organization_id); ?>
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td class="big">
                                                            <small>{{ $factor->customer->customer_code }}</small>
                                                        </td>
                                                        <td class="big">
                                                            <a href="{{ route('pishFactorInfo', $factor->id) }}">
                                                                <small data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ $factor->customer->name }}">
                                                                    {{ strlen($factor->customer->name) > 12 ? mb_substr($factor->customer->name, 0, 15, 'UTF-8') . '...' : $factor->customer->name }}</small>
                                                            </a>
                                                        </td>

                                                        <td><a
                                                                href="{{ route('pishFactorInfo', $factor->id) }}"><small>{{ $factor->invoiceID }}</small></a>
                                                        </td>
                                                        <td class="big"><a
                                                                href="{{ route('pishFactorInfo', $factor->id) }}"><small>{{ $factor->recive_date }}</small></a>
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
                                                        <td class="big">
                                                            @if ($factor->status == 0)
                                                                <span class="badge bg-label-warning me-1">منتظر
                                                                    تایید</span> <br />
                                                            @elseif($factor->status == 1)
                                                                @if ($factor->step == 2)
                                                                    <small
                                                                        class="badge bg-label-success send_to_store_status me-1"
                                                                        style="font-size: 9px;">تایید شده - ارسال به
                                                                        انبار</small> <br />
                                                                @elseif($factor->step == 3)
                                                                    <small
                                                                        class="badge bg-label-success shipment_status me-1"
                                                                        style="font-size: 9px;">تایید شده - باربری و
                                                                        پخش</small> <br />
                                                                @elseif($factor->step == 4)
                                                                    <small
                                                                        class="badge bg-label-success arrived_status me-1"
                                                                        style="font-size: 9px">تایید شده - تحویل به
                                                                        مشتری</small> <br />
                                                                @else
                                                                    <small
                                                                        class="badge bg-label-success accepted_status me-1"
                                                                        style="font-size: 9px;">تایید شده</small>
                                                                    <br />
                                                                @endif
                                                            @elseif($factor->status == 3)
                                                                <span class="badge bg-label-danger me-1">رد شده</span>
                                                                <br />
                                                            @elseif($factor->status == 4)
                                                                <small
                                                                    class="badge bg-label-success arrived_status me-1"
                                                                    style="font-size: 9px">تایید شده - تحویل به
                                                                    مشتری</small> <br />
                                                            @elseif($factor->status == 5)
                                                                <span class="badge bg-label-warning me-1">مرجوعی</span>
                                                                <br />
                                                            @endif
                                                        </td>
                                                        <td class="big">
                                                            @if ($factor->payment_type == 1)
                                                                <span>پرداخت نقدی</span>
                                                            @elseif($factor->payment_type == 2)
                                                                <span>چک 30 روزه</span>
                                                            @elseif($factor->payment_type == 3)
                                                                <span>پرداخت حین تحویل</span>
                                                            @else
                                                                <span class="text-danger">مشخص نشده</span>
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
                        @endif

                        @if (empty($showSetupCard))
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
                                                @php($x = 1)

                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (empty($showSetupCard) && ($dashboardWidgets['dashboard_widget_warehouse'] ?? true))
                        @include('sections.warehouse_dashboard')
                        @endif
                        @endif
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

    @if (!empty($panelOnboarding['show_welcome_modal']))
        @include('partials.panel-welcome-modal')
    @endif

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <!-- endbuild -->
    @if (empty($showSetupCard))
        @include('partials.assets.datatables-scripts', ['bundle' => 'basic'])
        <script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
    @endif

    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        if (window.panelTourConfig) {
            window.panelTourConfig.isDashboard = true;
            window.panelTourConfig.showWelcomeModal = @json(!empty($panelOnboarding['show_welcome_modal']));
        }
    </script>
    @include('partials.panel-tour-scripts')

    @if (empty($showSetupCard))
    <!-- Dashboard widgets JS -->
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
            var dt_without_ajax_table = $('.datatables-direct-basic');

            if (dt_without_ajax_table.length) {
                dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: true,
                    pageLength: 25,
                    autoWidth: false, // مهم: جلوگیری از inline-width های خودکار
                    columnDefs: [{
                            width: '40px',
                            targets: 0
                        }, // ستون اول
                        {
                            width: '70px',
                            targets: 0
                        }, // ستون اول
                        // مثال: { width: '100px', targets: 1 }, برای ستون دوم
                    ],
                    language: {
                        search: 'جستجو: ',
                        searchPlaceholder: 'جستجو کنید...',
                        info: 'نمایش صفحه _PAGE_ از _PAGES_',
                        infoEmpty: 'موردی وجود ندارد.',
                        infoFiltered: '(فیلتر شده از _MAX_ مورد)',
                        lengthMenu: 'نمایش _MENU_ مورد در صفحه',
                        zeroRecords: 'متاسفانه موردی پیدا نشد',
                        paginate: {
                            previous: 'قبلی',
                            next: 'بعدی',
                        }
                    }
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


    <script>
        $(document).ready(function() {
            // نمودار رو توی این متغیر نگه می‌داریم تا بتونیم دوباره ایجادش کنیم
            let myChart;
            // تابع ساخت نمودار
            function initChart(labels, values) {
                const ctx = document.getElementById('myChart').getContext('2d');

                // اگر قبلاً نموداری هست، پاکش کن
                if (myChart) {
                    myChart.destroy();
                }

                // ساخت نمودار جدید
                myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'فروش',
                            data: values,
                            backgroundColor: '#543C92'
                        }]
                    },
                    options: {}
                });
            }



            // تاریخ‌ها از بلید میان
            var startTarget = "{!! $StartTarget !!}";
            var endTarget = "{!! $EndTarget !!}";

            if (startTarget) startTarget = startTarget.split(' ')[0];
            if (endTarget) endTarget = endTarget.split(' ')[0];

            var ajaxData = {};
            if (startTarget) ajaxData.startDate = startTarget;
            if (endTarget) ajaxData.endDate = endTarget;

            // لیست سازمان‌ها از بلید؛ مثلاً آیدی‌هایی که در $OrganInfos هستن
            var orgIds = [
                @foreach ($OrganInfos as $organinfo)
                    {{ $organinfo['id'] }},
                @endforeach
            ];

            // برای هر سازمان یک رکوئست جدا
            orgIds.forEach(function(orgId) {
                $.ajax({
                    url: "{{ asset('/dashboard/org-data') }}/" + orgId,
                    type: 'GET',
                    data: ajaxData,
                    dataType: 'json',

                    beforeSend: function() {
                        // نمایش لودر‌ها
                        $('#this_month_organ' + orgId).html(`
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
        `);
                        $('#last_month_organ' + orgId).html(`
            <div class="spinner-border spinner-border-sm text-secondary" role="status">
                <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
        `);
                    },

                    success: function(data) {
                        $('#this_month_organ' + orgId).html(data.total_amount.toLocaleString(
                            'fa-IR') + ' ریال');
                        $('#last_month_organ' + orgId).html(data.last_month_total
                            .toLocaleString('fa-IR') + ' ریال');
                    },

                    error: function(xhr) {
                        console.error(xhr);
                        $('#this_month_organ' + orgId).html(
                            '<span class="text-danger">خطا</span>');
                        $('#last_month_organ' + orgId).html(
                            '<span class="text-danger">خطا</span>');
                    }
                });

            });

            // رویداد کلیک فقط برای li هایی که data-org-id دارند
            $('#MyOrgans li[data-org-id]').on('click', function() {
                let orgId = $(this).data('org-id');

                // نمایش اسپینر
                $('#chartContainer' + orgId).html(`
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
            </div>
        `);

                var startTarget = "{!! $StartTarget !!}";
                var endTarget = "{!! $EndTarget !!}";

                if (startTarget) {
                    startTarget = startTarget.split(' ')[0]; // فقط تاریخ
                }

                if (endTarget) {
                    endTarget = endTarget.split(' ')[0]; // فقط تاریخ
                }

                var ajaxData = {};
                if (startTarget) ajaxData.startDate = startTarget;
                if (endTarget) ajaxData.endDate = endTarget;

                // Ajax GET request
                $.ajax({
                    url: "{{ asset('/dashboard/org-data') }}/" + orgId, // آدرس روت
                    type: 'GET',
                    data: ajaxData, // اینجا اضافه شد
                    dataType: 'json',
                    success: function(data) {
                        var mande = ({{ $FullTargetsPrices }} - data.total_amount)
                            .toLocaleString('fa-IR');
                        let total_amount_formatted = data.total_amount.toLocaleString('fa-IR');
                        let amount_Accepted_formatted = data.amount_Accepted.toLocaleString(
                            'fa-IR');
                        let amount_Completed_formatted = data.amount_Completed.toLocaleString(
                            'fa-IR');

                        $('#chartContainer' + orgId).html(`
                <div class="text-center px-3">
                    <circle-progress
                value="${data.total_amount}"
                max="{{ $FullTargetsPrices }}"
                text-format="vertical"
                indeterminateText="تعداد فاکتور">
            </circle-progress>
                    <p class="text-info" style="font-size: 13px;font-weight: bold">تا این لحظه ${total_amount_formatted} ریال از تارگت خود را به دست آورده اید.</p>
                    <p class="text-warning" style="font-size: 12px;font-weight: bold">مبلغ فاکتورهای تایید شده تا این لحظه: ${amount_Accepted_formatted}</p>
                    <p class="text-success" style="font-size: 12px;font-weight: bold">مبلغ فاکتورهای تحویل شده به مشتریان تا این لحظه: ${amount_Completed_formatted}</p>
                    @if ($EndTarget)
                    <p class="text-warning" style="font-size: 13px">
                    هنوز <strong>{{ verta("$EndTarget")->diffDays() }}</strong> روز برای به پایان رساندن تارگت این ماهتان زمان دارید.
                        </p>
                    @endif
                    </div>
                    <div class="card green m-3">
                    <div class="card-header py-2" style="background-color: #248230">
                    <div class="card-title mb-0 text-white text-center">
                    تارگت این ماه: <strong style="font-size: 18px">{{ number_format($FullTargetsPrices) }}</strong> <small>ریال</small>
                        </div>
                    </div>
                    <div class="card-body  pb-0" style="background-color: rgba(36,130,48,0.1)">
                        <p class="text-danger text-center my-2">کسر شده از تارگت: <strong style="display: inline-block;direction: ltr"> - ${total_amount_formatted}</strong></p>
        <p class="text-success text-center">مانده به ریال: <strong style="display: inline-block;direction: ltr"> ${mande}</strong></p>
                    </div>
            </div>
        `);
                        initChart(data.labels, data.values);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#chartContainer' + orgId).html(
                            '<p class="text-danger">خطا در بارگذاری داده‌ها</p>');
                    }
                });


            });
        });
    </script>
    @endif



</body>

</html>

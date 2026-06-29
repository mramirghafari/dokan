<?php

use Hekmatinasser\Verta\Verta; ?>
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>تارگت پلن من -  دکان دارمینو</title>
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
                    @if($Target && $Target != null)
                        @php($Organ = DB::table('organizations')->where('id',auth()->user()->organization_id)->first())
                    <div class="row">
                        <!-- Support Tracker -->
                        <div class="col-12 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between pb-0">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">جزئیات تارگت پلن</h5>
                                        <small class="text-muted">مشخصات و جزئیات تارگت پلن من</small>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row justify-content-between">
                                        <div class="col-12 col-sm-6 col-lg-6">
                                            <div class="mt-lg-4 mt-lg-2 mb-lg-4 mb-2 pt-3">
                                                <h2 class="mb-0 lh-80p">{{ number_format($Target->target_price) }} <small>{{ org_currency_label($Organ) }}</small></h2>
                                                <p class="mb-0">تارگت مالی کل</p>
                                            </div>
                                            <ul class="row p-0 m-0">
                                                                                                <li class="d-flex col-12 col-md-6 gap-3 align-items-center mb-lg-3 pb-1">
                                                    <div class="badge rounded bg-label-success p-1">
                                                        <x-ui.icon name="circle-check" class="ti-sm" />
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تاریخ شروع</h6>
                                                        <small class="text-muted">{{ $Target->start_date_fa }}</small>
                                                    </div>
                                                </li>
                                                <li class="d-flex col-12 col-md-6 gap-3 align-items-center pb-1">
                                                    <div class="badge rounded bg-label-danger p-1">
                                                        <x-ui.icon name="clock" class="ti-sm" />
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تاریخ پایان</h6>
                                                        <small class="text-muted">{{ $Target->end_date_fa }}</small>
                                                    </div>
                                                </li>
                                                <li class="d-flex col-12 col-md-6 gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                                                    <div class="badge rounded bg-label-primary p-1">
                                                        <x-ui.icon name="target" class="ti-sm" />
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
                                                <li class="d-flex col-12 col-md-6 gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                                                    <div class="badge rounded bg-label-success p-1">
                                                        <x-ui.icon name="star" class="ti-sm" />
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">تعداد فاکتورهای ثبت شده</h6>
                                                        <span class="badge bg-label-success me-1">{{ $AllFactorCount }} فاکتور </span>
                                                    </div>
                                                </li>
                                                <li class="d-flex col-12 col-md-12 gap-3 align-items-center pb-1">
                                                    <div class="badge rounded bg-label-success p-1">
                                                        <x-ui.icon name="moneybag" class="ti-sm" />
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-nowrap">مجموع درآمد فاکتورها</h6>
                                                        <strong class="text-darg">{{ number_format($AllFactorPrices) }} {{ org_currency_label($Organ) }}</strong>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-12 col-sm-4 col-md-12 col-lg-4 text-end">
                                            <div id="supportTracker"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Monthly Campaign State -->
                        @foreach($myUsers as $myuser)
                            <div class="col-xl-4 col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">{{ $myuser['name'] }}</h5>
                                            @php($MyUserFactors = DB::table('pishfactors')->where('sarparast_id',$myuser['id'])->whereIn('status',[1,4])->whereBetween('created_at', ["$Target->start_date_en", "$Target->end_date_en"])->get())
                                            <p>تعداد فاکتورها: {{ $myuser['factors_count'] }} </p>
                                            <p>مجموع درآمد فاکتورها:  <span class="badge bg-label-success me-1">{{ number_format($myuser['FactorPrices']) }} {{ org_currency_label($Organ) }}</span></p>
                                            @if(count($myuser['children']) > 0)
                                                <small class="text-muted">تعداد بازاریاب ها: {{ count($myuser['children']) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(count($myuser['children']) > 0)
                                        @foreach($myuser['children'] as $visitor)
                                        <ul class="p-0 m-0">
                                            <li class="mb-4 pb-1 d-flex justify-content-between align-items-center">
                                                <div class="badge bg-label-primary rounded p-2">
                                                    <x-ui.icon name="user" class="ti-sm" />
                                                </div>
                                                <div class="d-flex justify-content-between w-100 flex-wrap">
                                                    <h6 class="mb-0 ms-3">{{ $visitor['name'] }}</h6>

                                                    <div class="d-flex">
                                                        <p class="mb-0 fw-medium">{{ $visitor['factors_count'] }} فاکتور</p>
                                                        <p class="ms-3 text-success mb-0">{{ number_format($visitor['FactorPrices']) }} <small>{{ org_currency_label($Organ) }}</small></p>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <!--/ Monthly Campaign State -->

                    </div>
                    @else
                        <div class="row">
                            <!-- Support Tracker -->
                            <div class="col-12 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between pb-0">
                                        <div class="card-title mb-3">
                                            <h5 class="mb-0">شما تارگت فعالی ندارید</h5>
                                            <small class="text-muted">هیچ تارگت پلنی برای شما ثبت نشده است.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
<!-- Vendors JS -->
<script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/swiper/swiper.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script>
    // datatable (jquery)
    $('.targets').addClass('open')
    $('.targets .my_targets').addClass('active open');

    @if($Target)
    /**
     * Dashboard Analytics
     */

    'use strict';

    (function () {
        let cardColor, headingColor, labelColor, shadeColor, grayColor;
        if (isDarkStyle) {
            cardColor = config.colors_dark.cardColor;
            labelColor = config.colors_dark.textMuted;
            headingColor = config.colors_dark.headingColor;
            shadeColor = 'dark';
            grayColor = '#5e9262'; // gray color is for stacked bar chart
        } else {
            cardColor = config.colors.cardColor;
            labelColor = config.colors.textMuted;
            headingColor = config.colors.headingColor;
            shadeColor = '';
            grayColor = '#8d7d7d';
        }


        // Support Tracker - Radial Bar Chart
        // --------------------------------------------------------------------
        @php($targetpercent = $AllFactorPrices / $Target->target_price * 100)
        const supportTrackerEl = document.querySelector('#supportTracker'),
            supportTrackerOptions = {
                series: [{{ round($targetpercent) }}],
                labels: ['مجموع فروش بر اساس تارگت'],
                chart: {
                    height: 360,
                    type: 'radialBar'
                },
                plotOptions: {
                    radialBar: {
                        offsetY: 10,
                        startAngle: -140,
                        endAngle: {{ -90 + $targetpercent }},
                        hollow: {
                            size: '65%'
                        },
                        track: {
                            background: cardColor,
                            strokeWidth: '100%'
                        },
                        dataLabels: {
                            name: {
                                offsetY: -20,
                                color: labelColor,
                                fontSize: '13px',
                                fontWeight: '400',
                                fontFamily: 'font-primary'
                            },
                            value: {
                                offsetY: 10,
                                color: headingColor,
                                fontSize: '38px',
                                fontWeight: '500',
                                fontFamily: 'font-primary'
                            }
                        }
                    }
                },
                colors: ['#FF9294'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        shadeIntensity: 0.5,
                        gradientToColors: ['green'],
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 0.6,
                        stops: [30, 70, 100]
                    }
                },
                stroke: {
                    dashArray: 10
                },
                grid: {
                    padding: {
                        top: -20,
                        bottom: 5
                    }
                },
                states: {
                    hover: {
                        filter: {
                            type: 'none'
                        }
                    },
                    active: {
                        filter: {
                            type: 'none'
                        }
                    }
                },
                responsive: [
                    {
                        breakpoint: 1025,
                        options: {
                            chart: {
                                height: 330
                            }
                        }
                    },
                    {
                        breakpoint: 769,
                        options: {
                            chart: {
                                height: 280
                            }
                        }
                    }
                ]
            };
        if (typeof supportTrackerEl !== undefined && supportTrackerEl !== null) {
            const supportTracker = new ApexCharts(supportTrackerEl, supportTrackerOptions);
            supportTracker.render();
        }


    })();
    @endif



</script>
</body>

</html>

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
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/swiper/swiper.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />
    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/pages/cards-advance.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
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

        .store_box {
            border-radius: 20px;
            color: #000
        }

        .store_box.purple {
            border: 2px solid #543C92;
        }

        .store_box.green {
            border: 2px solid #248230;
        }

        .store_box.gold {
            border: 2px solid #A57900;
        }

        .store_box.red {
            border: 2px solid #C1292E;
        }

        .store_box .content {
            font-size: 18px
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
                        <div class="row mb-3">
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100 store_box purple">
                                    <div class="table-responsive d-flex justify-content-between">
                                        <div class="title p-3">
                                            <svg width="21" height="18" viewBox="0 0 21 18" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M6.85604 15.4001C6.85604 15.8244 6.7018 16.2313 6.42725 16.5314C6.15269 16.8314 5.78032 17 5.39204 17C5.00376 17 4.63138 16.8314 4.35683 16.5314C4.08227 16.2313 3.92803 15.8244 3.92803 15.4001M6.85604 15.4001C6.85604 14.9757 6.7018 14.5688 6.42725 14.2687C6.15269 13.9687 5.78032 13.8001 5.39204 13.8001C5.00376 13.8001 4.63138 13.9687 4.35683 14.2687C4.08227 14.5688 3.92803 14.9757 3.92803 15.4001M6.85604 15.4001H12.7121M3.92803 15.4001H2.09802C1.80681 15.4001 1.52753 15.2736 1.32162 15.0486C1.1157 14.8236 1.00002 14.5183 1.00002 14.2001V10.6002M12.7121 15.4001H14.9081M12.7121 15.4001V10.6002M1.00002 10.6002V2.45646C0.998467 2.16439 1.09612 1.88207 1.27407 1.66414C1.45202 1.44622 1.69762 1.30819 1.96333 1.27677C5.2164 0.907744 8.49569 0.907744 11.7488 1.27677C12.3002 1.33863 12.7121 1.85062 12.7121 2.45646V3.4783M1.00002 10.6002H12.7121M17.8361 15.4001C17.8361 15.8244 17.6819 16.2313 17.4073 16.5314C17.1327 16.8314 16.7604 17 16.3721 17C15.9838 17 15.6114 16.8314 15.3369 16.5314C15.0623 16.2313 14.9081 15.8244 14.9081 15.4001M17.8361 15.4001C17.8361 14.9757 17.6819 14.5688 17.4073 14.2687C17.1327 13.9687 16.7604 13.8001 16.3721 13.8001C15.9838 13.8001 15.6114 13.9687 15.3369 14.2687C15.0623 14.5688 14.9081 14.9757 14.9081 15.4001M17.8361 15.4001H18.9341C19.5402 15.4001 20.036 14.8625 19.9979 14.2012C19.8018 10.6788 18.7164 7.28464 16.862 4.3956C16.6854 4.12498 16.4548 3.90177 16.1877 3.74285C15.9205 3.58394 15.6238 3.49348 15.32 3.4783H12.7121M12.7121 3.4783V10.6002"
                                                    stroke="#543C92" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                            در حال ارسال
                                        </div>
                                        <div class="content p-3">
                                            5 سفر فعال
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100 store_box green">
                                    <div class="table-responsive d-flex justify-content-between">
                                        <div class="title p-3">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M7 10.75L9.25 13L13 7.75M19 10C19 11.1819 18.7672 12.3522 18.3149 13.4442C17.8626 14.5361 17.1997 15.5282 16.364 16.364C15.5282 17.1997 14.5361 17.8626 13.4442 18.3149C12.3522 18.7672 11.1819 19 10 19C8.8181 19 7.64778 18.7672 6.55585 18.3149C5.46392 17.8626 4.47177 17.1997 3.63604 16.364C2.80031 15.5282 2.13738 14.5361 1.68508 13.4442C1.23279 12.3522 1 11.1819 1 10C1 7.61305 1.94821 5.32387 3.63604 3.63604C5.32387 1.94821 7.61305 1 10 1C12.3869 1 14.6761 1.94821 16.364 3.63604C18.0518 5.32387 19 7.61305 19 10Z"
                                                    stroke="#248230" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>

                                            تحویل شده
                                        </div>
                                        <div class="content p-3">
                                            18 سفارش
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100 store_box gold">
                                    <div class="table-responsive d-flex justify-content-between">
                                        <div class="title p-3">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M7 10.75L9.25 13L13 7.75M19 10C19 11.1819 18.7672 12.3522 18.3149 13.4442C17.8626 14.5361 17.1997 15.5282 16.364 16.364C15.5282 17.1997 14.5361 17.8626 13.4442 18.3149C12.3522 18.7672 11.1819 19 10 19C8.8181 19 7.64778 18.7672 6.55585 18.3149C5.46392 17.8626 4.47177 17.1997 3.63604 16.364C2.80031 15.5282 2.13738 14.5361 1.68508 13.4442C1.23279 12.3522 1 11.1819 1 10C1 7.61305 1.94821 5.32387 3.63604 3.63604C5.32387 1.94821 7.61305 1 10 1C12.3869 1 14.6761 1.94821 16.364 3.63604C18.0518 5.32387 19 7.61305 19 10Z"
                                                    stroke="#248230" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>

                                            در صف ارسال
                                        </div>
                                        <div class="content p-3">
                                            5 سفارش
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100 store_box red">
                                    <div class="table-responsive d-flex justify-content-between">
                                        <div class="title p-3">
                                            <svg width="19" height="18" viewBox="0 0 19 18" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M13.2934 6.4288H18L15.0009 3.34278C14.0353 2.34986 12.8326 1.63582 11.5136 1.27245C10.1946 0.909079 8.80581 0.909186 7.48686 1.27276C6.16791 1.63633 4.96528 2.35055 3.99987 3.34362C3.03445 4.3367 2.34028 5.57362 1.98713 6.93005M1.00094 16.4111V11.5712M1.00094 11.5712H5.7075M1.00094 11.5712L3.99911 14.6572C4.96467 15.6501 6.16741 16.3642 7.48641 16.7276C8.80541 17.0909 10.1942 17.0908 11.5131 16.7272C12.8321 16.3637 14.0347 15.6495 15.0001 14.6564C15.9655 13.6633 16.6597 12.4264 17.0129 11.07M18 1.5889V6.42686"
                                                    stroke="#C1292E" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>

                                            برگشت خورده
                                        </div>
                                        <div class="content p-3">
                                            3 سفارش
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

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
                                                موجودی کالا
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
                                                    <th>نام کالا</th>
                                                    <th>کد کالا</th>
                                                    <th>واحد کالا</th>
                                                    <th>مقدار مانده</th>
                                                    <th>کسری سفارشات</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
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
                                                <svg width="18" height="22" viewBox="0 0 18 22"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M16.4453 13.25V10.625C16.4453 9.72989 16.0897 8.87145 15.4568 8.23851C14.8239 7.60558 13.9654 7.25 13.0703 7.25H11.5703C11.2719 7.25 10.9858 7.13147 10.7748 6.9205C10.5638 6.70952 10.4453 6.42337 10.4453 6.125V4.625C10.4453 3.72989 10.0897 2.87145 9.4568 2.23851C8.82386 1.60558 7.96542 1.25 7.07031 1.25H5.19531M7.44531 1.25H2.57031C1.94931 1.25 1.44531 1.754 1.44531 2.375V19.625C1.44531 20.246 1.94931 20.75 2.57031 20.75H15.3203C15.9413 20.75 16.4453 20.246 16.4453 19.625V10.25C16.4453 7.86305 15.4971 5.57387 13.8093 3.88604C12.1214 2.19821 9.83226 1.25 7.44531 1.25Z"
                                                        stroke="#248230" stroke-width="1.5" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                حواله خروج
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="card-datatable table-responsive tablelist py-0">
                                        <table class="datatables-direct-basic table table-wrap">
                                            <thead>
                                                <tr>
                                                    <th width="20">#</th>
                                                    <th class="big">شماره حواله</th>
                                                    <th class="big">تاریخ خروج</th>
                                                    <th>نام مشتری</th>
                                                    <th class="big">ارزش حواله <small>ریال</small></th>
                                                    <th>وضعیت</th>
                                                    <th>اولویت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
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

                                                آماده بارگیری
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
                        @include('sections.warehouse_dashboard')
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

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>فاکتورهای کاربر - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">

    <!-- FixedHeader CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.css') }}">
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-table/bootstrap-table.min.css') }}">

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />

    <style>
        .dt-search {
            display: inline-block;
            background: #D0E4D3;
            width: 300px;
            padding: 10px;
            border-top-right-radius: 10px;
            border-top-left-radius: 10px;
            margin-top: 20px;
        }
    </style>
    <style>
        thead input {
            width: 100%;
            padding: 3px;
            font-size: 0.875rem;
        }

        table.dataTable thead th {
            text-align: center;
            vertical-align: middle;
        }

        .dt-buttons {
            float: right;
            flex-direction: row-reverse;
        }

        .table th input::placeholder {
            font-size: 11px;
        }

        [dir=rtl] .dropdown-toggle:not(.dropdown-toggle-split)::after {
            margin-right: 0;
            margin-left: 0.5em;
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
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <h4 class="py-3 mb-4">
                                    <span class="text-muted fw-light">کاربران /</span>
                                    لیست فاکتورهای کاربر
                                    <span style="font-size: 18px">{{ $cur_user->name }}</span>
                                </h4>
                            </div>
                            <div class="col-12 col-md-6 text-end">
                                <a href="{{ asset('/') }}" class="btn btn-label-dark waves-effect ms-3 mt-2"
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
                        <!-- Sticky Actions -->
                        <div class="row mb-3">
                            <div class="col-12 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between py-2">
                                        <div class="card-title mb-0">
                                            <h5 class="m-0 me-2">اطلاعات کاربر</h5>
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
                                                                <h6 class="mb-0">{{ $cur_user->name }}</h6>
                                                                @foreach ($cur_user->roles as $role)
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

                                                                سرپرست:
                                                            </span>
                                                            <?php $Leader = App\Models\User::find($cur_user->leader_id); ?>
                                                            <strong
                                                                class="text-dark">{{ $Leader ? $Leader->name : 'ندارد' }}</strong>
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

                                                                مسیر های فعال: </span>
                                                            <strong class="text-dark">{{ count($MyTasks) }} مسیر
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
                                                                تاریخ شروع تارگت
                                                            </span>
                                                            <strong
                                                                class="text-dark">{{ isset($Target) ? $Target->start_date_fa : 'تارگت ندارید' }}</strong>
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
                                                                class="text-dark">{{ isset($Target) ? $Target->end_date_fa : 'تارگت ندارید' }}</strong>
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
                                            <p class="text-info" style="font-size: 13px;font-weight: bold">تا این لحظه
                                                {{ number_format($AllFactorPrices) }} ریال از تارگت خود را به دست آورده
                                                اید.</p>
                                            <p class="text-warning" style="font-size: 12px;font-weight: bold">مبلغ
                                                فاکتورهای تایید شده تا این لحظه:
                                                {{ number_format($AcceptedFactorFullPrices) }}</p>
                                            <p class="text-success" style="font-size: 12px;font-weight: bold">مبلغ
                                                فاکتورهای تحویل شده به مشتریان تا این لحظه:
                                                {{ number_format($CompletedFactorFullPrices) }}</p>
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
                                                        {{ number_format($AllFactorPrices) }}</strong></p>
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
                        <div class="row mt-5">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <form action="{{ url()->current() }}" method="POST">
                                    @csrf
                                    <div class="col-12 card mb-4">
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="form-group col-6 col-md-3 mb-3">
                                                    <label for="from_date">نمایش از تاریخ:</label>
                                                    <input type="text" class="form-control" name="from_date"
                                                        id="from_date" data-jdp>
                                                </div>

                                                <div class="form-group col-6 col-md-3 mb-3">
                                                    <label for="to_date">نمایش تا تاریخ:</label>
                                                    <input type="text" class="form-control" name="to_date"
                                                        id="to_date" data-jdp>
                                                </div>

                                                <div class="form-group col-6 col-md-3">
                                                    <label for="delivery_from_date">نمایش از تاریخ تحویل:</label>
                                                    <input type="text" class="form-control"
                                                        name="delivery_from_date" id="delivery_from_date" data-jdp>
                                                </div>

                                                <div class="form-group col-6 col-md-3">
                                                    <label for="delivery_to_date">نمایش تا تاریخ تحویل:</label>
                                                    <input type="text" class="form-control"
                                                        name="delivery_to_date" id="delivery_to_date" data-jdp>
                                                </div>
                                                <div class="form-group col-6 col-md-3 mb-3">
                                                    <label for="region">نمایش بر اساس منطقه:</label>
                                                    <input type="text" class="form-control" name="region"
                                                        id="region" data-jdp>
                                                </div>
                                                <div class="form-group col-6 col-md-3 mb-3">
                                                    <label for="area">نمایش بر اساس مسیر:</label>
                                                    <input type="text" class="form-control" name="area"
                                                        id="area" data-jdp>
                                                </div>
                                                <div class="form-group col-6 col-md-3 mb-3">
                                                    <label for="area">نمایش بر اساس مشتری:</label>
                                                    <input type="text" class="form-control" name="area"
                                                        id="area" data-jdp>
                                                </div>
                                                <div class="form-group col-6 col-md-3 d-flex align-items-center">
                                                    <button type="submit" class="btn btn-info">فیلتر
                                                        فاکتورها</button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <div class="card">
                                    <div class="card-datatable table-responsive pt-3 px-1">
                                        <style>
                                            .table tr th,
                                            .table tr td {
                                                padding: 7px !important;
                                            }

                                            .dataTables_filter {
                                                width: 365px
                                            }
                                        </style>
                                        <table id="example"
                                            class="table table-bordered table-striped table-hover w-100">
                                            <thead>
                                                <tr class="text-center align-middle">
                                                    <th width="30"
                                                        style="width: 50px !important; max-width: 30px !important;">
                                                        ردیف</th>
                                                    <th>تاریخ ثبت</th>
                                                    <th>شماره فاکتور</th>
                                                    @if (auth()->user()->isAdmin == 1)
                                                        <th width="7%">واحد پخش</th>
                                                    @endif
                                                    <th class="bigcol">تابلو خریدار</th>
                                                    <th class="bigcol">نام خریدار</th>
                                                    <th width="80">مجموع مقدار</th>
                                                    <th>واحد سنجش</th>
                                                    <th width="10%">تاریخ تحویل</th>
                                                    <th width="12%">مبلغ کل</th>
                                                    @if ($isLeader == false)
                                                        <th>بازاریاب</th>
                                                    @endif
                                                    <th class="bigcol">سرپرست</th>
                                                    <th>وضعیت</th>
                                                    <th class="text-center">عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: #fff">

                                                @php($x = 1)
                                                @foreach ($AllFactors as $invoice)
                                                    <?php $organ = App\Models\Organization::find($invoice->organization_id); ?>
                                                    <tr>
                                                        <th width="30" class="text-center">
                                                            <small>{{ $x }}</small></th>
                                                        <td class="text-center"><small data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="ساعت {{ Verta($invoice->created_at)->format('H:i') }}">{{ Verta($invoice->created_at)->format('Y-m-d') }}</small>
                                                        </td>
                                                        <td class="text-center"><small>{{ $invoice->invoiceID }}
                                                            </small></td>
                                                        @if (auth()->user()->isAdmin == 1)
                                                            <td width="7%">
                                                                {{ $invoice->organization ? $invoice->organization->title : '---' }}
                                                            </td>
                                                        @endif
                                                        <td class="bigcol"><a
                                                                href="{{ route('pishFactorInfo', $invoice->id) }}">{{ isset($invoice->customer->tablo) ? $invoice->customer->tablo : '' }}</a>
                                                        </td>
                                                        <td class="bigcol"><a
                                                                href="{{ route('pishFactorInfo', $invoice->id) }}">{{ isset($invoice->customer->name) ? $invoice->customer->name : '' }}</a>
                                                        </td>
                                                        <td class="text-center" width="80">
                                                            <?php $details = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->count(); ?>
                                                            <small data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ $details }} قلم">
                                                                <?php
                                                                $Packs = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('pack');
                                                                $tedad = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('tedad');
                                                                ?>
                                                                @if ($Packs > 0)
                                                                    {{ $Packs }}
                                                                @else
                                                                    {{ $tedad }}
                                                                @endif

                                                            </small>
                                                        </td>
                                                        <td class="text-center">

                                                            @if ($Packs > 0)
                                                                {{ $organ->sub_unit }}
                                                            @else
                                                                {{ $organ->unit_order }}
                                                            @endif

                                                        </td>
                                                        <td class="text-center" width="10%">
                                                            <small>{{ $invoice->recive_date ? $invoice->recive_date : 'وارد نشده' }}</small>
                                                        </td>
                                                        <td width="12%">

                                                            <small>
                                                                {{ number_format(intval(str_replace(',', '', $invoice->fullPrice))) }}
                                                            </small>
                                                        </td>
                                                        <?php $Visitor = App\Models\User::find($invoice->visitor_id); ?>
                                                        <td><small>{{ $Visitor ? $Visitor->name : 'نامشخص' }}</small>
                                                        </td>
                                                        @if ($isLeader == false)
                                                            <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                                            <td class="bigcol">
                                                                <small>{{ $leader ? $leader->name : 'نامشخص' }}</small>
                                                            </td>
                                                        @endif
                                                        <td>
                                                            @if ($invoice->status == 0)
                                                                <span class="badge bg-label-warning me-1">منتظر
                                                                    تایید</span> <br />
                                                            @elseif($invoice->status == 1)
                                                                @if ($invoice->step == 2)
                                                                    <small
                                                                        class="badge bg-label-success send_to_store_status me-1">تایید
                                                                        شده - ارسال به انبار</small> <br />
                                                                @elseif($invoice->step == 3)
                                                                    <small
                                                                        class="badge bg-label-success shipment_status me-1">تایید
                                                                        شده - باربری و پخش</small> <br />
                                                                @elseif($invoice->step == 4)
                                                                    <small
                                                                        class="badge bg-label-success arrived_status me-1">تایید
                                                                        شده - تحویل به مشتری</small> <br />
                                                                @else
                                                                    <small
                                                                        class="badge bg-label-success accepted_status me-1">تایید
                                                                        شده</small> <br />
                                                                @endif
                                                            @elseif($invoice->status == 3)
                                                                <span class="badge bg-label-danger me-1">رد شده</span>
                                                                <br />
                                                            @elseif($invoice->status == 4)
                                                                <small
                                                                    class="badge bg-label-success arrived_status me-1">تایید
                                                                    شده - تحویل به مشتری</small> <br />
                                                            @elseif($invoice->status == 5)
                                                                <span class="badge bg-label-warning me-1">مرجوعی</span>
                                                                <br />
                                                            @endif


                                                        </td>
                                                        <td class="text-center">
                                                            <a class="d-inline-block me-3"
                                                                href="{{ route('pishFactorInfo', $invoice->id) }}"><svg
                                                                    width="24" height="21" viewBox="0 0 14 11"
                                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M1.02324 5.6932C0.981836 5.56875 0.981836 5.43425 1.02324 5.3098C1.85544 2.806 4.21764 1 7.00164 1C9.78444 1 12.1454 2.8042 12.9794 5.3068C13.0214 5.431 13.0214 5.5654 12.9794 5.6902C12.1478 8.194 9.78564 10 7.00164 10C4.21884 10 1.85724 8.1958 1.02324 5.6932Z"
                                                                        stroke="#248230" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                    <path
                                                                        d="M8.80312 5.49995C8.80312 5.97734 8.61348 6.43518 8.27592 6.77274C7.93835 7.11031 7.48051 7.29995 7.00312 7.29995C6.52574 7.29995 6.0679 7.11031 5.73033 6.77274C5.39277 6.43518 5.20312 5.97734 5.20312 5.49995C5.20312 5.02256 5.39277 4.56472 5.73033 4.22716C6.0679 3.88959 6.52574 3.69995 7.00312 3.69995C7.48051 3.69995 7.93835 3.88959 8.27592 4.22716C8.61348 4.56472 8.80312 5.02256 8.80312 5.49995Z"
                                                                        stroke="#248230" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>
                                                            </a>
                                                            @if (auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                                <form class="d-inline"
                                                                    action="{{ route('pishfactor.destroy', $invoice->id) }}"
                                                                    method="POST"
                                                                    onsubmit="return confirm('آیا از حذف فاکتور مورد نظر اطمینان دارید؟');">
                                                                    @csrf
                                                                    <button type="submit" class="d-inline"
                                                                        style="border: 0 none; background: transparent">
                                                                        <svg width="13" height="14"
                                                                            viewBox="0 0 13 14" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <path
                                                                                d="M8.31739 5.00023L8.08672 11.0001M4.89472 11.0001L4.66406 5.00023M11.3094 2.86028C11.5374 2.89495 11.7641 2.93162 11.9907 2.97095M11.3094 2.86028L10.5974 12.1154C10.5683 12.4922 10.3981 12.8441 10.1207 13.1008C9.84337 13.3576 9.47933 13.5001 9.10139 13.5H3.88006C3.50212 13.5001 3.13807 13.3576 2.86071 13.1008C2.58335 12.8441 2.41312 12.4922 2.38406 12.1154L1.67206 2.86028M11.3094 2.86028C10.54 2.74397 9.76657 2.65569 8.99072 2.59562M1.67206 2.86028C1.44406 2.89428 1.21739 2.93095 0.990723 2.97028M1.67206 2.86028C2.44148 2.74397 3.21488 2.65569 3.99072 2.59562M8.99072 2.59562V1.98497C8.99072 1.19833 8.38406 0.542346 7.59739 0.51768C6.8598 0.494107 6.12165 0.494107 5.38406 0.51768C4.59739 0.542346 3.99072 1.199 3.99072 1.98497V2.59562M8.99072 2.59562C7.32654 2.46701 5.65491 2.46701 3.99072 2.59562"
                                                                                stroke="#C1292E"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round" />
                                                                        </svg>

                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php($x++)
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th width="30"
                                                        style="width: 50px !important; max-width: 30px !important;">
                                                    </th>
                                                    <th></th>
                                                    <th></th>
                                                    @if (auth()->user()->isAdmin == 1)
                                                        <th width="7%"></th>
                                                    @endif
                                                    <th class="bigcol"></th>
                                                    <th class="bigcol"></th>
                                                    <th width="80"></th>
                                                    <th></th>
                                                    <th width="10%"></th>
                                                    <th width="12%">{{ number_format($totalPrice) }} </th>
                                                    @if ($isLeader == false)
                                                        <th></th>
                                                    @endif
                                                    <th class="bigcol"></th>
                                                    <th></th>
                                                    <th class="text-center"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
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
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    @include('partials.assets.datatables-scripts', ['bundle' => 'full'])
    <style>
        .dt-scroll-body table thead {
            display: none !important;
        }

        #example_length {
            text-align: left;
        }
    </style>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <link href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script>
        $('.users ').addClass('open')
        $('.users  .userslist').addClass('active open')
        // datatable (jquery)
    </script>
    <script>
        $(document).ready(function() {
            jalaliDatepicker.startWatch();
            let table = $('#example').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                pageLength: 50, // نمایش 50 ردیف به صورت پیش‌فرض

                language: {
                    url: '{{ asset('assets/vendor/libs/datatables-bs5/i18n/fa.json') }}'
                },
                dom: '<"row"<"col-md-6"l><"col-md-6 text-end"B>>rtip',
                buttons: [{
                        extend: 'excel',
                        text: 'اکسل'
                    },
                    {
                        extend: 'print',
                        text: 'پرینت'
                    },
                    {
                        extend: 'colvis',
                        text: 'نمایش ستون‌ها'
                    }
                ],
                initComplete: function() {
                    let api = this.api();

                    api.columns().eq(0).each(function(colIdx) {
                        let cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                        let title = $(cell).text();
                        $(cell).html('<input type="text" placeholder="جستجو ' + title + '" />');

                        $('input', $('.filters th').eq($(api.column(colIdx).header()).index()))
                            .off('keyup change')
                            .on('keyup change', function(e) {
                                e.stopPropagation();
                                api.column(colIdx).search(this.value).draw();
                            });
                    });
                }

            });
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

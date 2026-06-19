<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش جزئیات تارگت کاربر - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">

    <!-- FixedHeader CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.css') }}">
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-table/bootstrap-table.min.css') }}">

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
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
        @include('sections.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('sections.header')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-2">
                        <span class="text-muted fw-light">تارگت های فروش /</span>
                        ویرایش جزئیات تارگت کاربر
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-3">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <form class="card-body" method="POST" action="{{ route('targets.update',$Target->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="user_id">انتخاب کاربر</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="user_id" name="user_id">
                                                <option value="">انتخاب کنید</option>
                                                @foreach($Users as $user)
                                                    <option value="{{ $user['id'] }}" @if($Target->user_id == $user['id']) selected @endif >{{ $user['name'] }}</option>
                                                    @if(is_array($user['children']) && count($user['children']) > 0)
                                                        @foreach($user['children'] as $sub)
                                                            <option value="{{ $sub['id'] }}" @if($Target->user_id == $sub['id']) selected @endif > --> {{ $sub['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <hr class="my-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="start_date_fa">تاریخ شروع تارگت </label>
                                            <input class="form-control" id="start_date_fa" name="start_date_fa" placeholder="تاریخ شروع تارگت" value="{{ $Target->start_date_fa }}" type="text" data-jdp/>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="end_date_fa">تاریخ اتمام تارگت </label>
                                            <input class="form-control" id="end_date_fa" name="end_date_fa" placeholder="تاریخ اتمام تارگت" value="{{ $Target->end_date_fa }}" type="text" data-jdp/>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="orders_count">سقف تعداد فروش در تارگت</label>
                                            <input class="form-control" id="orders_count" placeholder="حداقل تعداد فروش" name="orders_count" value="{{ $Target->orders_count }}" type="number"/>
                                        </div>
                                        <div class="col-md-6">
                                            @php($Organ = DB::table('organizations')->where('id',auth()->user()->organization_id)->first())
                                            <label class="form-label" for="target_price">سقف مالی تارگت به ریال</label>
                                            <input class="form-control" id="target_price" placeholder="سقف مالی تارگت" name="target_price" value="{{ number_format($Target->target_price) }}" type="text"/>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="min_order_price">اعمال محدودیت حداقل مبلغ هر سفارش به ریال</label>
                                            <input class="form-control" id="min_order_price" name="min_order_price" value="@if($Target->min_order_price != null){{ number_format($Target->min_order_price) }}@endif" placeholder="محدودیت حداقل مبلغ هر سفارش" type="text"/>
                                        </div>
                                        <div class="row mt-4">

                                            <div class="form-group col-3 checkbox checkbox-primary">
                                                <input type="checkbox" name="status" id="status"
                                                    {{ $Target->status ? 'checked' : '' }}>
                                                <label for="status" class="cr">فعال</label>
                                            </div>
                                        </div>
                                        <hr class="my-3">
                                        <div class="col-12">
                                            <div class="col d-flex justify-content-end">
                                                <div class="col-2 mr-auto text-end mb-2">
                                                    <button class="btn btn-info additem" type="button">افزودن تارگت محصول جدید</button>
                                                </div>
                                            </div>
                                            <table class="table editfactor_table table-bordered">
                                                <thead>
                                                <tr class="text-center">
                                                    <th width="40">ردیف</th>
                                                    <th>انتخاب محصول</th>
                                                    <th>تعداد فروش</th>
                                                    <th>سقف مالی فروش</th>
                                                    <th>عملیات</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php($x = 1)
                                                @foreach($PrTargets as $prtr)
                                                    <tr class="item_{{ $x }}" data-item="{{ $x }}">
                                                        <td>{{ $x }}</td>
                                                        <td class="text-center">
                                                            <select class="select2 form-select" name="pr_id[]" >
                                                                <option value="">انتخاب کنید</option>
                                                                @foreach($Products as $product)
                                                                    <option value="{{ $product->id }}" @if($product->id == $prtr->pr_id) selected @endif>{{ $product->title }} {{ $product->display_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="order_count[]" value="{{ $prtr->order_count }}" />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="order_price[]" value="{{ $prtr->order_price }}" />
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="removeitem" style="cursor:pointer;"><svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923" stroke="#FF0000" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                                        </td>
                                                    </tr>
                                                    @php($x++)
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="pt-4">
                                        <button class="btn btn-primary me-sm-3 me-1" type="submit">به روزرسانی</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @php($Organ = DB::table('organizations')->where('id',auth()->user()->organization_id)->first())
                    <div class="row">
                        <!-- Support Tracker -->
                        <div class="col-12 col-md-3">
                            <div class="card mb-4 pt-3">
                                <div class="text-center px-3">
                                    <circle-progress text-format="vertical" indeterminateText="تارگت شما" max="{{ $Target->target_price }}" value="{{ $AllFactorPrices }}"></circle-progress>
                                    <p class="text-success" style="font-size: 13px;font-weight: bold">تا این لحظه {{ number_format($AllFactorPrices) }} ریال از تارگت خود را به دست آورده اید.</p>
                                    @if($Target->end_date_fa)
                                        <p class="text-warning" style="font-size: 13px">
                                            بازه تارگت از تاریخ {{ $Target->start_date_fa }} تا تاریخ {{ $Target->end_date_fa }} بوده است.
                                        </p>
                                    @endif
                                </div>
                                <div class="card green m-3">
                                    <div class="card-header py-2" style="background-color: #248230">
                                        <div class="card-title mb-0 text-white text-center">
                                            تارگت این ماه: <strong style="font-size: 18px">{{ number_format($Target->target_price) }}</strong> <small>ریال</small>
                                        </div>
                                    </div>
                                    <div class="card-body  pb-0" style="background-color: rgba(36,130,48,0.1)">
                                        <p class="text-danger text-center my-2">کسر شده از تارگت: <strong style="display: inline-block;direction: ltr"> - {{ number_format($AllFactorPrices) }}</strong></p>
                                        <?php $Mande = $Target->target_price - intval($AllFactorPrices) ?>
                                        <p class="text-success text-center">مانده به ریال: <strong style="display: inline-block;direction: ltr"> {{ number_format($Mande) }}</strong></p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-12 col-md-9">
                            <div class="card mb-4 pt-3">
                                <div class="card-header d-flex justify-content-between pt-0 pb-2">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">
                                            <svg width="21" height="25" viewBox="0 0 17 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15.75 12.75V10.125C15.75 9.22989 15.3944 8.37145 14.7615 7.73851C14.1286 7.10558 13.2701 6.75 12.375 6.75H10.875C10.5766 6.75 10.2905 6.63147 10.0795 6.4205C9.86853 6.20952 9.75 5.92337 9.75 5.625V4.125C9.75 3.22989 9.39442 2.37145 8.76149 1.73851C8.12855 1.10558 7.27011 0.75 6.375 0.75H4.5M6.75 0.75H1.875C1.254 0.75 0.75 1.254 0.75 1.875V19.125C0.75 19.746 1.254 20.25 1.875 20.25H14.625C15.246 20.25 15.75 19.746 15.75 19.125V9.75C15.75 7.36305 14.8018 5.07387 13.114 3.38604C11.4261 1.69821 9.13695 0.75 6.75 0.75Z" stroke="#248230" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>

                                            گزارشات تیم</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="card-datatable table-responsive tablelist py-0">
                                        <style>
                                            .table td, .table th {
                                                text-align: center !important;
                                            }
                                        </style>
                                        <table class="datatables-direct-basic table table-wrap">
                                            <thead>
                                            <tr>
                                                <th width="40">#</th>
                                                <th class="big">نام کاربر</th>
                                                <th>سمت کاربر</th>
                                                <th class="big">بازه تارگت</th>
                                                <th>سقف تارگت</th>
                                                <th class="big">مقدار محقق شده</th>
                                                <th>تعداد فاکتور ها</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php($x = 1)
                                            @php($SumPrices = 0)
                                            @foreach ($teamTree as $team)
                                                <tr>
                                                    <th>{{ $x }}</th>
                                                    <td class="big">
                                                        <small><a href="{{ route('targets.edit',$team['targetID']) }}">{{ $team['name'] }}</a></small>
                                                    </td>

                                                    <td><small>{{ $team['role'] }}</small></td>
                                                    <td class="big"><small>{{ $team['target_period'] }}</small></td>
                                                    <td class="big"><small>{{ number_format(intval($team['target_price'])) }}</small></td>
                                                    <td class="big"><small>{{ number_format(intval($team['FactorPrices'])) }}</small></td>
                                                    <td class="big"><small>{{ number_format(intval($team['factors_count'])) }}</small></td>

                                                </tr>
                                                @php($x++)
                                                @php($SumPrices += intval($team['FactorPrices']))
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <hr />
                                        <p>مجموع مبلغ فاکتور ها: {{ number_format($SumPrices) }} ریال</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-3" >
                        <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card px-1">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="card-title mb-0">
                                        <h5 class="mb-0">
                                            <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.770508 1.09766H2.15651C2.66651 1.09766 3.11151 1.44066 3.24351 1.93266L3.62651 3.36966M3.62651 3.36966C9.19715 3.21354 14.7624 3.83281 20.1625 5.20966C19.3385 7.66366 18.3595 10.0477 17.2385 12.3477H6.02051M3.62651 3.36966L6.02051 12.3477M6.02051 12.3477C5.22486 12.3477 4.4618 12.6637 3.89919 13.2263C3.33658 13.7889 3.02051 14.552 3.02051 15.3477H18.7705M4.52051 18.3477C4.52051 18.5466 4.44149 18.7373 4.30084 18.878C4.16019 19.0186 3.96942 19.0977 3.77051 19.0977C3.5716 19.0977 3.38083 19.0186 3.24018 18.878C3.09953 18.7373 3.02051 18.5466 3.02051 18.3477C3.02051 18.1487 3.09953 17.958 3.24018 17.8173C3.38083 17.6767 3.5716 17.5977 3.77051 17.5977C3.96942 17.5977 4.16019 17.6767 4.30084 17.8173C4.44149 17.958 4.52051 18.1487 4.52051 18.3477ZM17.2705 18.3477C17.2705 18.5466 17.1915 18.7373 17.0508 18.878C16.9102 19.0186 16.7194 19.0977 16.5205 19.0977C16.3216 19.0977 16.1308 19.0186 15.9902 18.878C15.8495 18.7373 15.7705 18.5466 15.7705 18.3477C15.7705 18.1487 15.8495 17.958 15.9902 17.8173C16.1308 17.6767 16.3216 17.5977 16.5205 17.5977C16.7194 17.5977 16.9102 17.6767 17.0508 17.8173C17.1915 17.958 17.2705 18.1487 17.2705 18.3477Z" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            سفارشات</h5>
                                    </div>
                                </div>
                                <div class="card-datatable table-responsive tablelist py-0">
                                    <style>
                                        .table td, .table th {
                                            text-align: center !important;
                                        }
                                    </style>
                                    <table id="example" class="datatables-direct-basic table table-wrap">
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
                                        @php($SumPrices = 0)
                                        @foreach ($AllFactors as $factor)
                                                <?php $Organ = App\Models\Organization::find($factor->organization_id); ?>
                                            <tr>
                                                <th>{{ $x }}</th>
                                                <td class="big"><small><a href="{{ route('pishFactorInfo', $factor->id) }}">{{ $factor->customer->customer_code }}</a></small></td>
                                                <td class="big">
                                                    <small data-bs-toggle="tooltip" data-bs-placement="top"
                                                           data-bs-custom-class="custom-tooltip"
                                                           data-bs-title="{{ $factor->customer->name }}">
                                                        <a href="{{ route('pishFactorInfo', $factor->id) }}">
                                                            {{ strlen($factor->customer->name) > 12 ?  mb_substr($factor->customer->name, 0, 15, 'UTF-8').'...'  : $factor->customer->name }}
                                                        </a>
                                                    </small>
                                                </td>

                                                <td><small><a href="{{ route('pishFactorInfo', $factor->id) }}">{{ $factor->invoiceID }}</a></small></td>
                                                <td class="big"><small>{{ $factor->recive_date }}</small></td>
                                                <td>
                                                        <?php $details = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->count(); ?>
                                                    <small data-bs-toggle="tooltip" data-bs-placement="top"
                                                           data-bs-custom-class="custom-tooltip"
                                                           data-bs-title="{{ $details }} قلم">
                                                            <?php
                                                            $Packs = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->sum('pack');
                                                            $tedad = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->sum('tedad');
                                                            ?>
                                                        @if($Packs > 0) {{ $Packs }} {{ isset($Organ) ? $Organ->sub_unit : '-' }} @endif
                                                        @if($tedad > 0) {{ $tedad }} {{ isset($Organ) ? $Organ->unit_order : '-' }} @endif
                                                    </small>
                                                </td>
                                                <td class="big">
                                                    <small> {{ number_format(intval(str_replace(',','',$factor->fullPrice))) }}
                                                    </small>
                                                    @php($SumPrices += $factor->fullPrice)
                                                </td>
                                                <td class="big">
                                                    @if($factor->status == 0)
                                                        <span class="badge bg-label-warning me-1">منتظر تایید</span> <br />
                                                    @elseif($factor->status == 1)
                                                        @if($factor->step == 2)
                                                            <small class="badge bg-label-success send_to_store_status me-1" style="font-size: 9px;">تایید شده - ارسال به انبار</small> <br />
                                                        @elseif($factor->step == 3)
                                                            <small class="badge bg-label-success shipment_status me-1" style="font-size: 9px;">تایید شده - باربری و پخش</small> <br />
                                                        @elseif($factor->step == 4)
                                                            <small class="badge bg-label-success arrived_status me-1" style="font-size: 9px">تایید شده - تحویل به مشتری</small> <br />
                                                        @else
                                                            <small class="badge bg-label-success accepted_status me-1" style="font-size: 9px;">تایید شده</small> <br />
                                                        @endif
                                                    @elseif($factor->status == 3)
                                                        <span class="badge bg-label-danger me-1">رد شده</span> <br />
                                                    @elseif($factor->status == 4)
                                                        <small class="badge bg-label-success arrived_status me-1" style="font-size: 9px">تایید شده - تحویل به مشتری</small> <br />
                                                    @elseif($factor->status == 5)
                                                        <span class="badge bg-label-warning me-1">مرجوعی</span> <br />
                                                    @endif
                                                </td>
                                                <td class="big">
                                                    @if($factor->payment_type == 1)
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
                                    <hr />
                                    <p>مجموع مبلغ فاکتور ها: {{ number_format($SumPrices) }} ریال</p>
                                </div>
                            </div>
                        </div>
                    </div>
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
<script>
    $(document).ready(function() {
        jalaliDatepicker.startWatch();
    });
</script>

<script>
    $(document).ready(function() {
        $('.additem').click(function() {
            $('.editfactor_table tbody').append('<tr class="item_1" data-item="1"><td>1</td><td class="text-center"><select class="select2 form-select" name="pr_id[]" ><option value="">انتخاب کنید</option>@foreach($Products as $product)<option value="{{ $product->id }}">{{ $product->title }} {{ $product->display_name }}</option>@endforeach</select></td><td><input type="number" class="form-control" name="order_count[]" value="" /></td><td><input type="number" class="form-control" name="order_price[]" value="" /></td><td class="text-center"><span class="removeitem" style="cursor:pointer;"><svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923" stroke="#FF0000" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span></td></tr>');
        });

        $('body').on('click','.removeitem',function() {
            $(this).parents('tr').remove();
        });
    });

    document.getElementById('target_price').addEventListener('input', function(e) {
        let value = e.target.value;

        // Remove all non-digit characters except for a single decimal point
        value = value.replace(/[^0-9.]/g, '');

        // Handle multiple decimal points
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Convert to a number and format
        const numberValue = parseFloat(value);
        if (!isNaN(numberValue)) {
            e.target.value = numberValue.toLocaleString('en-US'); // Format for US locale
        } else {
            e.target.value = ''; // Clear if not a valid number
        }
    });

    document.getElementById('min_order_price').addEventListener('input', function(e) {
        let value = e.target.value;

        // Remove all non-digit characters except for a single decimal point
        value = value.replace(/[^0-9.]/g, '');

        // Handle multiple decimal points
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Convert to a number and format
        const numberValue = parseFloat(value);
        if (!isNaN(numberValue)) {
            e.target.value = numberValue.toLocaleString('en-US'); // Format for US locale
        } else {
            e.target.value = ''; // Clear if not a valid number
        }
    });

</script>
<script src="{{ asset('/js/circle-progress.min.js') }}" type="module"></script>
<style>
    circle-progress::part(base) {width: 150px; height: auto;}
    circle-progress::part(value) {
        stroke-width: 6px;
        stroke: #248230;
        stroke-linecap: round;
    }
    circle-progress::part(circle) {
        stroke-width: 8px;
        stroke: #D0E4D3;
    }
    circle-progress::part(text-value), circle-progress::part(text-max) {
        font-size: 11px;
        font-family: 'font-primary';
    }
</style>
<script>
    $(document).ready(function() {
        // اضافه کردن ردیف فیلتر در thead
        $('#example thead tr').clone(true).addClass('filters').appendTo('#example thead');

        let table = $('.datatables-direct-basic').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 50, // نمایش 50 ردیف به صورت پیش‌فرض

            language: {
                url: '{{ asset('assets/vendor/libs/datatables-bs5/i18n/fa.json') }}'
            },
            dom: '<"row"<"col-md-6"l><"col-md-6 text-end"B>>rtip',
            buttons: [
                { extend: 'excel', text: 'اکسل' },
                { extend: 'print', text: 'پرینت' },
                { extend: 'colvis', text: 'نمایش ستون‌ها' }
            ],
            initComplete: function () {
                let api = this.api();

                api.columns().eq(0).each(function (colIdx) {
                    let cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                    let title = $(cell).text();
                    $(cell).html('<input type="text" placeholder="جستجو ' + title + '" />');

                    $('input', $('.filters th').eq($(api.column(colIdx).header()).index()))
                        .off('keyup change')
                        .on('keyup change', function (e) {
                            e.stopPropagation();
                            api.column(colIdx).search(this.value).draw();
                        });
                });
            }
        });
    });

</script>
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
    #example_length {
        text-align: left;
    }
</style>
</body>

</html>

@if (Request::routeIs(['invoices.index']))
    @php($title = 'پیش فاکتورهای در انتظار')
@elseif(Request::routeIs(['invoices.active_list']))
    @php($title = 'فاکتورهای تایید شده')
@elseif(Request::routeIs(['invoices.denciled']))
    @php($title = 'سفارشات رد شده')
@elseif(Request::routeIs(['invoices.compeleted']))
    @php($title = 'فاکتورهای تحویل شده')
@elseif(Request::routeIs(['invoices.all_invoices']))
    @php($title = 'همه فاکتورها')
@elseif(Request::routeIs(['deliveries.compeleted']))
    @php($title = 'تایید شده های تحویل به مشتری')
@elseif(Request::routeIs(['regions.invoiceList']))
    @php($title = 'مرور فاکتور های منطقه')
@elseif(Request::routeIs(['areas.invoiceList']))
    @php($title = 'مرور فاکتور های مسیر')
@elseif(Request::routeIs(['customers.orders']))
    @php($title = 'مرور فاکتور های مسیر')
@endif
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>{{ $title }} - دکان دارمینو</title>
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
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- FixedHeader CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.bootstrap5.min.css">
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.css">

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
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
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">فاکتورها /</span>
                            {{ $title }}
                            @isset($pishFactorsTotal)
                                <small class="text-muted">({{ number_format($pishFactorsTotal) }} مورد)</small>
                            @endisset
                        </h4>
                        <!-- Sticky Actions -->
                        <form id="pishfactors-filter-form" action="{{ url()->current() }}" method="GET">
                            <div class="col-12 card mb-4">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="form-group col-6 col-md-2 mb-3">
                                            <label for="from_date">نمایش از تاریخ:</label>
                                            <input type="text" class="form-control" name="from_date" id="from_date"
                                                value="{{ ($filterValues ?? [])['from_date'] ?? '' }}" data-jdp>
                                        </div>

                                        <div class="form-group col-6 col-md-2 mb-3">
                                            <label for="to_date">نمایش تا تاریخ:</label>
                                            <input type="text" class="form-control" name="to_date" id="to_date"
                                                value="{{ ($filterValues ?? [])['to_date'] ?? '' }}" data-jdp>
                                        </div>

                                        <div class="form-group col-6 col-md-2">
                                            <label for="delivery_from_date">نمایش از تاریخ تحویل:</label>
                                            <input type="text" class="form-control" name="delivery_from_date"
                                                id="delivery_from_date" value="{{ ($filterValues ?? [])['delivery_from_date'] ?? '' }}" data-jdp>
                                        </div>

                                        <div class="form-group col-6 col-md-2">
                                            <label for="delivery_to_date">نمایش تا تاریخ تحویل:</label>
                                            <input type="text" class="form-control" name="delivery_to_date"
                                                id="delivery_to_date" value="{{ ($filterValues ?? [])['delivery_to_date'] ?? '' }}" data-jdp>
                                        </div>
                                        @if ($isLeader == false && $isVisitor == false)
                                            <div class="form-group col-6 col-md-2 mb-3">
                                                <label for="leader_id">نمایش بر اساس سرپرست:</label>
                                                <select class="select2 form-select" id="leader_id" name="leader_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($Cities as $city)
                                                        <option value="{{ $city->id }}">{{ $city->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-6 col-md-2 mb-3">
                                                <label for="visitor_id">نمایش بر اساس بازاریاب:</label>
                                                <select class="select2 form-select" id="visitor_id" name="visitor_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($Cities as $city)
                                                        <option value="{{ $city->id }}">{{ $city->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-6 col-md-3 mb-3">
                                                <label for="city_id">نمایش بر اساس شهر:</label>
                                                <select class="select2 form-select" data-allow-clear="true" multiple
                                                    id="city_id" name="city_id[]">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($Cities as $city)
                                                        <option value="{{ $city->id }}">{{ $city->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
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
                                        @endif


                                        <div class="form-group col-6 col-md-3 d-flex align-items-center">
                                            <button type="submit" class="btn btn-info">فیلتر تاریخ</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>

                        @if (auth()->user()->id == 1 && !isset($listKey))
                            <div class="row">
                                <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                    <table id="table" data-locale="fa-IR" data-toggle="table"
                                        data-sortable="true" data-show-columns="true"
                                        data-show-columns-toggle-all="true" data-show-pagination-switch="true"
                                        data-show-fullscreen="true" data-buttons="buttons" data-show-export="true"
                                        data-export-types='["excel", "csv"]' data-search="true">
                                        <thead>
                                            <tr class="text-center">
                                                <th data-width="20" data-sortable="true">#</th>
                                                <th class="text-center" data-sortable="true">تاریخ ثبت</th>
                                                <th class="text-center" data-sortable="true">شماره فاکتور</th>
                                                <th data-width="80" data-sortable="true">واحد پخش</th>
                                                <th class="bigcol" data-sortable="true">نام خریدار</th>
                                                <th width="80" data-sortable="true">مجموع مقدار</th>
                                                <th width="80" data-visible="false">واحد سنجش</th>
                                                <th width="10%" class="text-center" data-sortable="true">تاریخ
                                                    تحویل</th>
                                                <th width="12%" data-sortable="true">مبلغ کل <small>ریال</small>
                                                </th>
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
                                            @foreach ($PishFactors as $invoice)
                                                <?php $organ = App\Models\Organization::find($invoice->organization_id); ?>
                                                <tr>
                                                    <th width="30" class="text-center">
                                                        <small>{{ $x }}</small></th>
                                                    <td class="text-center"><small data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            data-bs-custom-class="custom-tooltip"
                                                            data-bs-title="ساعت {{ Verta($invoice->created_at)->format('H:i') }}">{{ Verta($invoice->created_at)->format('Y-m-d') }}</small>
                                                    </td>
                                                    <td class="text-center"><small>{{ $invoice->invoiceID }} </small>
                                                    </td>
                                                    <td width="7%">
                                                        {{ $invoice->organization ? $invoice->organization->title : '---' }}
                                                    </td>
                                                    <td class="bigcol">
                                                        <a href="{{ route('pishFactorInfo', $invoice->id) }}">
                                                            {{ $invoice->is_agency_order ? ($invoice->agencyUser ? $invoice->agencyUser->name : ($invoice->visitor ? $invoice->visitor->name : 'وارد نشده')) : (isset($invoice->customer->name) ? $invoice->customer->name : 'وارد نشده') }}
                                                        </a>
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
                                                    <td class="text-center" width="80">

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
                                                    <td><small>{{ $Visitor->name }}</small></td>
                                                    @if ($isLeader == false)
                                                        <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                                        <td class="bigcol"><small>{{ $leader->name }}</small></td>
                                                    @endif
                                                    <td>
                                                        @if ($invoice->status == 0)
                                                            <span class="badge bg-label-warning me-1">منتظر
                                                                تایید</span> <br />
                                                        @elseif($invoice->status == 1)
                                                            @if ($invoice->step == 2)
                                                                <span class="badge bg-label-success me-1">تایید شده -
                                                                    ارسال به انبار</span> <br />
                                                            @elseif($invoice->step == 3)
                                                                <span class="badge bg-label-success me-1">تایید شده -
                                                                    باربری و پخش</span> <br />
                                                            @elseif($invoice->step == 4)
                                                                <span class="badge bg-label-success me-1">تایید شده -
                                                                    تحویل به مشتری</span> <br />
                                                            @endif
                                                        @elseif($invoice->status == 3)
                                                            <span class="badge bg-label-danger me-1">رد شده</span>
                                                            <br />
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
                                                                            stroke="#C1292E" stroke-linecap="round"
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
                                    </table>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <form id="bulkForm" method="POST" action="{{ route('invoices.actions') }}">
                                @csrf
                                <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                    <!-- منوی عملیات -->
                                    <div class="dropdown amaliat mb-3">
                                        <button class="btn btn-primary dropdown-toggle " type="button"
                                            data-bs-toggle="dropdown">
                                            عملیات
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if (Request::routeIs(['invoices.index']))
                                                <li><button type="submit" name="accept" value="1"
                                                        class="dropdown-item bulk-action dencil">تایید همه انتخاب شده
                                                        ها</button></li>
                                                <li><button type="submit" name="dencel" value="1"
                                                        class="dropdown-item bulk-action accpet">رد کردن انتخاب شده
                                                        ها</button></li>
                                            @elseif(Request::routeIs(['invoices.active_list']))
                                                <li><button type="submit" name="waiting" value="1"
                                                        class="dropdown-item bulk-action waiting">تغییر به در
                                                        انتظار</button></li>
                                                <li><button type="submit" name="assign_to_store" value="1"
                                                        class="dropdown-item bulk-action assign_to_store">ارسال به
                                                        انبار</button></li>
                                                <li><button type="submit" name="dencel" value="1"
                                                        class="dropdown-item bulk-action accpet">رد کردن انتخاب شده
                                                        ها</button></li>
                                            @endif
                                        </ul>

                                    </div>
                                    <div class="card-datatable table-responsive pt-0">
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
                                                    <td><input type="checkbox" class="checkallactions"
                                                            value="1" /></td>
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
                                                @if (!isset($listKey))
                                                @php($x = 1)
                                                @foreach ($PishFactors as $invoice)
                                                    <?php $organ = App\Models\Organization::find($invoice->organization_id); ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="actions"
                                                                name="item_{{ $invoice->id }}" value="1" />
                                                        </td>
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
                                                                @foreach ($invoice->storeNames() as $storename)
                                                                    <small
                                                                        class="d-block badge bg-label-primary p-1 rounded mb-1"
                                                                        style="font-size: 11px">
                                                                        {{ $storename }}</small>
                                                                @endforeach
                                                            </td>
                                                        @endif
                                                        <td class="bigcol"><a
                                                                href="{{ route('pishFactorInfo', $invoice->id) }}">{{ isset($invoice->customer->tablo) ? $invoice->customer->tablo : '' }}</a>
                                                        </td>
                                                        <td class="bigcol">
                                                            <a href="{{ route('pishFactorInfo', $invoice->id) }}">
                                                                {{ $invoice->is_agency_order ? ($invoice->agencyUser ? $invoice->agencyUser->name : ($invoice->visitor ? $invoice->visitor->name : '')) : (isset($invoice->customer->name) ? $invoice->customer->name : '') }}
                                                            </a>
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
                                                                {{ isset($organ->sub_unit) ? $organ->sub_unit : '' }}
                                                            @else
                                                                {{ isset($organ->$organ->unit_order) ? $organ->$organ->unit_order : '' }}
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
                                                        <td><small>{{ $Visitor->name }}</small></td>
                                                        @if ($isLeader == false)
                                                            <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                                            <td class="bigcol"><small>{{ $leader->name }}</small></td>
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
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </form>
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
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    @if (auth()->user()->id == 1 && !isset($listKey))
        <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/libs/jsPDF/jspdf.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/extensions/export/bootstrap-table-export.min.js">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table-locale-all.min.js"></script>
    @endif


    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>


    <!-- Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <!-- FixedHeader -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
    <style>
        .dt-scroll-body table thead {
            display: none !important;
        }

        #example_length {
            text-align: left;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.all.min.js"></script>
    <link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.min.css" rel="stylesheet">
    <script>
        $(document).ready(function() {
            const datatableLanguage = {
                search: 'جستجو: ',
                searchPlaceholder: 'جستجو کنید...',
                info: 'نمایش _START_ تا _END_ از _TOTAL_ مورد',
                infoEmpty: 'موردی وجود ندارد.',
                infoFiltered: '(فیلتر شده از _MAX_ مورد)',
                lengthMenu: 'نمایش _MENU_ مورد در صفحه',
                zeroRecords: 'متاسفانه موردی پیدا نشد',
                processing: 'در حال بارگذاری...',
                paginate: {
                    previous: 'قبلی',
                    next: 'بعدی',
                }
            };

            @isset($listKey)
            const filterPayload = function() {
                return {
                    list: @json($listKey),
                    from_date: $('#from_date').val() || '',
                    to_date: $('#to_date').val() || '',
                    delivery_from_date: $('#delivery_from_date').val() || '',
                    delivery_to_date: $('#delivery_to_date').val() || '',
                    leader_id: $('#leader_id').val() || '',
                    visitor_id: $('#visitor_id').val() || '',
                    customer_id: @json(($filterValues ?? [])['customer_id'] ?? null),
                };
            };

            const columnCount = @json($datatableColumnCount ?? 13);
            const columns = [];
            const nonOrderableTargets = [0, columnCount - 1];

            for (let i = 0; i < columnCount; i++) {
                columns.push({ data: i });
                if (i === 0 || i === columnCount - 1) {
                    nonOrderableTargets.push(i);
                }
            }

            const dtPishFactors = $('#example').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                lengthChange: true,
                ordering: true,
                order: [[1, 'desc']],
                pageLength: 50,
                lengthMenu: [25, 50, 100],
                fixedHeader: true,
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
                ajax: {
                    url: @json(route('invoices.pishfactors.datatable')),
                    type: 'GET',
                    data: function(data) {
                        return Object.assign(data, filterPayload());
                    }
                },
                columns: columns,
                columnDefs: [
                    { targets: [0, columnCount - 1], orderable: false, searchable: false },
                ],
                language: datatableLanguage,
                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    $('.checkallactions').prop('checked', false);
                }
            });

            $('#pishfactors-filter-form').on('submit', function(event) {
                event.preventDefault();
                dtPishFactors.ajax.reload();
            });
            @else
            function normalizePersian(str) {
                if (!str) return '';
                return str
                    .toLowerCase()
                    .replace(/\u064A/g, 'ی')
                    .replace(/\u0643/g, 'ک')
                    .replace(/\u200C/g, '')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            @if ($isLeader == false && $isVisitor == false)
                $('#example thead tr').clone(true).addClass('filters').appendTo('#example thead');
            @endif

            $('#example').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                pageLength: 50,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/fa.json'
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

                    api.columns().every(function(colIdx) {
                        let cell = $('.filters th').eq(colIdx);
                        let title = $(api.column(colIdx).header()).text();

                        $(cell).html('<input type="text" placeholder="جستجو ' + title +
                            '" style="width:100%;">');

                        $('input', cell).on('keyup change clear', function() {
                            let searchVal = normalizePersian(this.value);

                            api.rows().every(function() {
                                let cellText = normalizePersian($(this.node())
                                    .find('td').eq(colIdx).text());
                                if (!searchVal || cellText.includes(searchVal)) {
                                    $(this.node()).show();
                                } else {
                                    $(this.node()).hide();
                                }
                            });
                        });
                    });
                }
            });
            @endisset
        });
    </script>
    <script>
        $(document).ready(function() {
            jalaliDatepicker.startWatch();

            $('.checkallactions').on('change', function() {
                $('.actions').prop('checked', $(this).prop('checked'));
            });

            $('.amaliat .dropdown-menu button').on('click', function(e) {
                e.preventDefault();

                let action = $(this).val();
                $('<input>').attr({
                    type: 'hidden',
                    name: $(this).attr('name'),
                    value: action
                }).appendTo($(this).closest('form'));

                // بررسی انتخاب شدن حداقل یک رکورد
                if ($('.actions:checked').length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'هیچ موردی انتخاب نشده!',
                        text: 'لطفا حداقل یک مورد را انتخاب کنید.',
                        confirmButtonText: 'باشه'
                    });

                } else {
                    $('#bulkForm').submit();
                }


            });
        });
    </script>
    <script>
        // DataTable Direct
        // --------------------------------------------------------------------
        /*  if(dt_without_ajax_table.length){
             dt_without_ajax = dt_without_ajax_table.DataTable({
                 searching: true,
                 lengthChange: false,
                 ordering: true,
                 order: [[0, 'asc']],
                 lengthMenu: [10, 25, 50, 75, 100, 150, 200],
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
                /* initComplete: function () {
                     this.api()
                         .columns()
                         .every(function () {
                             let column = this;

                             // Create select element
                             let select = document.createElement('select');
                             select.add(new Option(''));
                             column.footer().replaceChildren(select);

                             // Apply listener for user change in value
                             select.addEventListener('change', function () {
                                 column
                                     .search(select.value, {exact: true})
                                     .draw();
                             });

                             // Add list of options
                             column
                                 .data()
                                 .unique()
                                 .sort()
                                 .each(function (d, j) {
                                     let text = d.replace(/(<([^>]+)>)/gi, "");
                                     select.add(new Option(text));
                                 });
                         });
                 }*/
    </script>
</body>

</html>

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>اطلاعات مشتری {{ $Customer->name }} - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="https://static.neshan.org/sdk/mapboxgl/v1.13.2/neshan-sdk/v1.1.5/index.css" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/sweetalert2/sweetalert2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link
        href="{{ asset('assets/') }}/vendor/libs/@form-validation/form-validation.css" rel="stylesheet"/>
    <!-- Page CSS -->
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
</head>
<?php $organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
<body>
@include('sweetalert::alert')
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- Menu -->
        @include('sections.sidebar')
        <!-- / Menu -->
        <!-- Layout container -->
        <div class="layout-page">
            @include('sections.header')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="row justify-content-between">
                        <div class="col">
                            <h4 class="py-3 mb-2">
                                <span class="text-muted fw-light">لیست مشتریان /</span>
                                @if ($MyTask != null)
                                        <?php
                                        $task_area = DB::table('areas')->where('id', $MyTask->area_id)->first();
                                        $task_region = DB::table('regions')->where('id', $task_area->region_id)->first();
                                        ?>
                                    <span class="text-muted fw-light">مشتریان {{ $task_region->name }} - {{ $task_area->name }} /</span>
                                @endif
                                جزئیات و تاریخچه حساب مشتری
                            </h4>
                        </div>
                        <div class="col text-end">
                            <a href="{{ route('customers.360', $Customer->id) }}" class="btn btn-primary waves-effect mt-2">پرونده ۳۶۰</a>
                            <a href="{{ session('backlink') }}" class="btn btn-label-dark waves-effect ms-3 mt-2" type="button">
                                بازگشت
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>

                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between mb-4 text-center text-sm-start gap-2">
                        <div class="mb-2 mb-sm-0">
                            <h4 class="mb-1">شناسه مشتری #{{ $Customer->customer_code }}</h4>
                            <p class="mb-0">ثبت شده در تاریخ: {{ Verta($Customer->created_at)->format('Y/m/d H:i:s') }}</p>
                        </div>
                        @if (auth()->user()->isAdmin == 1 && $Customer->activeOrders->count() == 0)
                            <form action="{{ route('customers.destroy', $Customer->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('آیا از این مشتری اطمینان دارید؟');">
                                @method('delete')
                                @csrf
                                <button class="btn btn-label-danger delete-customer" type="submit">حذف مشتری</button>
                            </form>

                        @endif
                    </div>
                    <div class="row">
                        <!-- Customer-detail Sidebar -->
                        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                            <!-- Customer-detail Card -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="customer-avatar-section">
                                        <div class="d-flex align-items-center flex-column">
                                            <div class="customer-info text-center">
                                                <h4 class="mb-1">{{ $Customer->tablo }}</h4>
                                                <small>نام مشتری: <strong>{{ $Customer->name }}</strong></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-around flex-wrap my-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar">
                                                <div class="avatar-initial rounded bg-label-primary">
                                                    <i class="ti ti-shopping-cart ti-md"></i>
                                                </div>
                                            </div>
                                            <div class="gap-0 d-flex flex-column">
                                                <p class="mb-0 fw-medium">{{ count($Factors) }}</p>
                                                <small>سفارشات</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar">
                                                <div class="avatar-initial rounded bg-label-primary">
                                                    <i class="ti ti-currency-dollar ti-md"></i>
                                                </div>
                                            </div>
                                            <div class="gap-0 d-flex flex-column">
                                                <p class="mb-0 fw-medium">
                                                    <bdi><svg class="toman" width="1rem" height="1rem">
                                                            <use xlink:href="#toman">
                                                                <symbol id="toman" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                                                    <path clip-rule="evenodd" d="M3.057 1.742L3.821 1l.78.75-.776.741-.768-.749zm3.23 2.48c0 .622-.16 1.111-.478 1.467-.201.221-.462.39-.783.505a3.251 3.251 0 01-1.083.163h-.555c-.421 0-.801-.074-1.139-.223a2.045 2.045 0 01-.9-.738A2.238 2.238 0 011 4.148c0-.059.001-.117.004-.176.03-.55.204-1.158.525-1.827l1.095.484c-.257.532-.397 1-.419 1.403-.002.04-.004.08-.004.12 0 .252.055.458.166.618a.887.887 0 00.5.354c.085.028.178.048.278.06.079.01.16.014.243.014h.555c.458 0 .769-.081.933-.244.14-.139.21-.383.21-.731V2.02h1.2v2.202zm5.433 3.184l-.72-.7.709-.706.735.707-.724.7zm-2.856.308c.542 0 .973.19 1.293.569.297.346.445.777.445 1.293v.364h.18v-.004h.41c.221 0 .377-.028.467-.084.093-.055.14-.14.14-.258v-.069c.004-.243.017-1.044 0-1.115L13 8.05v1.574a1.4 1.4 0 01-.287.863c-.306.405-.804.607-1.495.607h-.627c-.061.733-.434 1.257-1.117 1.573-.267.122-.58.21-.937.265a5.845 5.845 0 01-.914.067v-1.159c.612 0 1.072-.082 1.38-.247.25-.132.376-.298.376-.499h-.515c-.436 0-.807-.113-1.113-.339-.367-.273-.55-.667-.55-1.18 0-.488.122-.901.367-1.24.296-.415.728-.622 1.296-.622zm.533 2.226v-.364c0-.217-.048-.389-.143-.516a.464.464 0 00-.39-.187.478.478 0 00-.396.187.705.705 0 00-.136.449.65.65 0 00.003.067c.008.125.066.22.177.283.093.054.21.08.352.08h.533zM9.5 6.707l.72.7.724-.7L10.209 6l-.709.707zm-6.694 4.888h.03c.433-.01.745-.106.937-.29.024.012.065.035.12.068l.074.039.081.042c.135.073.261.133.379.18.345.146.67.22.977.22a1.216 1.216 0 00.87-.34c.3-.285.449-.714.449-1.286a2.19 2.19 0 00-.335-1.145c-.299-.457-.732-.685-1.3-.685-.502 0-.916.192-1.242.575-.113.132-.21.284-.294.456-.032.062-.06.125-.084.191a.504.504 0 00-.03.078 1.67 1.67 0 00-.022.06c-.103.309-.171.485-.205.53-.072.09-.214.14-.427.147-.123-.005-.209-.03-.256-.076-.057-.054-.085-.153-.085-.297V7l-1.201-.5v3.562c0 .261.048.496.143.703.071.158.168.296.29.413.123.118.266.211.43.28.198.084.42.13.665.136v.001h.036zm2.752-1.014a.778.778 0 00.044-.353.868.868 0 00-.165-.47c-.1-.134-.217-.201-.35-.201-.18 0-.33.103-.447.31-.042.071-.08.158-.114.262a2.434 2.434 0 00-.04.12l-.015.053-.015.046c.142.118.323.216.544.293.18.062.325.092.433.092.044 0 .086-.05.125-.152z" fill-rule="evenodd"></path>
                                                                </symbol>
                                                            </use>
                                                        </svg>{{ number_format($FactorsPriceCount) }}</bdi>
                                                </p>
                                                <small>مجموع خرید</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-container">
                                        <small class="d-block pt-4 border-top fw-normal text-uppercase text-muted my-3">جزئیات</small>
                                        <ul class="list-unstyled">
                                            <li class="mb-3">
                                                <span class="fw-medium me-2">نام مشتری:</span>
                                                <span>{{ $Customer->name }}</span>
                                            </li>
                                            <li class="mb-3">
                                                <span class="fw-medium me-2">کدملی:</span>
                                                <span>{{ $Customer->national_id }}</span>
                                            </li>
                                            <li class="mb-3">
                                                <span class="fw-medium me-2">صنف / کانال:</span>
                                                <span class="badge bg-label-info">{{ $Customer->senf }}</span> / <span class="badge bg-label-info">{{ $Customer->channel }}</span>
                                            </li>
                                            <li class="mb-3">
                                                <span class="fw-medium me-2">شماره تلفن:</span>
                                                <span>
                                                    <bdi><a href="tel:{{ $Customer->phone }}">{{ $Customer->phone }}</a></bdi>
                                                </span>
                                            </li>
                                            <li class="mb-3">
                                                <span class="fw-medium me-2">آدرس:</span>
                                                <span>{{ $Customer->address }}</span>
                                            </li>
                                        </ul>
                                        <div class="d-flex justify-content-center">
                                            <a class="btn btn-primary me-3"  href="{{ route('customers.edit', $Customer->id) }}">ویرایش اطلاعات مشتری</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="customer-avatar-section">
                                        <div class="d-flex align-items-center flex-column">
                                            <div class="customer-info text-center">
                                                <h4 class="mb-1">رفتار مالی مشتری</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-container">
                                        <small class="d-block pt-4 border-top fw-normal text-uppercase text-muted my-3">آمار ورود و خروج مالی</small>
                                        <ul class="list-unstyled">
                                            <?php $Mande = 0; ?>
                                            @foreach ($FactorsAccepted as $fa)
                                                <li class="mb-1 py-1" style="border-bottom: 1px solid #272626;">
                                                    <span class="fw-medium me-2 text-warning">خروج</span>
                                                    <span>{{ number_format(intval(str_replace(',', '', $fa->fullPrice))) }} ریال</span>
                                                </li>
                                                    <?php $Mande -= intval(str_replace(',', '', $fa->fullPrice)); ?>

                                                @if ($fa->payment_type == 1)
                                                    <li class="mb-1 py-1" style="border-bottom: 1px solid #272626;">
                                                        <span class="fw-medium me-2 text-success">ورود نقدی</span>
                                                        <span>{{ number_format(intval(str_replace(',', '', $fa->fullPrice))) }} ریال</span>
                                                    </li>
                                                        <?php $Mande += intval(str_replace(',', '', $fa->fullPrice)); ?>
                                                @endif
                                                @if ($fa->payment_type == 2)
                                                    <li class="mb-1 py-1" style="border-bottom: 1px solid #272626;">
                                                        <span class="fw-medium me-2 text-success">ورود چکی</span>
                                                        <span>{{ number_format(intval(str_replace(',', '', $fa->fullPrice))) }} ریال</span>
                                                    </li>
                                                        <?php $Mande += intval(str_replace(',', '', $fa->fullPrice)); ?>
                                                @endif
                                                @if ($fa->payment_type == null || $fa->payment_type == 3)
                                                    <li class="mb-1 py-1" style="border-bottom: 1px solid #272626;">
                                                        <span class="fw-medium me-2 text-danger">بدهکار</span>
                                                        <span>{{ number_format(intval(str_replace(',', '', $fa->fullPrice))) }} ریال</span>
                                                    </li>
                                                @endif @endforeach
                                        </ul>
                                        <p>وضعیت:

                                            <?php if($Mande > 0) { ?>
                                            <strong class="text-success">
    بستانکار مبلغ: {{ number_format(abs($Mande)) }} ریال
    </strong>
    <?php }else { ?>
    <strong class="text-danger">بدهکار مبلغ: {{ number_format(abs($Mande)) }} ریال</strong>
    <?php } ?>
    </p>
    </div>
    </div>
    </div>
    <!-- /Customer-detail Card -->
    </div>
    <!--/ Customer Sidebar -->
    <!-- Customer Content -->
    <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
        <div class="card card-action mb-4">
            <div class="card-header align-items-center py-4">
                <h5 class="card-action-title mb-0">CRM و فرصت های فروش مشتری</h5>
                <div class="card-action-element d-flex gap-2">
                    <a class="btn btn-label-primary"
                        href="{{ route('crm.followups.index', ['subject_type' => 'customer', 'search' => $Customer->name]) }}">کارتابل
                        پیگیری</a>
                    <a class="btn btn-label-info"
                        href="{{ route('crm.opportunities.index', ['search' => $Customer->name]) }}">Pipeline فروش</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100"><small class="text-muted">پیگیری باز</small>
                            <h5 class="mb-0 mt-1">{{ number_format($CrmStats['open_followups']) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100"><small class="text-muted">پیگیری معوق</small>
                            <h5 class="mb-0 mt-1 text-danger">{{ number_format($CrmStats['overdue_followups']) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100"><small class="text-muted">فرصت باز</small>
                            <h5 class="mb-0 mt-1">{{ number_format($CrmStats['open_opportunities']) }}</h5>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 h-100"><small class="text-muted">ارزش فرصت</small>
                            <h5 class="mb-0 mt-1 text-success">
                                {{ number_format($CrmStats['open_opportunity_amount']) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <h6 class="mb-3">آخرین پیگیری ها</h6>
                        <div class="list-group list-group-flush">
                            @forelse($CrmFollowups as $followup)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between gap-2">
                                        <strong>{{ $followup->title }}</strong>
                                        <span
                                            class="badge bg-label-{{ in_array($followup->status, ['open', 'in_progress'], true) ? 'warning' : 'secondary' }}">{{ $followup->statusText() }}</span>
                                    </div>
                                    <small class="text-muted">{{ $followup->typeText() }} - مسئول:
                                        {{ optional($followup->assignedUser)->name ?: '-' }} - موعد:
                                        {{ optional($followup->due_date_en)->format('Y-m-d') ?: '-' }}</small>
                                </div>
                            @empty
                                <div class="text-muted py-3">برای این مشتری پیگیری CRM ثبت نشده است.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <h6 class="mb-3">فرصت های فروش</h6>
                        <div class="list-group list-group-flush">
                            @forelse($CrmOpportunities as $opportunity)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between gap-2">
                                        <strong>{{ $opportunity->title }}</strong>
                                        <span
                                            class="badge bg-label-{{ $opportunity->status === 'won' ? 'success' : ($opportunity->status === 'lost' ? 'danger' : 'primary') }}">{{ $opportunity->stageText() }}</span>
                                    </div>
                                    <small class="text-muted">{{ $opportunity->code }} -
                                        {{ number_format($opportunity->amount) }} ریال - احتمال
                                        {{ $opportunity->probability_percent }}% - مسئول:
                                        {{ optional($opportunity->assignedUser)->name ?: '-' }}</small>
                                </div>
                            @empty
                                <div class="text-muted py-3">برای این مشتری فرصت فروش ثبت نشده است.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Address accordion -->
        <div class="card card-action mb-4">
            <div class="card-header align-items-center py-4">
                <h5 class="card-action-title mb-0">تاریخچه خریدهای مشتری</h5>
                <div class="card-action-element">
                    @if ($Customer->shop_lat == null && $Customer->shop_lng == null)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalerrorlocation">
                            ثبت فاکتور جدید
                        </button>
                        <div class="modal fade" id="modalerrorlocation" tabindex="-1"
                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <p class="alert alert-danger m-0">برای این مشتری لوکیشن ثبت نشده و قابلیت ثبت سفارش
                                        وجود ندارد.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <form method="GET" action="{{ route('products.index') }}">
                            <input type="hidden" name="Customer" value="{{ $Customer->id }}" />
                            @if ($MyTask)
                                <input type="hidden" name="Task" value="{{ $MyTask->id }}" />
                            @endif
                            <button class="btn btn-label-primary" type="submit">ثبت سفارش جدید</button>
                        </form>
                    @endif

                </div>
            </div>
            <div class="card-body">
                <div class="accordion accordion-flush accordion-arrow-left" id="ecommerceBillingAccordionAddress">
                    @foreach ($Factors as $factor)

                        @php
                            $details_count = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->count();
                            $details = App\Models\PishFactorItems::where('pishfactor_id', $factor->id)->get();
                        @endphp

                        <div class="accordion-item border-bottom">
                            <div class="accordion-header d-flex justify-content-between align-items-center flex-wrap flex-sm-nowrap"
                                id="heading_{{ $factor->id }}">
                                <a aria-controls="heading_{{ $factor->id }}" aria-expanded="false"
                                    class="accordion-button collapsed"
                                    data-bs-target="#factor_id_{{ $factor->id }}" data-bs-toggle="collapse"
                                    role="button">
                                    <span>
                                        <span class="d-flex gap-2 align-items-baseline">
                                            <span class="h6 mb-1">سفارش {{ $factor->id }}</span>
                                            @if ($factor->status == 0)
                                                <span class="badge bg-label-warning">در انتظار تایید</span>
                                            @elseif($factor->status == 1)
                                                <span class="badge bg-label-success">تایید شده</span>
                                            @elseif($factor->status == 3)
                                                <span class="badge bg-label-danger">رد شده</span>
                                            @elseif($factor->status == 4)
                                                <span class="badge bg-label-success">تحویل شده</span>
                                            @elseif($factor->status == 5)
                                                <span class="badge bg-label-warning">مرجوعی</span>
                                            @endif

                                        </span>
                                        <?php $visitor = DB::table('users')->where('id', $factor->visitor_id)->first(); ?>
                                        <span class="mb-0 text-muted">سفارش ثبت شده توسط: <strong>{{ $visitor->name }}
                                            </strong><span style="display: inline-block;direction: ltr"> در تاریخ:
                                                {{ Verta($factor->created_at)->format('Y/m/d') }}</span> </span>
                                    </span>
                                </a>
                                <div class="d-flex gap-3 p-4 p-sm-0 pt-0 ms-1 ms-sm-0">
                                    <a href="{{ route('pishFactorInfo', $factor->id) }}">
                                        <i class="ti ti-pencil text-secondary ti-sm"></i>
                                    </a>

                                </div>
                            </div>
                            <div class="accordion-collapse collapse"
                                data-bs-parent="#ecommerceBillingAccordionAddress"
                                id="factor_id_{{ $factor->id }}">
                                <div class="accordion-body ps-4 ms-2">
                                    <h6 class="mb-1">تعداد اقلام سفارش: <b>{{ $details_count }} قلم</b></h6>
                                    @foreach ($details as $item)
                                        <?php $product = DB::table('products')->where('id', $item->pr_id)->first(); ?>
                                        <p class="mb-1">{{ $product->title }} به مقدار
                                            {{ intval($item->tedad) + intval($item->pack * $product->pack_items) }}
                                            {{ $product->pr_unit }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <form method="POST" action="{{ route('update_customer_loc', $Customer->id) }}">
            @csrf
            <div class="card card-action mb-4">
                <div class="card-header align-items-center py-4">
                    <h5 class="card-action-title mb-0">لوکیشن مشتری</h5>
                    <div class="card-action-element">
                        <button class="btn btn-label-success" type="submit">ثبت موقعیت مشتری</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-12 mb-3">
                            <h5>لوکیشن فروشگاه</h5>
                            <div id="map_get" style="height: 400px"></div>
                            @if ($Customer->shop_lat == null && $Customer->shop_lng == null)
                                <input type="hidden" id="shop_lat" name="shop_lat" value="" />
                                <input type="hidden" id="shop_lng" name="shop_lng" value="" />
                            @endif
                        </div>

                        <div class="col-6 col-12 mb-3">
                            <h5>لوکیشن انبار</h5>
                            <div id="map_get_store" style="height: 400px"></div>
                            @if ($Customer->store_lat == null && $Customer->store_lat == null)
                                <input type="hidden" id="store_lat" name="store_lat" value="" />
                                <input type="hidden" id="store_lng" name="store_lng" value="" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Address accordion -->
        <!-- payment accordion -->

    </div>
    <!--/ Customer Content -->
    </div>

    </div>
    <!-- / Content -->
    <!-- Footer -->
    @include('sections.footer')
    <!-- / Footer -->
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
    <script src="{{ asset('assets/') }}/vendor/libs/moment/moment.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/@form-validation/bootstrap5.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script src="https://static.neshan.org/sdk/mapboxgl/v1.13.2/neshan-sdk/v1.1.5/index.js"></script>
    <!-- Page JS -->
    </body>
    <script>
        $('.customers').addClass('open')
        $('.customers .list').addClass('active open');
    </script>
    <script>
        $(document).ready(function() {
            const neshanMapget = new nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: "map_get",
                zoom: 14,
                pitch: 0,
                center: [{{ $Customer->shop_lat != null ? $Customer->shop_lat : 51.391173 }},
                    {{ $Customer->shop_lng != null ? $Customer->shop_lng : 35.700954 }}
                ],
                minZoom: 2,
                maxZoom: 21,
                trackResize: true,
                mapKey: "web.69873d4db05f495bb49de6c13e8eb294",
                poi: false,
                traffic: false,
                mapTypeControllerOptions: {
                    show: false,
                    position: 'bottom-left'
                }
            });


            var popup = new nmp_mapboxgl.Popup({
                offset: 25
            }).setText('در محل مورد نظر قرار بگیرد.');


            var marker_with_popup = new nmp_mapboxgl.Marker({
                    color: "#FABA0D",
                    draggable: true
                }).setPopup(popup)
                .setLngLat([{{ $Customer->shop_lat != null ? $Customer->shop_lat : 51.391173 }},
                    {{ $Customer->shop_lng != null ? $Customer->shop_lng : 35.700954 }}
                ])
                .addTo(neshanMapget).togglePopup();

            function ShoponDragEnd() {
                const lngLat = marker_with_popup.getLngLat();
                var latinp = document.getElementById('shop_lat').value = lngLat.lat;
                var langinp = document.getElementById('shop_lng').value = lngLat.lng;

            }
            marker_with_popup.on('dragend', ShoponDragEnd);

            // Add geolocate control to the map.
            // Initialize the geolocate control.
            let geolocate = new nmp_mapboxgl.GeolocateControl({
                positionOptions: {
                    enableHighAccuracy: true
                },
                trackUserLocation: true
            });
            // Add the control to the map.
            neshanMapget.addControl(geolocate);
            neshanMapget.on("load", function() {
                geolocate.trigger(); // add this if you want to fire it by code instead of the button
            });
            geolocate.on("geolocate", locateUser);

            function locateUser(e) {
                // alert("A geolocate event has occurred.");
                //alert("lng:" + e.coords.longitude + ", lat:" + e.coords.latitude);



            }

            const neshanMapget2 = new nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: "map_get_store",
                zoom: 14,
                pitch: 0,
                center: [{{ $Customer->store_lat != null ? $Customer->store_lat : 51.391173 }},
                    {{ $Customer->store_lng != null ? $Customer->store_lng : 35.700954 }}
                ],
                minZoom: 2,
                maxZoom: 21,
                trackResize: true,
                mapKey: "web.69873d4db05f495bb49de6c13e8eb294",
                poi: false,
                traffic: false,
                mapTypeControllerOptions: {
                    show: false,
                    position: 'bottom-left'
                }
            });

            var popup2 = new nmp_mapboxgl.Popup({
                offset: 25
            }).setText(
                'روی محل مورد نظر قرار بگیرد'
            );

            var marker_with_popup2 = new nmp_mapboxgl.Marker({
                    color: "#FABA0D",
                    draggable: true
                }).setPopup(popup2)
                .setLngLat([{{ $Customer->store_lat != null ? $Customer->store_lat : 51.391173 }},
                    {{ $Customer->store_lng != null ? $Customer->store_lng : 35.700954 }}
                ])
                .addTo(neshanMapget2).togglePopup();

            function StoreonDragEnd() {
                const lngLat = marker_with_popup2.getLngLat();
                var latinp = document.getElementById('store_lat').value = lngLat.lat;
                var langinp = document.getElementById('store_lng').value = lngLat.lng;

            }

            marker_with_popup2.on('dragend', StoreonDragEnd);


        });
    </script>

</html>

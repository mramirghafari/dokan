<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مشتریان ثبت شده در منظقه {{ $Region->name }} - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>

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
                    <div class="row">
                        <div class="col">
                            <h4 class="py-3 mb-2">
                                <a href="{{ route('regions.index') }}" class="text-muted fw-light">مناطق /</a>
                                <span class="text-muted fw-light">{{ $Region->name }} /</span>
                                لیست مشتریان {{ Request::routeIs('regions.activeCustomersList') ? 'فعال' : '' }}
                            </h4>
                        </div>
                        <div class="col text-end">
                            <a href="{{ route('regions.index') }}" class="btn btn-label-dark waves-effect ms-3 mt-2" type="button">
                                بازگشت
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Sticky Actions -->
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left"> <span>مجموع مشتریان</span>
                                            <div class="d-flex align-items-center my-2">
                                                <h3 class="mb-0 me-2">{{ number_format($CustomersCount) }}</h3>
                                            </div>
                                            <p class="mb-0">مجموع کل مشتریان</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-primary"> <x-ui.icon name="user" class="ti-sm" /> </span> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left"> <span>مشتریان فعال</span>
                                            <div class="d-flex align-items-center my-2">
                                                <h3 class="mb-0 me-2">{{ number_format($customersWithPurchaseCount) }}</h3>
                                            </div>
                                            <p class="mb-0" style="font-size: 13px">جمع سفارشات : {{ number_format($customersPurchaseCount) }} ریال</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-success"> <x-ui.icon name="user-check" class="ti-sm" /> </span> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left"> <span>مشتریان محدود شده</span>
                                            <div class="d-flex align-items-center my-2">
                                                <h3 class="mb-0 me-2">0</h3>
                                            </div>
                                            <p class="mb-0">مشتریان با محدودیت خرید</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-danger"> <x-ui.icon name="user-plus" class="ti-sm" /> </span> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left"> <span>مشتریان بن شده</span>
                                            <div class="d-flex align-items-center my-2">
                                                <h3 class="mb-0 me-2">0</h3>
                                            </div>
                                            <p class="mb-0">مشتریان بدهکار یا بن شده</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-warning"> <x-ui.icon name="user-exclamation" class="ti-sm" /> </span> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>ردیف</th>
                                            <th>کد مشتری</th>
                                            <th>نام مشتری</th>
                                            <th>تابلو</th>
                                            <th>منطقه / مسیر</th>
                                            <th>صنف / کانال</th>
                                            <th>تعداد سفارش</th>
                                            <th>مجموع سفارشات</th>
                                            <th>سرپرست</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($Customers as $customer)
                                            <tr>
                                                <td>{{ $customer->id }}</td>
                                                <td><a href="{{ route('customers.show', $customer->id) }}">{{ $customer->customer_code }}</a></td>
                                                <td><a href="{{ route('customers.show', $customer->id) }}">{{ $customer->name }}</a></td>
                                                <td><a href="{{ route('customers.show', $customer->id) }}"><small>{{ $customer->tablo }}</small></a></td>
                                                <td>
                                                    <small>
                                                        {{ $customer->region->name ?? '' }} / {{ isset($customer->Area) ? $customer->Area->name : '' }}
                                                    </small>
                                                </td>
                                                <td><small>{{ $customer->senf }} / {{ $customer->channel }}</small></td>
                                                <td><a href="{{ route('customers.orders',$customer->id) }}">{{ number_format(intval($customer->activeOrders()->count())) }}</a></td>
                                                <td><a href="{{ route('customers.orders',$customer->id) }}">{{ number_format(intval($customer->activeOrdersSum())) }}</a></td>
                                                <td>
                                                    {{ $customer->leader->name ?? '' }}
                                                </td>
                                                <td>
                                                    @if($customer->status == 1)
                                                        <span class="badge bg-label-success">فعال</span>
                                                    @else
                                                        <span class="badge bg-label-danger">غیرفعال</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('customers.show', $customer->id) }}"
                                                       style="font-size:20px;float: right;margin-left:5px">
                                                        <x-ui.icon name="fa-edit" />
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
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<script>
    // datatable (jquery)
    $(function () {
        var
            dt_without_ajax_table = $('.datatables-direct-basic');

        // DataTable Direct
        // --------------------------------------------------------------------
        if(dt_without_ajax_table.length){
            dt_without_ajax = dt_without_ajax_table.DataTable({
                searching: true,
                lengthChange: false,
                ordering: true,
                order: [[0, 'asc']],
                pageLength: 50,
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
                }
            });


        }


    });

</script>
</body>

</html>

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مشتریان - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/>
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet"/>
    <!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
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
                        <span class="text-muted fw-light">مشتریان /</span>
                        لیست مشتریان
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left"> <span>مجموع مشتریان</span>
                                            <div class="d-flex align-items-center my-2">
                                                <h3 class="mb-0 me-2">{{ number_format($customersTotal) }}</h3>
                                            </div>
                                            <p class="mb-0">مجموع کل مشتریان</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-primary"> <i class="ti ti-user ti-sm"></i> </span> </div>
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
                                            <p class="mb-0">مشتریان با سابقه خرید</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-success"> <i class="ti ti-user-check ti-sm"></i> </span> </div>
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
                                                <h3 class="mb-0 me-2">{{ number_format($restrictedCustomers ?? 0) }}</h3>
                                            </div>
                                            <p class="mb-0">مشتریان با محدودیت خرید</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-danger"> <i class="ti ti-user-plus ti-sm"></i> </span> </div>
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
                                                <h3 class="mb-0 me-2">{{ number_format($bannedCustomers ?? 0) }}</h3>
                                            </div>
                                            <p class="mb-0">مشتریان بدهکار یا بن شده</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-warning"> <i class="ti ti-user-exclamation ti-sm"></i> </span> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(Request::routeIs(['customers.search','customers.index']))
                    <div class="row mb-3">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card p-3">
                                <form method="GET" action="{{ route('customers.index') }}" id="customers-filter-form">
                                    <div class="row">
                                        <div class="form-group col-6 col-md-2 mb-3">
                                            <label for="codename">جستجوی نام / کد مشتری:</label>
                                            <input type="text" class="form-control" id="codename" name="codename" value="{{ $codename ?? '' }}" >
                                        </div>

                                        <div class="form-group col-6 col-md-3 mb-3">
                                            <label for="to_date">نمایش بر اساس منطقه و مسیر:</label>
                                            <select class="select2 form-select" id="area_id" name="area_id">
                                                <option value="0">--انتخاب کنید--</option>
                                                @foreach ($Regions as $region)
                                                    @foreach($region->areas as $area)
                                                    <option value="{{ $area->id }}" {{ $area_id != null && $area_id == $area->id ? 'selected' : '' }}>{{ $region->name }} - {{ $area->name }}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-6 col-md-2">
                                            <label for="delivery_from_date">نمایش بر اساس سرپرست ها:</label>
                                            <select class="select2 form-select" id="leader_id" name="leader_id">
                                                <option value="0">--انتخاب کنید--</option>
                                                @foreach ($Leaders as $leader)
                                                    <option value="{{ $leader->id }}" {{ $leader_id != null && $leader_id == $leader->id ? 'selected' : '' }}>{{ $leader->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-6 col-md-2">
                                            <label for="delivery_to_date">نمایش بر اساس بازاریاب ها:</label>
                                            <select class="select2 form-select" id="visitor_id" name="visitor_id">
                                                <option value="0">--انتخاب کنید--</option>
                                                @foreach ($Visitors as $visitor)
                                                    <option value="{{ $visitor->id }}" {{ $visitor_id != null && $visitor_id == $visitor->id ? 'selected' : '' }}>{{ $visitor->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-6 col-md-2">
                                            <label for="status">وضعیت مشتریان:</label>
                                            <select class="select2 form-select" id="status" name="status">
                                                <option value="2" {{ (int) ($status ?? 2) === 2 ? 'selected' : '' }}>همه مشتریان</option>
                                                <option value="1" {{ (int) ($status ?? 2) === 1 ? 'selected' : '' }}>مشتریان با سابقه خرید</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-6 col-md-1 d-flex align-items-center">
                                            <button type="submit" class="btn btn-info">فیلتر</button>
                                        </div>
                                    </div>
                                </form>

                            </div>

                        </div>

                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-customers-server table" id="customers-table">
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
                                        <tbody></tbody>
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
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<!-- endbuild -->
<script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->

<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<script>
    $(function () {
        const customersTable = $('#customers-table');

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

        const filterPayload = function () {
            return {
                codename: $('#codename').val() || '',
                area_id: $('#area_id').val() || 0,
                leader_id: $('#leader_id').val() || 0,
                visitor_id: $('#visitor_id').val() || 0,
                status: $('#status').val() || 2,
            };
        };

        if (customersTable.length) {
            const dtCustomers = customersTable.DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                lengthChange: true,
                ordering: true,
                order: [[0, 'desc']],
                pageLength: 50,
                lengthMenu: [25, 50, 100],
                ajax: {
                    url: @json(route('customers.datatable')),
                    type: 'GET',
                    data: function (data) {
                        return Object.assign(data, filterPayload());
                    }
                },
                columns: [
                    { data: 0, orderable: true },
                    { data: 1, orderable: true },
                    { data: 2, orderable: true },
                    { data: 3, orderable: true },
                    { data: 4, orderable: false },
                    { data: 5, orderable: false },
                    { data: 6, orderable: true },
                    { data: 7, orderable: true },
                    { data: 8, orderable: false },
                    { data: 9, orderable: true },
                    { data: 10, orderable: false },
                ],
                columnDefs: [
                    { targets: [4, 5, 8, 10], orderable: false },
                    { targets: [9, 10], searchable: false },
                ],
                language: datatableLanguage,
            });

            $('#customers-filter-form').on('submit', function (event) {
                event.preventDefault();
                dtCustomers.ajax.reload();
            });
        }
    });
</script>
</body>

</html>

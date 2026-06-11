<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>لیست محصولات - دکان دارمینو</title>
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
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        div#DataTables_Table_0_length {
            text-align: left;
            padding-left: 15px;
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
                        <h4 class="py-3 mb-2">
                            <span class="text-muted fw-light">محصولات /</span>
                            لیست محصولات
                            <small class="text-muted">({{ number_format($productsTotal) }} مورد)</small>
                        </h4>
                        <!-- Sticky Actions -->

                        <div class="row my-3">
                            <div class="card px-0 mb-5">
                                <div class="card-body pb-0">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                        <select class="form-select" id="store_filter" style="width:auto; min-width:180px;">
                                            <option value="">همه انبارها</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}"
                                                    @selected(($filterValues['store_id'] ?? null) == $store->id)>{{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select" id="status_filter" style="width:auto; min-width:180px;">
                                            <option value="active" @selected(($filterValues['status_filter'] ?? 'active') === 'active')>فقط فعال</option>
                                            <option value="inactive" @selected(($filterValues['status_filter'] ?? 'active') === 'inactive')>فقط غیرفعال</option>
                                            <option value="all" @selected(($filterValues['status_filter'] ?? 'active') === 'all')>همه وضعیت‌ها</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-datatable table-responsive tablelist py-0">
                                    <table class="datatables-products-server table" id="products-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>کد کالا</th>
                                                <th>نام کالا</th>
                                                <th>انبار</th>
                                                <th>واحد پخش</th>
                                                <th>موجودی کالا</th>
                                                <th>واحد اصلی</th>
                                                <th>واحد فرعی</th>
                                                <th>قیمت</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
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
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <script>
        $('.products').addClass('open')
        $('.products .productslist').addClass('active open')

        $(function () {
            const productsTable = $('#products-table');

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
                    status_filter: $('#status_filter').val() || 'active',
                    store_id: $('#store_filter').val() || '',
                };
            };

            const syncUrlFilters = function () {
                const params = new URLSearchParams(window.location.search);
                const statusFilter = $('#status_filter').val() || 'active';
                const storeId = $('#store_filter').val() || '';

                params.set('status_filter', statusFilter);
                if (storeId) {
                    params.set('store_id', storeId);
                } else {
                    params.delete('store_id');
                }

                const query = params.toString();
                const nextUrl = query ? `${window.location.pathname}?${query}` : window.location.pathname;
                window.history.replaceState({}, '', nextUrl);
            };

            if (productsTable.length) {
                const dtProducts = productsTable.DataTable({
                    processing: true,
                    serverSide: true,
                    searching: true,
                    lengthChange: true,
                    ordering: true,
                    order: [],
                    pageLength: 50,
                    lengthMenu: [25, 50, 100],
                    autoWidth: false,
                    ajax: {
                        url: @json(route('products.datatable')),
                        type: 'GET',
                        data: function (data) {
                            return Object.assign(data, filterPayload());
                        }
                    },
                    columns: [
                        { data: 0, orderable: false },
                        { data: 1 },
                        { data: 2 },
                        { data: 3, orderable: false },
                        { data: 4, orderable: false },
                        { data: 5, orderable: false },
                        { data: 6 },
                        { data: 7 },
                        { data: 8 },
                        { data: 9, orderable: false, searchable: false },
                    ],
                    columnDefs: [
                        { width: '30px', targets: 0 },
                        { width: '90px', targets: 1 },
                        { width: '250px', targets: 2 },
                        { width: '160px', targets: 3 },
                        { width: '160px', targets: 4 },
                        { width: '90px', targets: 5 },
                        { width: '60px', targets: 6 },
                        { width: '60px', targets: 7 },
                        { width: '120px', targets: 8 },
                        { width: '50px', targets: 9 },
                    ],
                    language: datatableLanguage,
                });

                $('#store_filter, #status_filter').on('change', function () {
                    syncUrlFilters();
                    dtProducts.ajax.reload();
                });
            }
        });
    </script>
</body>

</html>

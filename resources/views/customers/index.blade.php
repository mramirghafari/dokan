<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مشتریان - دکان دارمینو</title>
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
                                            <p class="mb-0">مشتریان با سابقه خرید</p>
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
                                                <h3 class="mb-0 me-2">{{ number_format($restrictedCustomers ?? 0) }}</h3>
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
                                                <h3 class="mb-0 me-2">{{ number_format($bannedCustomers ?? 0) }}</h3>
                                            </div>
                                            <p class="mb-0">مشتریان بدهکار یا بن شده</p>
                                        </div>
                                        <div class="avatar"> <span class="avatar-initial rounded bg-label-warning"> <x-ui.icon name="user-exclamation" class="ti-sm" /> </span> </div>
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
                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h5 class="card-title mb-0">لیست مشتریان</h5>
                                        <small class="text-muted">ستون‌های نمایشی برای همین پنل قابل تنظیم است.</small>
                                    </div>
                                    <button type="button"
                                        class="btn btn-label-secondary btn-sm"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#customer-columns-collapse"
                                        aria-expanded="false"
                                        aria-controls="customer-columns-collapse"
                                        id="customer-columns-toggle">
                                        <x-ui.icon name="columns-3" class="me-1" />ستون‌های جدول
                                    </button>
                                </div>

                                <div class="collapse border-bottom" id="customer-columns-collapse">
                                    <div class="card-body py-3">
                                        <div class="row g-3" id="customer-columns-form">
                                            @foreach ($customerListColumnCatalog as $columnKey => $column)
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input customer-column-toggle"
                                                            type="checkbox"
                                                            value="{{ $columnKey }}"
                                                            id="customer-column-{{ $columnKey }}"
                                                            @checked(in_array($columnKey, $customerListVisibleColumns, true))>
                                                        <label class="form-check-label" for="customer-column-{{ $columnKey }}">
                                                            <span class="fw-medium">{{ $column['label'] }}</span>
                                                            <span class="d-block small text-muted">{{ $column['description'] }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                                            <small class="text-muted">
                                                ستون‌های «ردیف»، «نام مشتری»، «وضعیت» و «عملیات» همیشه نمایش داده می‌شوند.
                                                کنار نام: تیک سبز = فعال، قلب طلایی = وفادار (بیش از یک خرید).
                                                @if ($customerListIsSubscriptionPanel)
                                                    ستون «مانده اشتراک» فقط برای پنل‌های دوره‌ای/اشتراکی فعال است.
                                                @endif
                                            </small>
                                            <button type="button" class="btn btn-primary btn-sm" id="customer-columns-save">
                                                <x-ui.icon name="device-floppy" class="me-1" />ذخیره برای این پنل
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-datatable table-responsive py-0 customers-table-shell">
                                    <div id="customers-table-loading" class="dokan-table-loading is-active" aria-live="polite" aria-busy="true">
                                        <div class="dokan-table-loading__panel">
                                            <div class="dokan-table-loading__spinner" aria-hidden="true"></div>
                                            <p class="dokan-table-loading__title">در حال بارگذاری لیست مشتریان...</p>
                                            <p class="dokan-table-loading__subtitle">لطفاً چند لحظه صبر کنید</p>
                                        </div>
                                        <div class="dokan-table-loading__skeleton" aria-hidden="true">
                                            @for ($i = 0; $i < 6; $i++)
                                                <div class="dokan-table-loading__skeleton-row"></div>
                                            @endfor
                                        </div>
                                    </div>
                                    <table class="datatables-customers-server table" id="customers-table">
                                        <thead>
                                        <tr>
                                            @foreach ($customerListHeaders as $header)
                                                <th>{{ $header['label'] }}</th>
                                            @endforeach
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

@include('partials.panel-toasts')

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
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<script src="{{ asset('assets/') }}/js/dokan-datatables.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->

<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<script>
    $(function () {
        const customersTable = $('#customers-table');
        const $tableLoading = $('#customers-table-loading');
        const hiddenColumnIndexes = @json($customerListHiddenIndexes);
        const fixedColumnIndexes = @json($customerListFixedIndexes);
        const nonSortableIndexes = @json($customerListNonSortableIndexes);
        const columnCount = @json($customerListColumnCount);
        const columnsSaveUrl = @json(route('customers.list-columns.save'));
        const csrfToken = @json(csrf_token());

        const datatableLanguage = {
            search: 'جستجو: ',
            searchPlaceholder: 'جستجو کنید...',
            info: 'نمایش _START_ تا _END_ از _TOTAL_ مورد',
            infoEmpty: 'موردی وجود ندارد.',
            infoFiltered: '(فیلتر شده از _MAX_ مورد)',
            lengthMenu: 'نمایش _MENU_ مورد در صفحه',
            zeroRecords: 'متاسفانه موردی پیدا نشد',
            processing: '',
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

        function setTableLoading(isLoading) {
            $tableLoading.toggleClass('is-active', !!isLoading);
        }

        function applyColumnVisibility(dt) {
            hiddenColumnIndexes.forEach(function (index) {
                dt.column(index).visible(false, false);
            });
            dt.columns.adjust().draw(false);
        }

        function collectSelectedColumns() {
            return $('.customer-column-toggle:checked').map(function () {
                return $(this).val();
            }).get();
        }

        if (customersTable.length) {
            const dtColumns = Array.from({ length: columnCount }, function (_, index) {
                return {
                    data: index,
                    orderable: nonSortableIndexes.indexOf(index) === -1,
                    searchable: index !== columnCount - 1,
                };
            });

            const dtCustomers = customersTable.DataTable({
                processing: false,
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
                columns: dtColumns,
                columnDefs: [
                    { targets: nonSortableIndexes, orderable: false },
                    { targets: [columnCount - 1], searchable: false },
                ],
                language: datatableLanguage,
                initComplete: function () {
                    applyColumnVisibility(dtCustomers);
                    setTableLoading(false);
                },
            });

            customersTable.on('preXhr.dt', function () {
                setTableLoading(true);
            });

            customersTable.on('draw.dt', function () {
                setTableLoading(false);
            });

            $('#customers-filter-form').on('submit', function (event) {
                event.preventDefault();
                dtCustomers.ajax.reload();
            });

            $('#customer-columns-save').on('click', function () {
                const $button = $(this);
                const selected = collectSelectedColumns();

                $button.prop('disabled', true);

                $.ajax({
                    url: columnsSaveUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        columns: selected,
                    },
                }).done(function (response) {
                    const hidden = response.hidden_indexes || [];

                    dtCustomers.columns().every(function (index) {
                        if (fixedColumnIndexes.indexOf(index) !== -1) {
                            this.visible(true, false);
                            return;
                        }

                        this.visible(hidden.indexOf(index) === -1, false);
                    });

                    dtCustomers.columns.adjust().draw(false);

                    if (window.DokanToast) {
                        DokanToast.success(response.message || 'ستون‌ها ذخیره شد.');
                    }
                }).fail(function (xhr) {
                    const message = xhr.responseJSON?.message || 'ذخیره ستون‌ها ناموفق بود.';
                    if (window.DokanToast) {
                        DokanToast.error(message);
                    }
                }).always(function () {
                    $button.prop('disabled', false);
                });
            });
        }
    });
</script>
</body>

</html>

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
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .products-table-shell .dokan-dt-top {
            direction: ltr;
            align-items: center !important;
            background-color: #D0E4D3;
            margin: 0;
            padding: 12px 10px;
            border-top-left-radius: 8px;
            border-top-right-radius: 25px;
            gap: 0.75rem;
        }

        .products-table-shell .dokan-dt-length .dataTables_length {
            margin: 0;
            padding: 0;
            text-align: left;
        }

        .products-table-shell .dokan-dt-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }

        .products-table-shell .dokan-dt-filters .form-select {
            width: auto;
            min-width: 170px;
        }

        .products-table-shell .dokan-dt-search .dataTables_filter {
            float: none;
            width: auto;
            margin: 0;
            padding: 0;
            background: transparent;
            text-align: right;
        }

        .products-table-shell .dokan-dt-search .dataTables_filter .form-control {
            width: 250px !important;
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
                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom-0 pb-0">
                                    <div>
                                        <h5 class="card-title mb-0">لیست محصولات</h5>
                                        <small class="text-muted">ستون‌های نمایشی برای همین پنل قابل تنظیم است.</small>
                                    </div>
                                    <button type="button"
                                        class="btn btn-label-secondary btn-sm"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#product-columns-collapse"
                                        aria-expanded="false"
                                        aria-controls="product-columns-collapse"
                                        id="product-columns-toggle">
                                        <x-ui.icon name="columns-3" class="me-1" />ستون‌های جدول
                                    </button>
                                </div>

                                <div class="collapse border-bottom" id="product-columns-collapse">
                                    <div class="card-body py-3">
                                        <div class="row g-3" id="product-columns-form">
                                            @foreach ($productListColumnCatalog as $columnKey => $column)
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input product-column-toggle"
                                                            type="checkbox"
                                                            value="{{ $columnKey }}"
                                                            id="product-column-{{ $columnKey }}"
                                                            @checked(in_array($columnKey, $productListVisibleColumns, true))>
                                                        <label class="form-check-label" for="product-column-{{ $columnKey }}">
                                                            <span class="fw-medium">{{ $column['label'] }}</span>
                                                            <span class="d-block small text-muted">{{ $column['description'] }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                                            <small class="text-muted">
                                                ستون‌های «#»، «نام کالا»، «وضعیت» و «عملیات» همیشه نمایش داده می‌شوند.
                                                @unless ($warehouseModuleEnabled)
                                                    با غیرفعال بودن «مدیریت انبار و موجودی»، ستون و فیلتر انبار نمایش داده نمی‌شود.
                                                @endunless
                                            </small>
                                            <button type="button" class="btn btn-primary btn-sm" id="product-columns-save">
                                                <x-ui.icon name="device-floppy" class="me-1" />ذخیره برای این پنل
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="products-dt-filters" class="d-none">
                                    @if ($warehouseModuleEnabled)
                                        <select class="form-select" id="store_filter">
                                            <option value="">همه انبارها</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}"
                                                    @selected(($filterValues['store_id'] ?? null) == $store->id)>{{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <select class="form-select" id="status_filter">
                                        <option value="active" @selected(($filterValues['status_filter'] ?? 'active') === 'active')>فقط فعال</option>
                                        <option value="inactive" @selected(($filterValues['status_filter'] ?? 'active') === 'inactive')>فقط غیرفعال</option>
                                        <option value="all" @selected(($filterValues['status_filter'] ?? 'active') === 'all')>همه وضعیت‌ها</option>
                                    </select>
                                    <select class="form-select" id="sales_filter">
                                        <option value="all" @selected(($filterValues['sales_filter'] ?? 'all') === 'all')>همه محصولات</option>
                                        <option value="sold" @selected(($filterValues['sales_filter'] ?? 'all') === 'sold')>فروخته‌شده</option>
                                        <option value="unsold" @selected(($filterValues['sales_filter'] ?? 'all') === 'unsold')>فروخته‌نشده</option>
                                    </select>
                                </div>

                                <div class="card-datatable table-responsive py-0 customers-table-shell products-table-shell">
                                    <div id="products-table-loading" class="dokan-table-loading is-active" aria-live="polite" aria-busy="true">
                                        <div class="dokan-table-loading__panel">
                                            <div class="dokan-table-loading__spinner" aria-hidden="true"></div>
                                            <p class="dokan-table-loading__title">در حال بارگذاری لیست محصولات...</p>
                                            <p class="dokan-table-loading__subtitle">لطفاً چند لحظه صبر کنید</p>
                                        </div>
                                        <div class="dokan-table-loading__skeleton" aria-hidden="true">
                                            @for ($i = 0; $i < 6; $i++)
                                                <div class="dokan-table-loading__skeleton-row"></div>
                                            @endfor
                                        </div>
                                    </div>
                                    <table class="datatables-products-server table" id="products-table">
                                        <thead>
                                            <tr>
                                                @foreach ($productListHeaders as $header)
                                                    <th>{{ $header['label'] }}</th>
                                                @endforeach
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
    <script src="{{ asset('assets/') }}/js/dokan-datatables.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <script>
        $('.products').addClass('open')
        $('.products .productslist').addClass('active open')

        $(function () {
            const productsTable = $('#products-table');
            const $tableLoading = $('#products-table-loading');
            const hiddenColumnIndexes = @json($productListHiddenIndexes);
            const nonSortableIndexes = @json($productListNonSortableIndexes);
            const columnCount = @json($productListColumnCount);
            const columnsSaveUrl = @json(route('products.list-columns.save'));
            const csrfToken = @json(csrf_token());
            const warehouseModuleEnabled = @json($warehouseModuleEnabled);

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
                return $('.product-column-toggle:checked').map(function () {
                    return $(this).val();
                }).get();
            }

            const filterPayload = function () {
                const payload = {
                    status_filter: $('#status_filter').val() || 'active',
                    sales_filter: $('#sales_filter').val() || 'all',
                };

                if (warehouseModuleEnabled) {
                    payload.store_id = $('#store_filter').val() || '';
                }

                return payload;
            };

            const syncUrlFilters = function () {
                const params = new URLSearchParams(window.location.search);
                const statusFilter = $('#status_filter').val() || 'active';
                const salesFilter = $('#sales_filter').val() || 'all';

                params.set('status_filter', statusFilter);
                params.set('sales_filter', salesFilter);

                if (warehouseModuleEnabled) {
                    const storeId = $('#store_filter').val() || '';
                    if (storeId) {
                        params.set('store_id', storeId);
                    } else {
                        params.delete('store_id');
                    }
                } else {
                    params.delete('store_id');
                }

                const query = params.toString();
                const nextUrl = query ? `${window.location.pathname}?${query}` : window.location.pathname;
                window.history.replaceState({}, '', nextUrl);
            };

            const mountProductsToolbarFilters = function ($wrapper) {
                const $slot = $wrapper.find('.dokan-dt-filters');
                const $filters = $('#products-dt-filters');

                if ($slot.length && $filters.length) {
                    $filters.children().appendTo($slot);
                    $filters.remove();
                }
            };

            if (productsTable.length) {
                const dtColumns = Array.from({ length: columnCount }, function (_, index) {
                    return {
                        data: index,
                        orderable: nonSortableIndexes.indexOf(index) === -1,
                        searchable: index !== columnCount - 1,
                    };
                });

                const dtProducts = productsTable.DataTable({
                    processing: false,
                    serverSide: true,
                    searching: true,
                    lengthChange: true,
                    ordering: true,
                    order: [[0, 'desc']],
                    pageLength: 50,
                    lengthMenu: [25, 50, 100],
                    autoWidth: false,
                    dom: '<"dokan-dt-top d-flex flex-wrap"<"dokan-dt-length"l><"dokan-dt-filters"><"dokan-dt-search ms-auto"f>>rt<"row mx-0"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    ajax: {
                        url: @json(route('products.datatable')),
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
                        mountProductsToolbarFilters(productsTable.closest('.dataTables_wrapper'));
                        applyColumnVisibility(dtProducts);
                        setTableLoading(false);
                    },
                });

                productsTable.on('preXhr.dt', function () {
                    setTableLoading(true);
                });

                productsTable.on('draw.dt', function () {
                    setTableLoading(false);
                });

                const filterSelectors = warehouseModuleEnabled
                    ? '#store_filter, #status_filter, #sales_filter'
                    : '#status_filter, #sales_filter';
                $(filterSelectors).on('change', function () {
                    syncUrlFilters();
                    dtProducts.ajax.reload();
                });

                $('#product-columns-save').on('click', function () {
                    const $button = $(this);
                    $button.prop('disabled', true);

                    $.ajax({
                        url: columnsSaveUrl,
                        method: 'POST',
                        data: {
                            _token: csrfToken,
                            columns: collectSelectedColumns(),
                        },
                    }).done(function () {
                        window.location.reload();
                    }).fail(function () {
                        alert('ذخیره ستون‌ها انجام نشد. دوباره تلاش کنید.');
                    }).always(function () {
                        $button.prop('disabled', false);
                    });
                });
            }
        });
    </script>
</body>

</html>

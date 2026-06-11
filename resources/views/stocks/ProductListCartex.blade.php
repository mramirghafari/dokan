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
</head>

<body>
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
                            <span class="text-muted fw-light">تولید و انبار /</span>
                            لیست محصولات
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr>
                                                    <th width="40">ردیف</th>
                                                    <th>کد محصول</th>
                                                    <th>عنوان محصول</th>
                                                    <th>اول دوره</th>
                                                    <th>اول دوره واحد فرعی</th>
                                                    <th>ورودی</th>
                                                    <th>ورودی واحد فرعی</th>
                                                    <th>خروجی</th>
                                                    <th>خروجی واحد فرعی</th>
                                                    <th>موجودی</th>
                                                    <th>موجودی واحد فرعی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $x = 1;
                                                @endphp
                                                @foreach ($Products as $product)
                                                    @php
                                                        $balance = $productBalances[$product->id] ?? null;
                                                        $quantity = $balance ? (float) $balance->quantity : 0;
                                                        $quantitySubUnit = $balance
                                                            ? (float) $balance->quantity_sub_unit
                                                            : 0;
                                                        $orderLimit = (float) ($product->orderLimit ?: 0);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $x }}</td>
                                                        <td><a
                                                                href="{{ route('stocks.PrCartex', $product->id) }}">{{ $product->sku }}</a>
                                                        </td>
                                                        <td><a href="{{ route('stocks.PrCartex', $product->id) }}">{{ $product->title }}
                                                                {{ $product->display_name }}</a> </td>
                                                        <td><a href="{{ route('stocks.PrCartex', $product->id) }}">-</a>
                                                        </td>
                                                        <td><a href="{{ route('stocks.PrCartex', $product->id) }}">-</a>
                                                        </td>
                                                        <td><a href="{{ route('stocks.PrCartex', $product->id) }}">-</a>
                                                        </td>
                                                        <td><a href="{{ route('stocks.PrCartex', $product->id) }}">-</a>
                                                        </td>
                                                        <td><a href="{{ route('stocks.PrCartex', $product->id) }}">-</a>
                                                        </td>
                                                        <td><a href="{{ route('stocks.PrCartex', $product->id) }}">-</a>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('stocks.PrCartex', $product->id) }}">
                                                                {{ number_format($quantity, 3) }}
                                                                @if ($quantity < 0)
                                                                    <span class="badge bg-label-danger ms-1">منفی</span>
                                                                @elseif ($orderLimit > 0 && $quantity <= $orderLimit)
                                                                    <span
                                                                        class="badge bg-label-warning ms-1">کمبود</span>
                                                                @endif
                                                            </a>
                                                        </td>
                                                        <td><a
                                                                href="{{ route('stocks.PrCartex', $product->id) }}">{{ number_format($quantitySubUnit, 3) }}</a>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $x++;
                                                    @endphp
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
        $('.stocks').addClass('open')
        $('.stocks .vorodi').addClass('active open')
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
                    pageLength: 100,
                });

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }


        });

        $(document).ready(function() {
            $('#pr_id').on('change', function() {
                var dataUnit = $(this).find('option:selected').attr('data-unit');
                $('.unitplace').html(dataUnit);
                var dataSubUnit = $(this).find('option:selected').attr('data-subunit');
                $('.subunitplace').html(dataSubUnit);
            });
        });
    </script>
</body>

</html>

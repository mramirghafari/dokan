<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پیش فاکتورهای در انتظار - دکان دارمینو</title>
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
                            <span class="text-muted fw-light">مسیرها /</span>
                            پیش فاکتورهای در انتظار
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card-datatable table-responsive pt-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                            <tr class="text-center">
                                                <th width="40">ردیف</th>
                                                <th>نام خریدار</th>
                                                <th>تعداد اقلام</th>
                                                <th>تاریخ ثبت</th>
                                                <th>تاریخ تحویل</th>
                                                <th>مبلغ کل</th>
                                                <th>وضعیت</th>
                                                @if (auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                    <th>بازاریاب</th>
                                                @endif
                                                <th>سرپرست</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @php($x = 1)
                                            @foreach ($Factors as $invoice)
                                                <tr>
                                                    <th>{{ $x }}</th>
                                                    <td>
                                                        <a href="{{ route('pishFactorInfo', $invoice->id) }}">
                                                            {{ $invoice->is_agency_order ? ($invoice->agencyUser ? $invoice->agencyUser->name : ($invoice->visitor ? $invoice->visitor->name : 'وارد نشده')) : (isset($invoice->customer->name) ? $invoice->customer->name : 'وارد نشده') }}
                                                        </a>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php $details = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->count(); ?>
                                                        {{ $details }} قلم
                                                    </td>
                                                    <td>{{ Verta($invoice->created_at)->format('Y-m-d-H:i:s') }}</td>
                                                    <td>1404/03/25</td>
                                                    <td>
                                                        <?php
                                                        $PItems = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->get();
                                                        $PItems_id = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->pluck('id')->toArray();
                                                        $FactorPrice = 0;
                                                        foreach ($PItems as $pit) {
                                                            $PR = App\Models\Product::find($pit->pr_id);
                                                            if ($PR) {
                                                                $items = intval($PR->pack_items) * intval($pit->pack) + intval($pit->tedad);
                                                                $fee_price = intval($items) * intval($pit->price);
                                                                $discount_price = (intval($items) * intval($pit->price) * intval($pit->discount)) / 100;
                                                                $pat = intval($fee_price) - intval($discount_price);
                                                                $tax_price = (intval($pat) * intval($PR->tax)) / 100;
                                                                $fullp = intval($pat) + intval($tax_price);
                                                                $FactorPrice += $fullp;
                                                            }
                                                        }
                                                        ?>

                                                        {{ number_format($FactorPrice) }}
                                                        ریال
                                                    </td>
                                                    <td>
                                                        @if ($invoice->status == 0)
                                                            <span class="badge bg-label-warning me-1">منتظر تایید</span>
                                                            <br />
                                                        @elseif($invoice->status == 3)
                                                            <span class="badge bg-label-danger me-1">رد شده</span>
                                                            <br />
                                                        @elseif($invoice->status == 5)
                                                            <span class="badge bg-label-warning me-1">مرجوعی</span>
                                                            <br />
                                                        @endif


                                                    </td>
                                                    @if (auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                        <?php $Visitor = App\Models\User::find($invoice->visitor_id); ?>
                                                        <td>{{ $Visitor->name }}</td>
                                                    @endif
                                                    <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                                    <td>{{ $leader->name }}</td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown" type="button">
                                                                <x-ui.icon name="dots-vertical" />
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item"
                                                                    href="{{ route('pishFactorInfo', $invoice->id) }}">
                                                                    <x-ui.icon name="pencil" class="me-1" />
                                                                    مشاهده جزئیات / ویرایش
                                                                </a>
                                                                @if (auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                                    <form
                                                                        action="{{ route('pishfactor.destroy', $invoice->id) }}"
                                                                        method="POST"
                                                                        onsubmit="return confirm('آیا از حذف فاکتور مورد نظر اطمینان دارید؟');">
                                                                        @csrf
                                                                        <button type="submit" class="dropdown-item">
                                                                            حذف
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @php($x++)
                                            @endforeach
                                        </tbody>
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
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <script>
        $('.orders').addClass('open')
        $('.orders .waiting').addClass('active open')
        // datatable (jquery)
        $(function() {
            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: true,
                    lengthChange: false,
                    ordering: true,
                    pageLength: 10,
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

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }


        });
    </script>
</body>

</html>

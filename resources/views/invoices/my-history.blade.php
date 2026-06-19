
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>تاریخچه سفارشات ثبتی من - دکان دارمینو</title>
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
                        تاریخچه سفارشات ثبتی من
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card-datatable table-responsive pt-0">
                                <style>
                                    .table tr th, .table tr td {padding: 7px 5px}
                                    .dataTables_filter {width: 365px}
                                </style>
                                <table class="datatables-direct-basic table">
                                    <thead>
                                    <tr class="text-center">
                                        <th width="30">ردیف</th>
                                        <th>تاریخ ثبت</th>
                                        <th>شماره فاکتور</th>
                                        <th>نام خریدار</th>
                                        <th>مجموع مقدار</th>
                                        <th>تاریخ تحویل</th>
                                        <th>مبلغ کل</th>
                                        <th>بازاریاب</th>
                                        <th>سرپرست</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody style="background-color: #fff">
                                    @php($x = 1)
                                    @foreach($PishFactors as $invoice)
                                            <?php $organ = App\Models\Organization::find($invoice->organization_id); ?>
                                        <tr>
                                            <th class="text-center"><small>{{ $x }}</small></th>
                                            <td class="text-center"><small data-bs-toggle="tooltip" data-bs-placement="top"
                                                       data-bs-custom-class="custom-tooltip"
                                                       data-bs-title="ساعت {{ Verta($invoice->created_at)->format('H:i') }}">{{ Verta($invoice->created_at)->format('Y-m-d') }}</small></td>
                                            <td class="text-center">{{ $invoice->invoiceID }} </td>
                                            <td class="text-center"><a href="{{ route('pishFactorInfo', $invoice->id) }}">{{ isset($invoice->customer->name) ? $invoice->customer->name : 'وارد نشده' }}</a> </td>
                                            <td class="text-center">
                                                    <?php $details = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->count(); ?>
                                                   <small data-bs-toggle="tooltip" data-bs-placement="top"
                                                          data-bs-custom-class="custom-tooltip"
                                                          data-bs-title="{{ $details }} قلم">
                                                           <?php
                                                           $Packs = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('pack');
                                                           $tedad = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('tedad');
                                                           ?>
                                                       @if($Packs > 0) {{ $Packs }} {{ $organ->sub_unit }} @endif
                                                       @if($tedad > 0) {{ $tedad }} {{ $organ->unit_order }} @endif

                                                   </small>
                                            </td>
                                            <td class="text-center"><small>{{ $invoice->recive_date ? $invoice->recive_date : 'وارد نشده' }}</small></td>
                                            <td class="text-center">

                                                <small>
                                                    {{ number_format(intval(str_replace(',','',$invoice->fullPrice))) }}
                                                    @if($organ->currency_type == 1) تومان @elseif($organ->currency_type == 2) ریال @endif
                                                </small>
                                            </td>
                                                <?php $Visitor = App\Models\User::find($invoice->visitor_id); ?>
                                            <td class="text-center"><small>{{ $Visitor->name }}</small></td>
                                                <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                            <td class="text-center"><small>{{ $leader->name }}</small></td>
                                            <td class="text-center">
                                                @if($invoice->status == 0)
                                                    <span class="badge bg-label-warning me-1">منتظر تایید</span> <br />
                                                @elseif($invoice->status == 1)
                                                    <span class="badge bg-label-success me-1">تایید شده</span> <br />
                                                @elseif($invoice->status == 3)
                                                    <span class="badge bg-label-danger me-1">رد شده</span> <br />
                                                @elseif($invoice->status == 5)
                                                    <span class="badge bg-label-warning me-1">مرجوعی</span> <br />
                                                @endif


                                            </td>
                                            <td class="text-center">
                                                <a class="d-inline-block me-3" href="{{ route('pishFactorInfo', $invoice->id) }}"><svg width="24" height="21" viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M1.02324 5.6932C0.981836 5.56875 0.981836 5.43425 1.02324 5.3098C1.85544 2.806 4.21764 1 7.00164 1C9.78444 1 12.1454 2.8042 12.9794 5.3068C13.0214 5.431 13.0214 5.5654 12.9794 5.6902C12.1478 8.194 9.78564 10 7.00164 10C4.21884 10 1.85724 8.1958 1.02324 5.6932Z" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M8.80312 5.49995C8.80312 5.97734 8.61348 6.43518 8.27592 6.77274C7.93835 7.11031 7.48051 7.29995 7.00312 7.29995C6.52574 7.29995 6.0679 7.11031 5.73033 6.77274C5.39277 6.43518 5.20312 5.97734 5.20312 5.49995C5.20312 5.02256 5.39277 4.56472 5.73033 4.22716C6.0679 3.88959 6.52574 3.69995 7.00312 3.69995C7.48051 3.69995 7.93835 3.88959 8.27592 4.22716C8.61348 4.56472 8.80312 5.02256 8.80312 5.49995Z" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                                @if(auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                    <form class="d-inline" action="{{ route('pishfactor.destroy', $invoice->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('آیا از حذف فاکتور مورد نظر اطمینان دارید؟');">
                                                        @csrf
                                                        <button type="submit" class="d-inline" style="border: 0 none; background: transparent">
                                                            <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8.31739 5.00023L8.08672 11.0001M4.89472 11.0001L4.66406 5.00023M11.3094 2.86028C11.5374 2.89495 11.7641 2.93162 11.9907 2.97095M11.3094 2.86028L10.5974 12.1154C10.5683 12.4922 10.3981 12.8441 10.1207 13.1008C9.84337 13.3576 9.47933 13.5001 9.10139 13.5H3.88006C3.50212 13.5001 3.13807 13.3576 2.86071 13.1008C2.58335 12.8441 2.41312 12.4922 2.38406 12.1154L1.67206 2.86028M11.3094 2.86028C10.54 2.74397 9.76657 2.65569 8.99072 2.59562M1.67206 2.86028C1.44406 2.89428 1.21739 2.93095 0.990723 2.97028M1.67206 2.86028C2.44148 2.74397 3.21488 2.65569 3.99072 2.59562M8.99072 2.59562V1.98497C8.99072 1.19833 8.38406 0.542346 7.59739 0.51768C6.8598 0.494107 6.12165 0.494107 5.38406 0.51768C4.59739 0.542346 3.99072 1.199 3.99072 1.98497V2.59562M8.99072 2.59562C7.32654 2.46701 5.65491 2.46701 3.99072 2.59562" stroke="#C1292E" stroke-linecap="round" stroke-linejoin="round"/>
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
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script src="{{ asset('assets/') }}/js/datatable/dataTables.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/dataTables.buttons.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/buttons.dataTables.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/jszip.min.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/buttons.html5.min.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        jalaliDatepicker.startWatch();
    });
</script>
<script>
    // datatable (jquery)

   /* $(document).ready(function () {
        // Setup - add a text input to each footer cell
        $('.datatables-direct-basic thead tr.dovom th').each(function (i) {
            var title = $('.datatables-direct-basic thead th')
                .eq($(this).index())
                .text();
            $(this).html(
                '<input type="text" placeholder="' + title + '" data-index="' + i + '" />'
            );
        });

    }); */

    $('.orders').addClass('open')
    $('.orders .myhistory').addClass('active open');

    $(function () {
        var
            dt_without_ajax_table = $('.datatables-direct-basic');

        if(dt_without_ajax_table.length){
            dt_without_ajax = dt_without_ajax_table.DataTable({
                searching: true,
                scrollX: true,
                lengthChange: false,
                pageLength: 100,
                lengthMenu: [10, 25, 50, 75, 100, 150, 200],
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
            });

        }

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




    });

</script>
</body>

</html>

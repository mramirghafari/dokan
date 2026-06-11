<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>گزارش گیری فاکتور ها - دکان دارمینو</title>
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
                        <span class="text-muted fw-light">فاکتورها /</span>
                       گزارش گیری فاکتورهای ثبت شده
                    </h4>
                    <!-- Sticky Actions -->
                    <form action="{{ route('invoices.reporter') }}" method="GET">
                        @csrf
                        <div class="col-12 card mb-4">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="form-group col-6 col-md-2 mb-3">
                                        <label for="from_date">نمایش از تاریخ:</label>
                                        <input type="text" class="form-control" name="from_date"
                                               id="from_date" value="{{ isset($_GET['from_date']) ? $_GET['from_date'] : '' }}" data-jdp>
                                    </div>

                                    <div class="form-group col-6 col-md-2 mb-3">
                                        <label for="to_date">نمایش تا تاریخ:</label>
                                        <input type="text" class="form-control" name="to_date"
                                               id="to_date" value="{{ isset($_GET['to_date']) ? $_GET['to_date'] : '' }}" data-jdp>
                                    </div>

                                    <div class="form-group col-6 col-md-2">
                                        <label for="delivery_from_date">نمایش از تاریخ تحویل:</label>
                                        <input type="text" class="form-control" name="delivery_from_date"
                                               id="delivery_from_date" value="{{ isset($_GET['delivery_from_date']) ? $_GET['delivery_from_date'] : '' }}" data-jdp>
                                    </div>

                                    <div class="form-group col-6 col-md-2">
                                        <label for="delivery_to_date">نمایش تا تاریخ تحویل:</label>
                                        <input type="text" class="form-control" name="delivery_to_date"
                                               id="delivery_to_date" value="{{ isset($_GET['delivery_to_date']) ? $_GET['delivery_to_date'] : '' }}" data-jdp>
                                    </div>
                                    @if($isLeader == false && $isVisitor == false)
                                    <div class="form-group col-6 col-md-2 mb-3">
                                        <label for="leader_id">نمایش بر اساس سرپرست:</label>
                                        <select class="select2 form-select" id="leader_id" name="leader_id">
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($Leaders as $leader)
                                                <option value="{{ $leader->id }}" @if(isset($_GET['leader_id']) && $leader->id == $_GET['leader_id'] ) selected @endif >{{ $leader->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-6 col-md-2 mb-3">
                                        <label for="visitor_id">نمایش بر اساس بازاریاب:</label>
                                        <select class="select2 form-select" id="visitor_id" name="visitor_id">
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($Visitors as $visitor)
                                                <option value="{{ $visitor->id }}" @if(isset($_GET['visitor_id']) && $visitor->id == $_GET['visitor_id'] ) selected @endif >{{ $visitor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-6 col-md-3 mb-3">
                                        <label for="city_id">نمایش بر اساس شهر:</label>
                                        <select class="select2 form-select" data-allow-clear="true" multiple id="city_id" name="city_id[]">
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($Cities as $city)
                                                <option value="{{ $city->id }}" @if(isset($_GET['city_id']) && $city->id == $_GET['city_id'] ) selected @endif >{{ $city->name }}</option>
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
                                        <button type="submit" class="btn btn-info">فیلتر کن</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card-datatable table-responsive pt-0">
                                <style>
                                    .table tr th, .table tr td {padding: 7px !important;}
                                    .dataTables_filter {width: 365px}
                                </style>
                                <table class="datatables-direct-basic table">
                                    <thead>
                                    <tr class="text-center">
                                        <th width="30">ردیف</th>
                                        <th width="7%">تاریخ ثبت</th>
                                        <th width="7%">شماره فاکتور</th>
                                        <th width="7%">واحد پخش</th>
                                        <th width="12%">نام خریدار</th>
                                        <th width="80">مجموع مقدار</th>
                                        <th width="10%" class="text-center">تاریخ تحویل</th>
                                        <th width="12%">مبلغ کل</th>
                                        @if($isLeader == false)
                                        <th>بازاریاب</th>
                                        @endif
                                        <th>سرپرست</th>

                                        <th>وضعیت</th>
                                        <th class="text-center">عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody style="background-color: #fff">


                                    @php($x = 1)
                                    @if(isset($PishFactors))
                                    @foreach($PishFactors as $invoice)
                                            <?php $organ = App\Models\Organization::find($invoice->organization_id); ?>
                                        <tr>
                                            <th width="30" class="text-center"><small>{{ $x }}</small></th>
                                            <td  width="7%"><small data-bs-toggle="tooltip" data-bs-placement="top"
                                                       data-bs-custom-class="custom-tooltip"
                                                       data-bs-title="ساعت {{ Verta($invoice->created_at)->format('H:i') }}">{{ Verta($invoice->created_at)->format('Y-m-d') }}</small></td>
                                            <td class="text-center" width="7%"><small>{{ $invoice->invoiceID }} </small></td>
                                            <td width="7%">{{ $invoice->organization ? $invoice->organization->title : '---' }}</td>
                                            <td  width="12%"><a href="{{ route('pishFactorInfo', $invoice->id) }}">{{ isset($invoice->customer->name) ? $invoice->customer->name : 'وارد نشده' }}</a> </td>
                                            <td class="text-center"  width="80">
                                                    <?php $details = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->count(); ?>
                                                   <small data-bs-toggle="tooltip" data-bs-placement="top"
                                                          data-bs-custom-class="custom-tooltip"
                                                          data-bs-title="{{ $details }} قلم">
                                                           <?php
                                                           $Packs = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('pack');
                                                           $tedad = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('tedad');
                                                           ?>
                                                       @if($Packs > 0) {{ $Packs }} {{ $organ ? $organ->sub_unit : 'واحد پخش حذف شده' }} @endif
                                                       @if($tedad > 0) {{ $tedad }} {{ $organ ? $organ->unit_order : 'واحد پخش حذف شده' }} @endif

                                                   </small>
                                            </td>
                                            <td class="text-center" width="10%"><small>{{ $invoice->recive_date ? $invoice->recive_date : 'وارد نشده' }}</small></td>
                                            <td width="12%">

                                                <small>
                                                    {{ number_format(intval(str_replace(',','',$invoice->fullPrice))) }}
                                                    ریال
                                                </small>
                                            </td>
                                                <?php $Visitor = App\Models\User::find($invoice->visitor_id); ?>
                                            <td><small>{{ $Visitor->name }}</small></td>
                                            @if($isLeader == false)
                                                <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                            <td><small>{{ $leader->name }}</small></td>
                                            @endif
                                            <td>
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
                                    @endif
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
                @if($isLeader == false && $isVisitor == false)
                layout: {
                    topStart: {
                        buttons: [{
                            extend: 'copy',
                            text: 'کپی',
                            className: 'btn rounded-pill btn-label-secondary waves-effect'
                        }, {
                            extend: 'excel',
                            text: 'خروجی اکسل',
                            className: 'btn rounded-pill btn-label-success waves-effect',
                        },{
                            extend: 'print',
                            text: 'پرینت',
                            className: 'btn rounded-pill btn-label-warning waves-effect',
                            customize: function (win) {
                                $(win.document.body)
                                    .css('font-size', '10pt')
                                    .prepend(
                                        // '<img src="http://datatables.net/media/images/logo-fade.png" style="position:absolute; top:0; left:0;" />'
                                    );
                                $(win.document.body).css('direction', 'rtl')

                                $(win.document.body)
                                    .find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                                $(win.document.body)
                                    .find('td')
                                    .css('min-width', '70px');
                                $(win.document.body)
                                    .find('th')
                                    .css('min-width', '70px');
                                $(win.document.body)
                                    .find('th.now')
                                    .css('max-width', '40px').css('min-width', '40px').css('width', '40px');
                                $(win.document.body)
                                    .find('thead tr')
                                    .css('background-color', '#D0E4D3').css('border-top-left-radius', '8px').css('border-top-right-radius', '8px')
                            }
                        }]
                    }
                }
                @endif
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

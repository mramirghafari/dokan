<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>فاکتورهای در حال پخش - دکان دارمینو</title>
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
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">انبار و توزیع /</span>
                       لیست در حال پخش <small>(اساین شده به رانندگان)</small>
                    </h4>

                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card-datatable pt-0">
                                <?php $Organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
                                <style>
                                    table.ps th:not(.now), table.ps td:not(.now) { min-width: 70px !important;}
                                </style>

                                <table class="datatables-direct-basic table display ps" style="overflow-x: scroll">
                                    <thead>
                                    <tr class="text-center">
                                        <th class="now" style="width: 40px !important;min-width: 40px !important;max-width: 40px !important;">شماره</th>
                                        <th class="w-25">مشتری</th>
                                        <th class="w-25">توضیحات</th>
                                        @foreach($uniqueItems as $pritem)
                                        <th><small>{{ $pritem['product_name'] }}</small></th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody style="background-color: #fff">
                                    @php($x = 1)
                                        @foreach($Factors as $factor)
                                            <tr>
                                                <th class="now">{{ $x }}</th>
                                                <td><a href="{{ route('pishFactorInfo', $factor->id) }}">{{ $factor->customer->name }}</a> <br />
                                                <small>تحویل:  {{ $factor->recive_date }}</small>
                                                </td>
                                                <td>{{ $factor->tozihat }}</td>
                                                @foreach($uniqueItems as $pritem)
                                                    <td style="min-width: 85px;">
                                                            <?php $CheckFactorItem = App\Models\PishFactorItems::where('pishfactor_id',$factor->id)->where('pr_id',$pritem['pr_id'])->first(); ?>
                                                        @if($CheckFactorItem)
                                                            @if($CheckFactorItem->pack > 0) {{ $CheckFactorItem->pack }} {{ $Organ->sub_unit }} <br /> @endif
                                                            @if($CheckFactorItem->tedad > 0) {{ $CheckFactorItem->tedad }} {{ $Organ->unit_order }}  @endif
                                                        @endif
                                                    </td>
                                                @endforeach
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
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script>
    $(document).ready(function() {
        jalaliDatepicker.startWatch();
    });
</script>


<script src="{{ asset('assets/') }}/js/datatable/dataTables.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/dataTables.buttons.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/buttons.dataTables.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/jszip.min.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/buttons.html5.min.js"></script>
<script src="{{ asset('assets/') }}/js/datatable/buttons.print.min.js"></script>
<script>
    $('.anbarotozi').addClass('open')
    $('.anbarotozi .assigned_to_drivers').addClass('active open');
    // datatable (jquery)
    $(function () {
        var
            dt_without_ajax_table = $('.datatables-direct-basic');

        // DataTable Direct
        // --------------------------------------------------------------------
        if(dt_without_ajax_table.length){
            dt_without_ajax = dt_without_ajax_table.DataTable({
                searching: true,
                scrollX: true,
                lengthChange: false,
                ordering: false,
                pageLength: 100,
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
            });

        }


    });

    let mouseDown = false;
    let startX, scrollLeft;
    const slider = document.querySelector('table.ps');

    const startDragging = (e) => {
        mouseDown = true;
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    }

    const stopDragging = (e) => {
        mouseDown = false;
    }

    const move = (e) => {
        e.preventDefault();
        if(!mouseDown) { return; }
        const x = e.pageX - slider.offsetLeft;
        const scroll = x - startX;
        slider.scrollLeft = scrollLeft - scroll;
    }

    // Add the event listeners
    slider.addEventListener('mousemove', move, false);
    slider.addEventListener('mousedown', startDragging, false);
    slider.addEventListener('mouseup', stopDragging, false);
    slider.addEventListener('mouseleave', stopDragging, false);

</script>
</body>

</html>

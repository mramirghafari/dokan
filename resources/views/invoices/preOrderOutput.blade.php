<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>حواله خروج پیش سفارشات انبار - دکان دارمینو</title>
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
                        حواله خروج پیش سفارشات
                    </h4>
                    <!-- Sticky Actions -->
                    <form action="{{ route('deliveries.preOrderOutputFilter') }}" method="POST">
                        @csrf
                        <div class="col-12 card mb-4">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="form-group col-6 col-md-3">
                                        <label for="from_date">نمایش از تاریخ ثبت:</label>
                                        <input type="text" class="form-control" name="from_date"
                                               id="from_date" data-jdp>
                                    </div>

                                    <div class="form-group col-6 col-md-3">
                                        <label for="to_date">نمایش تا تاریخ ثبت:</label>
                                        <input type="text" class="form-control" name="to_date"
                                               id="to_date" data-jdp>
                                    </div>

                                    <div class="form-group col-6 col-md-3">
                                        <label for="delivery_from_date">نمایش از تاریخ تحویل:</label>
                                        <input type="text" class="form-control" name="delivery_from_date"
                                               id="delivery_from_date" data-jdp>
                                    </div>

                                    <div class="form-group col-6 col-md-3">
                                        <label for="delivery_to_date">نمایش تا تاریخ تحویل:</label>
                                        <input type="text" class="form-control" name="delivery_to_date"
                                               id="delivery_to_date" data-jdp>
                                    </div>


                                    <div class="form-group col-12 d-flex align-items-end mt-3">
                                        <button type="submit" class="btn btn-info w-100">فیلتر تاریخ</button>
                                    </div>

                                </div>
                                @if($fromDate == null && $toDate == null)
                                    <p class="text-danger">لیست زیر مربوط به کلیه سفارشات تایید شده میباشد. در صورتی که حواله مربوط به تاریخ خاصی مد نظر هست لطفا از طریق فیلتر تاریخ را انتخاب کنید.</p>
                                @elseif($fromDate != null && $toDate != null && $fromDate == $toDate)
                                    <p class="text-danger">حواله خروج مربوط به تاریخ <span style="display: inline-block;direction: ltr">{{ $fromDate }}</span> میباشد</p>
                                @elseif($fromDate != null && $toDate != null && $fromDate != $toDate)
                                    <p class="text-danger">حواله خروج مربوط به تاریخ <span style="display: inline-block;direction: ltr">{{ $fromDate }}</span> تا <span style="display: inline-block;direction: ltr">{{ $toDate }}</span> میباشد</p>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card-datatable table-responsive pt-0">

                                <table class="datatables-direct-basic table">
                                    <thead>
                                    <tr class="text-center">
                                        <th style="width: 40px">شماره</th>
                                        <th>کد محصول</th>
                                        <th class="w-25">نام محصول</th>
                                        <th>
                                            واحد فرعی
                                        </th>
                                        <th>واحد اصلی</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($x = 1)
                                    @php($AllTedad = 0)
                                    @php($Allpacks = 0)
                                    @foreach($Items as $productId => $summary)
                                            <?php $pr = DB::table('products')->where('id', $productId)->first() ?>
                                        <tr>
                                            <td>{{ $x }}</td>
                                            <td>{{ $pr->sku }}</td>
                                            <td>
                                                {{ $pr->title }} {{ $pr->display_name }}
                                            </td>
                                            <td>{{ $summary['total_pack'] }} @php($Allpacks += $summary['total_pack'])</td>
                                            <td>{{ $summary['total_tedad'] }} @php($AllTedad += $summary['total_tedad'])</td>
                                            <td></td>
                                        </tr>
                                        @php($x++)
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <th></th>
                                    <th></th>
                                    <th> {{ $Allpacks }} واحد فرعی </th>
                                    <th> {{ $AllTedad }} واحد اصلی </th>
                                    <th></th>
                                    </tfoot>
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
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script>
    $(document).ready(function() {
        jalaliDatepicker.startWatch();
    });
</script>
<script>
    $('.anbarotozi').addClass('open')
    $('.anbarotozi .preOrderOutput').addClass('active open');
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
                }
            });

        }


    });


</script>
</body>

</html>

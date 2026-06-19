<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>پیش فاکتورهای در انتظار - دکان دارمینو</title>
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
                        <span class="text-muted fw-light">مالی حسابداری /</span>
                       لیست فاکتورها بر اساس پرداخت
                    </h4>
                    <form action="{{ route('Accounting.Filter') }}" method="POST">
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
                                @if(isset($fromDate) && $fromDate == null && $toDate == null)
                                    <p class="text-danger">لیست زیر مربوط به کلیه سفارشات تایید شده میباشد. در صورتی که حواله مربوط به تاریخ خاصی مد نظر هست لطفا از طریق فیلتر تاریخ را انتخاب کنید.</p>
                                @elseif(isset($fromDate) && $fromDate != null && $toDate != null && $fromDate == $toDate)
                                    <p class="text-danger">حواله خروج مربوط به تاریخ <span style="display: inline-block;direction: ltr">{{ $fromDate }}</span> میباشد</p>
                                @elseif(isset($fromDate) && $fromDate != null && $toDate != null && $fromDate != $toDate)
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
                                        <th width="40">ردیف</th>
                                        <th>نام خریدار</th>
                                        <th>وضعیت پرداخت</th>
                                        <th>تاریخ ثبت</th>
                                        <th>تاریخ تحویل</th>
                                        <th>مبلغ کل</th>
                                        <th>وضعیت</th>
                                        <th>بازاریاب</th>
                                        <th>سرپرست</th>
                                        <th>عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody style="background-color: #fff">
                                    <?php $organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
                                    @php($x = 1)
                                    @foreach($PishFactors as $invoice)
                                        <tr>
                                            <th>{{ $x }}</th>
                                            <td><a href="{{ route('pishFactorInfo', $invoice->id) }}">{{ isset($invoice->customer->name) ? $invoice->customer->name : 'وارد نشده' }}</a> </td>
                                            <td class="text-center">
                                                @if($invoice->payment_type == 1)
                                                    <span class="badge bg-label-success me-1">پرداخت نقدی</span> <br />
                                                @elseif($invoice->payment_type == 2)
                                                    <span class="badge bg-label-warning me-1">پرداخت چکی</span> <br />
                                                @elseif($invoice->payment_type == 3)
                                                    <span class="badge bg-label-danger me-1">پرداخت نشده</span> <br />

                                                @endif
                                            </td>
                                            <td><small>{{ Verta($invoice->created_at)->format('Y-m-d - H:i:s') }}</small></td>
                                            <td><small>{{ $invoice->recive_date ? $invoice->recive_date : 'وارد نشده' }}</small></td>
                                            <td>

                                                {{ number_format(intval(str_replace(',','',$invoice->fullPrice))) }}
                                               ریال
                                            </td>
                                            <td>
                                                @if($invoice->status == 1)
                                                    <span class="badge bg-label-info me-1">تایید شده</span> <br />
                                                @elseif($invoice->status == 4)
                                                    <span class="badge bg-label-success me-1">تحویل به مشتری</span> <br />
                                                @endif
                                            </td>
                                            @if(auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                    <?php $Visitor = App\Models\User::find($invoice->visitor_id); ?>
                                                <td>{{ $Visitor->name }}</td>
                                            @endif
                                                <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                            <td>{{ $leader->name }}</td>
                                            <td class="text-center">

                                                <a href="{{ route('pishFactorInfo', $invoice->id) }}"><svg width="24" height="21" viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M1.02324 5.6932C0.981836 5.56875 0.981836 5.43425 1.02324 5.3098C1.85544 2.806 4.21764 1 7.00164 1C9.78444 1 12.1454 2.8042 12.9794 5.3068C13.0214 5.431 13.0214 5.5654 12.9794 5.6902C12.1478 8.194 9.78564 10 7.00164 10C4.21884 10 1.85724 8.1958 1.02324 5.6932Z" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M8.80312 5.49995C8.80312 5.97734 8.61348 6.43518 8.27592 6.77274C7.93835 7.11031 7.48051 7.29995 7.00312 7.29995C6.52574 7.29995 6.0679 7.11031 5.73033 6.77274C5.39277 6.43518 5.20312 5.97734 5.20312 5.49995C5.20312 5.02256 5.39277 4.56472 5.73033 4.22716C6.0679 3.88959 6.52574 3.69995 7.00312 3.69995C7.48051 3.69995 7.93835 3.88959 8.27592 4.22716C8.61348 4.56472 8.80312 5.02256 8.80312 5.49995Z" stroke="#248230" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                                @if(auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)

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
<script>
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
                order: [[0, 'asc']],
                pageLength: 50,
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

            $('.datatables-direct-basic tbody').on( 'click', '.dropdown-item.delete-record', function () {
                dt_without_ajax
                    .row( $(this).parents('tr') )
                    .remove()
                    .draw();
            } );
        }


    });

</script>
</body>

</html>

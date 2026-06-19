<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مدیریت مسیرهای منطقه سامانه - دکان دارمینو</title>
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
                    <div class="row">
                        <div class="col">
                            <h4 class="py-3 mb-4">
                                <span class="text-muted fw-light">اطلاعات پایه /</span>
                                <a href="{{ route('regions.index') }}">مناطق</a>  /
                                <span class="text-muted fw-light">لیست مسیرهای  {{ $Region->name }}</span>
                            </h4>
                        </div>
                        <div class="col text-end">
                            <a href="{{ route('regions.index') }}" class="btn btn-label-dark waves-effect ms-3 mt-2" type="button">
                                بازگشت
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Sticky Actions -->
                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان مسیر</th>
                                            <th>منطقه</th>
                                            <th>شهر</th>
                                            <th>تعداد مشتریان</th>
                                            <th>مشتری فعال</th>
                                            <th>تعداد سفارشات</th>
                                            <th>جمع سفارشات (ریال)</th>
                                            <th>سرپرست</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach ($Areas as $area)
                                            <tr>
                                                <th>{{ $x }}</th>
                                                <td><a href="{{ route('areas.edit', $area->id) }}">{{ $area->name }}</a></td>
                                                <td>
                                                    @php($Region_cur = DB::table('regions')->where('id', $area->region_id)->first())
                                                    {{ $Region_cur->name }}
                                                </td>
                                                @php($City = DB::table('cities')->where('id',$Region_cur->city_id)->first())
                                                <td class="text-center">@if($City){{ $City->name }}@else <small>وارد نشده</small> @endif</td>
                                                <td class="text-center"><a href="{{ route('areas.customersList', $area->id) }}">{{ $area->customers->count() }}</a></td>
                                                <td class="text-center"><a href="{{ route('areas.activeCustomersList', $area->id) }}">{{ $area->activeCustomersCount() }}</a></td>
                                                <td class="text-center"><a href="{{ route('areas.invoiceList', $area->id) }}">{{ number_format(count($area->activeOrders())) }}</a></td>
                                                <td class="text-center"><a href="{{ route('areas.invoiceList', $area->id) }}">{{ number_format($area->activeOrdersSum()) }}</a></td>
                                                <td class="text-center">
                                                    @php($Leader_cur = DB::table('users')->where('id', $area->leader_id)->first())
                                                    @if($Leader_cur)
                                                        {{ $Leader_cur->name }}
                                                    @else
                                                        @php($Leader_cur = DB::table('users')->where('id', $Region_cur->leader_id)->first())
                                                        @if($Leader_cur)
                                                            {{ $Leader_cur->name }}
                                                        @else
                                                            <div class='badge badge-danger'>وارد نشده</div>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('areas.edit', $area->id) }}"
                                                       style="font-size:20px;float: right;margin-left:5px"><x-ui.icon name="fa-edit" /></a>

                                                    @if(auth()->user()->isAdmin == 1 && count($area->activeOrders()) == 0)
                                                        <form action="{{ route('areas.destroy', $area->id) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('آیا از حذف مسیر مورد نظر اطمینان دارید؟');">
                                                            @method('delete')
                                                            @csrf
                                                            <button type="submit"
                                                                    style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                                <x-ui.icon name="fa-trash" />
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
    $('.basicdata').addClass('open')
    $('.basicdata .regions').addClass('active open')
    // datatable (jquery)
    $(function () {
        var
            dt_without_ajax_table = $('.datatables-direct-basic');

        // DataTable Direct
        // --------------------------------------------------------------------
        if(dt_without_ajax_table.length){
            dt_without_ajax = dt_without_ajax_table.DataTable({
                searching: false,
                lengthChange: false,
                ordering: false,
                pageLength: 30,
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

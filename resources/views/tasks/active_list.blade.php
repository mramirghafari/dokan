<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مسیرهای فعال - دکان دارمینو</title>
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
                    <h4 class="py-3 mb-2">
                        <span class="text-muted fw-light">لیست مسیرها /</span>
                        مسیرهای فعال
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-3">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card">
                                <h5 class="card-header">مسیرهای فعال</h5>
                                <div class="card-body">
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr class="text-center">
                                                <th width="40">ردیف</th>
                                                <th>محل عملیات</th>
                                                <th>کانال / صنف</th>
                                                <th>سرپرست</th>
                                                <th>بازاریاب</th>
                                                <th>وضعیت فروش</th>
                                                <th>وضعیت</th>
                                                <th>عملیات</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @php($x = 1)
                                            @foreach($Tasks as $task)
                                            <tr>
                                                <th>{{ $x }}</th>
                                                @php($area_cur = DB::table('areas')->where('id', $task->area_id)->first())
                                                @php($Region_cur = DB::table('regions')->where('id', $area_cur->region_id)->first())

                                                <td><a href="{{ route('tasks.edit', $task->id) }}">{{ $Region_cur->name }} / {{ $area_cur->name }}</a> </td>
                                                <td>{{ $task->channel }} / {{ $task->senf }}</td>
                                                <td class="text-center">
                                                    @php($Leader = DB::table('users')->where('id', $task->leader_id)->first())
                                                    @if($Leader)
                                                    {{ $Leader->name }}
                                                    @else
                                                        <small class="badge bg-label-danger me-1">وارد نشده</small>
                                                    @endif

                                                </td>
                                                <td class="text-center">
                                                    @php($Visitor = DB::table('users')->where('id', $task->user_id)->first())
                                                    {{ $Visitor->name }}
                                                </td>
                                                <td class="text-center">
                                                    @php($Customers = DB::table('pishfactors')->where('task_id', $task->id)->get())
                                                    @if(count($Customers) > 0)
                                                    <span class="badge bg-label-primary me-1">10 فاکتور ثبت شده</span> <br />
                                                    <span class="badge bg-label-success me-1">8 فاکتور تایید شده</span> <br />
                                                    <span class="badge bg-label-success me-1">260,000,000 ریال مجموع فروش</span>
                                                    @else
                                                        <small class="badge bg-label-danger me-1">آماری وجود ندارد</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($task->status == 1)
                                                    <span class="badge bg-label-success me-1">فعال</span>
                                                    @else
                                                        <span class="badge bg-label-danger me-1">غیرفعال</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('tasks.edit', $task->id) }}">
                                                        <x-ui.icon name="pencil" class="me-1" />
                                                        مشاهده جزئیات / ویرایش
                                                    </a>
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
                searching: false,
                lengthChange: false,
                ordering: false,
                pageLength: 5,
            });

        }


    });

</script>
</body>

</html>

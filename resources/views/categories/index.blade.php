<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>دسته بندی ها - دکان دارمینو</title>
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
        @include('sections/sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('sections/header')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">اطلاعات پایه /</span>
                        دسته بندی ها
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 col-md-5 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form id="addCategory" action="{{ route('categories.store') }}" method="POST" novalidate>
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label" for="title">نام دسته بندی</label>
                                            <input type="text" class="form-control" name="title" id="title" placeholder="نام دسته بندی" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="description">توضیح</label>
                                            <input class="form-control" id="description" placeholder="توضیح" type="text" name="description"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="exampleInputEmail12">انتخاب دسته بندی مادر:</label>
                                            <select class="js-example-basic-single form-control" name="parent_id" style="width: 100%;">
                                                <option value="">--هیچکدام--</option>
                                                @foreach ($parents as $parent)
                                                    <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if(auth()->user()->isGod == 1)
                                            <?php $Tenants = DB::table('tenants')->get(); ?>
                                        <div class="mb-3">
                                            <label class="form-label" for="tenantds_id">انتخاب پنل</label>
                                            <select class="select2 form-select" id="tenantds_id" required>
                                                <option value="0">انتخاب کنید</option>
                                                @foreach($Tenants as $tenant)
                                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                        <div class="mb-3">
                                            <label class="form-label" for="organization_id">شعبه</label>
                                            <select class="select2 form-select" id="organization_id" required>
                                                <option value="0">انتخاب کنید</option>
                                                @foreach($organizations as $organ)
                                                <option value="{{ $organ->id }}">{{ $organ->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ارسال</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-7 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان دسته بندی</th>
                                            <th>توضیح</th>
                                            <th>دسته والد</th>
                                            <th>وضعیت </th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach($categories as $cat)

                                        <tr>
                                            <td> {{ $x }}</td>
                                            <td><bdi>{{ $cat->title }}</bdi></td>
                                            <td>
                                                {{ $cat->description }}
                                            </td>
                                            <td>
                                               -
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><x-ui.icon name="dots-vertical" class="text-primary" /></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="category-edit.php" class="dropdown-item">ویرایش</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">غیرفعال</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
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
                    </div>
                    <!-- /Sticky Actions -->
                </div>
                <!-- / Content -->
                @include('sections/footer')
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
    $('.basicdata .categories').addClass('active open')
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
                pageLength: 50,
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
<script>
    $(document).ready(function () {
        $('#addCategory').on('submit', function (e) {
            let isValid = true;

            // پاک کردن پیام‌های قبلی
            $('.error-message').remove();

            // چک کردن تمام فیلدهای این فرم که الزامی هستند
            $(this).find('input[required], select[required]').each(function () {
                let $field = $(this);
                let value = $.trim($field.val());

                // شرط: اگر select باشد و مقدارش 0 باشد، یا اینپوت خالی باشد
                if (($field.is('select') && value === '0') ||
                    ($field.is('input') && value === '')) {

                    isValid = false;

                    // ساخت پیام خطا
                    let errorMsg = $('<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>');

                    // درج پیام بعد از فیلد
                    if ($field.next('.select2').length) {
                        // اگر select2 هست، پیام رو بعد از container select2 بذاریم
                        $field.next('.select2').after(errorMsg);
                    } else {
                        $field.after(errorMsg);
                    }
                }
            });

            if (!isValid) {
                e.preventDefault(); // جلوگیری از ارسال فرم
            }
        });
    });
</script>
</body>

</html>

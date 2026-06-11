<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ثبت تارگت جدید - دکان دارمینو</title>
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
                    <h4 class="py-3 mb-2">
                        <span class="text-muted fw-light">تارگت های فروش /</span>
                        ثبت تارگت جدید
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-3">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <form class="card-body" id="addTarget" method="POST" action="{{ route('targets.store') }}" novalidate>
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="user_id">انتخاب کاربر <small style="color: red;">*</small></label>
                                            <select class="select2 form-select"id="user_id" name="user_id" required>
                                                <option value="0">انتخاب کنید</option>
                                                @foreach($Users as $user)
                                                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                                    @if(is_array($user['children']) && count($user['children']) > 0)
                                                        @foreach($user['children'] as $sub)
                                                            <option value="{{ $sub['id'] }}"> --> {{ $sub['name'] }}</option>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <hr class="my-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="start_date_fa">تاریخ شروع تارگت <small style="color: red;">*</small> </label>
                                            <input class="form-control" id="start_date_fa" name="start_date_fa" placeholder="تاریخ اتمام تارگت" type="text" data-jdp required />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="end_date_fa">تاریخ اتمام تارگت <small style="color: red">*</small> </label>
                                            <input class="form-control" id="end_date_fa" name="end_date_fa" placeholder="تاریخ اتمام تارگت" type="text" data-jdp required/>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="orders_count">سقف تعداد فروش در تارگت <small style="color: red;"></small></label>
                                            <input class="form-control" id="orders_count" placeholder="حداقل تعداد فروش" name="orders_count" type="number"/>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="target_price">سقف مالی تارگت به ریال <small style="color: red">*</small></label>
                                            <input class="form-control" id="target_price" placeholder="سقف مالی تارگت" name="target_price" data-max="{{ $Mande }}" type="text" required/>
                                            <p class="mandee" style="color: #0f6ded;">مقدار تارگت قابل استفاده : {{ number_format($Mande) }}</p>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="min_order_price">اعمال محدودیت حداقل مبلغ هر سفارش به ریال</label>
                                            <input class="form-control" id="min_order_price" name="min_order_price" placeholder="محدودیت حداقل مبلغ هر سفارش" type="text"/>
                                        </div>
                                        <hr class="my-3">
                                        <div class="col-12">
                                            <div class="col d-flex justify-content-end">
                                                <div class="col-2 mr-auto text-end mb-2">
                                                    <button class="btn btn-info additem" type="button">افزودن تارگت محصول جدید</button>
                                                </div>
                                            </div>
                                            <table class="table editfactor_table table-bordered">
                                                <thead>
                                                <tr class="text-center">
                                                    <th width="40">ردیف</th>
                                                    <th>انتخاب محصول</th>
                                                    <th>تعداد فروش</th>
                                                    <th>سقف مالی فروش</th>
                                                    <th>عملیات</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="item_1" data-item="1">
                                                    <td>1</td>
                                                    <td class="text-center">
                                                        <select class="select2 form-select" name="pr_id[]" >
                                                            <option value="">انتخاب کنید</option>
                                                            @foreach($Products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->title }} {{ $product->display_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="order_count[]" value="" />
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="price_count[]" value="" />
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="removeitem" style="cursor:pointer;"><svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923" stroke="#FF0000" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="pt-4">
                                        <button class="btn btn-primary me-sm-3 me-1" type="submit">ثبت تارگت جدید</button>
                                    </div>
                                </form>
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
<script>
    $(document).ready(function() {
        jalaliDatepicker.startWatch();
    });
</script>
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
    $(document).ready(function() {
        $('.additem').click(function() {
            $('.editfactor_table tbody').append('<tr class="item_1" data-item="1"><td>1</td><td class="text-center"><select class="select2 form-select" name="pr_id[]" ><option value="">انتخاب کنید</option>@foreach($Products as $product)<option value="{{ $product->id }}">{{ $product->title }} {{ $product->display_name }}</option>@endforeach</select></td><td><input type="number" class="form-control" name="order_count[]" value="" /></td><td><input type="number" class="form-control" name="price_count[]" value="" /></td><td class="text-center"><span class="removeitem" style="cursor:pointer;"><svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923" stroke="#FF0000" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span></td></tr>');
        });

        $('body').on('click','.removeitem',function() {
            $(this).parents('tr').remove();
        });

        @if($Mande > 0)
        $('body').on('keyup','#target_price',function() {
            var target_price = $(this).val();
            var mande = $(this).attr('data-max');
            var tp = target_price.replaceAll(',','');
            if(parseInt(tp) > parseInt(mande)) {
                $('.mandee').css('color','red');
                $('.mandee').html('مقدار وارد شده از مقدار باقی مانده مالی تارگت بیشتر است.');
            }


        });
        @endif
    });

    document.getElementById('target_price').addEventListener('input', function(e) {
        let value = e.target.value;

        // Remove all non-digit characters except for a single decimal point
        value = value.replace(/[^0-9.]/g, '');

        // Handle multiple decimal points
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Convert to a number and format
        const numberValue = parseFloat(value);
        if (!isNaN(numberValue)) {
            e.target.value = numberValue.toLocaleString('en-US'); // Format for US locale
        } else {
            e.target.value = ''; // Clear if not a valid number
        }
    });

    document.getElementById('min_order_price').addEventListener('input', function(e) {
        let value = e.target.value;

        // Remove all non-digit characters except for a single decimal point
        value = value.replace(/[^0-9.]/g, '');

        // Handle multiple decimal points
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Convert to a number and format
        const numberValue = parseFloat(value);
        if (!isNaN(numberValue)) {
            e.target.value = numberValue.toLocaleString('en-US'); // Format for US locale
        } else {
            e.target.value = ''; // Clear if not a valid number
        }
    });

</script>
<script>
    $(document).ready(function () {
        $('#addTarget').on('submit', function (e) {
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

<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>مدیریت درگاه و پایانه ها - دکان دارمینو</title>
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
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">اطلاعات پایه /</span>
                        مدیریت درگاه و پایانه ها
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 col-md-4 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form id="addStore" action="{{ route('Terminals.store') }}" method="POST" novalidate>
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label" for="title">نام پایانه<small style="color: red">*</small></label>
                                            <input class="form-control" id="title" placeholder="عنوان پایانه" type="text" name="title" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="terminal_number">شماره پایانه<small style="color: red">*</small></label>
                                            <input class="form-control" id="terminal_number" placeholder="کد پایانه" type="text" name="terminal_number" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="description">توضیحات پایانه</label>
                                            <textarea class="form-control" id="description" placeholder="توضیح" name="description" ></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="account_id">حساب مربوطه:</label>
                                            <select class="select2 form-select" id="account_id" name="account_id">
                                                <option value="0">انتخاب کنید</option>
                                                @foreach($Accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->name }} {{ $acc->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="terminal_type">نوع پایانه</label>
                                            <select class="select2 form-select" id="terminal_type" name="terminal_type">
                                                <option value="0">انتخاب کنید</option>
                                                <option value="1">کارتخوان</option>
                                                <option value="2">درگاه بانکی</option>
                                                <option value="3">درگاه USSD</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="provider_name">نام سرویس دهنده:</label>
                                            <input class="form-control" id="provider_name" placeholder="عنوان سرویس دهنده" type="text" name="provider_name" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="organization_id">واحدهای پخش مربوطه<small style="color: red">*</small></label>
                                            <select class="select2 form-select" data-allow-clear="true" id="organization_id" name="organization_id[]" multiple required>
                                                <option value="">انتخاب کنید</option>
                                                @if (\Auth::user()->isAdmin == 1)
                                                    @foreach ($Organizations as $organization)
                                                        <option value="{{ $organization->id }}">
                                                            {{ $organization->description }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="{{ \Auth::user()->organization_id }}">
                                                        {{ optional(\Auth::user()->organization)->title ?? '—' }}</option>
                                                @endif
                                            </select>
                                        </div>

                                        <button class="btn btn-primary" type="submit">ایجاد پایانه</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-8 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr class="text-center">
                                            <th>ردیف</th>
                                            <th>کد پایانه</th>
                                            <th>نام پایانه</th>
                                            <th>حساب متصل</th>
                                            <th>نوع</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                            @foreach($Terminals as $terminal)
                                                <tr>
                                                    <th>{{ $x }}</th>
                                                    <td>{{ $terminal->terminal_number }}</td>
                                                    <td>{{ $terminal->title }}</td>
                                                    <td>{{ $terminal->account->name }}</td>
                                                    <td class="text-center">
                                                         <span class="badge bg-label-info me-1">
                                                        @if($terminal->terminal_type == 1) کارتخوان
                                                        @elseif($terminal->terminal_type == 2) درگاه اینترنتی
                                                        @elseif($terminal->terminal_type == 3) کد USSDT
                                                        @endif
                                                         </span>
                                                    </td>
                                                    <td class="text-center">
                                                    @if($terminal->isActive == 1)
                                                            <span class="badge bg-label-success me-1">فعال</span>
                                                    @else
                                                            <span class="badge bg-label-danger me-1">غیرفعال</span>
                                                    @endif
                                                    </td>
                                                    <td><a href="#">ویرایش</a> </td>
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
    // datatable (jquery)
    $('.basicdata').addClass('open')
    $('.basicdata .terminals').addClass('active open')
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
                pageLength: 10,
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
        $('#addStore').on('submit', function (e) {
            e.preventDefault(); // جلوگیری از ارسال ابتدا، تا ولیدیشن اجرا شود
            let isValid = true;
            $('.error-message').remove();

            $(this).find('input[required], select[required]').each(function () {
                let $field = $(this);
                let value = $field.val();

                // تبدیل آرایه به رشته اگر multiple باشد
                if (Array.isArray(value)) {
                    value = value.length ? value.join(',') : '';
                }

                if ($.trim(value) === '') {
                    isValid = false;
                    let errorMsg = $('<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>');

                    if ($field.next('.select2').length) {
                        $field.next('.select2').after(errorMsg);
                    } else {
                        $field.after(errorMsg);
                    }
                }
            });

            if (isValid) {
                this.submit(); // اگر معتبر بود، فرم رو ارسال کن
            }
        });

        $('#type').on('change',function () {
            var type = $(this).find('option:selected').val();
            if(type == 1) {
                $('.con_bank').removeClass('d-none');
            }else {
                $('.con_bank').addClass('d-none');
            }
        });

        $('#level').on('change',function () {
            var type = $(this).find('option:selected').val();
            if(type > 1) {
                $('.parent_box').removeClass('d-none');
            }else {
                $('.parent_box').addClass('d-none');
            }
        });
    });
</script>
<script>
    $(document).ready(function () {

        // ذخیره کامل همه آپشن‌ها
        let allOptions = [];
        $('#parent_id option').each(function () {
            allOptions.push({
                id: $(this).val(),
                text: $(this).text(),
                level: $(this).data('level') ?? null
            });
        });

        $('#level').select2();
        $('#parent_id').select2();

        $('#level').on('change', function () {

            let selectedLevel = parseInt($(this).val());
            let allowed = null;

            if (selectedLevel === 2) allowed = 1;   // معین ← والد کل
            if (selectedLevel === 3) allowed = 2;   // تفصیلی ← والد معین
            if (selectedLevel === 1 || selectedLevel === 0) allowed = -1;

            // Destroy قبل از ساخت لیست جدید
            $('#parent_id').select2('destroy');

            // ساخت HTML جدید بر اساس فیلتر درست
            let newHtml = '<option value="0">انتخاب کنید</option>';

            if (allowed !== -1) {
                allOptions.forEach(opt => {
                    if (opt.id !== "0" && opt.level === allowed) {
                        newHtml += `<option value="${opt.id}" data-level="${opt.level}">${opt.text}</option>`;
                    }
                });
            }

            $('#parent_id').html(newHtml);

            // Init جدید؛ حالا Select2 دقیقاً فقط همین گزینه‌ها رو می‌بینه
            $('#parent_id').select2();

        });

    });
</script>

</body>

</html>
